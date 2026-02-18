<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DTO\BusinessInputDTO;
use App\Services\FinancialEngine;
use App\Services\SimulationEngine;
use App\Services\DiagnosticEngine;
use App\Services\PlannerPresetService;
use App\Models\MentorSession;
use Illuminate\Support\Facades\Auth;

use App\Services\RoadmapGeneratorService;
use App\Models\Roadmap;
use App\Models\RoadmapAction;
use App\Models\Simulation;

class MentorController extends Controller
{
    public function index()
    {
        return view('mentor.index'); // If we have a separate view, otherwise it's API
    }

    public function calculate(Request $request)
    {
        try {
            $input = BusinessInputDTO::fromRequest($request);
            $mode = $request->input('mode', 'optimizer'); // 'optimizer' or 'planner'

            // Save Snapshot if Auth
            $session = null;
            \Illuminate\Support\Facades\Log::info('Mentor Calculate Request', ['user_id' => Auth::id(), 'auth_check' => Auth::check()]);

            if (Auth::check()) {
                $session = MentorSession::create([
                    'user_id' => Auth::id(),
                    'mode' => $mode,
                    'input_json' => $input->toArray(),
                ]);
                
                // Also Create Simulation Record for Roadmap Generator
                // We use the input here, result (baseline) will be added/updated shortly
            }

            // 1. Calculate Baseline
            $baseline = FinancialEngine::calculateBaseline($input);
            
            // 2. Diagnostic
            $diagnostic = DiagnosticEngine::analyze($baseline, $input);

            if ($session) {
                $session->update(['baseline_json' => $baseline]);
                
                // Create/Update Simulation
                $sim = Simulation::create([
                    'user_id' => Auth::id(),
                    'mode' => $mode,
                    'input_data' => $input->toArray(),
                    'result_data' => $baseline,
                    'health_score' => $diagnostic
                ]);
                \Illuminate\Support\Facades\Log::info('Simulation Created', ['sim_id' => $sim->id]);
            }

            // 3. Sensitivity (Heavy calculation, maybe separate? No, it's fast enough)
            $sensitivity = SimulationEngine::sensitivity($input);

            // 4. Break Even
            $breakEven = SimulationEngine::breakEven($input);

            return response()->json([
                'success' => true,
                'session_id' => $session ? $session->id : null,
                'baseline' => $baseline,
                'diagnostic' => $diagnostic,
                'sensitivity' => $sensitivity,
                'break_even' => $breakEven
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function simulate(Request $request)
    {
        // For Real-time Slider updates (Scenario Slider)
        // This is lightweight, doesn't need to save every slide move.
        // Maybe save "final" result?
        
        $baseline = $request->input('baseline'); // Pass baseline from frontend to avoid recalc? 
        // OR pass input again? Passing input is safer source of truth.
        
        // Let's expect input + changes
        $input = BusinessInputDTO::fromRequest($request);
        $baseline = FinancialEngine::calculateBaseline($input);
        
        $changes = $request->input('changes', []); // ['price_pct' => 0.1, etc]
        
        $simulation = SimulationEngine::simulateScenario($baseline, $changes);
        
        return response()->json([
            'success' => true,
            'simulation' => $simulation
        ]);
    }
    
    public function plannerPreset(Request $request)
    {
        $type = $request->input('type', 'digital');
        $preset = PlannerPresetService::getPreset($type);
        return response()->json($preset);
    }
    
    public function checkFeasibility(Request $request) 
    {
        $modal = (float) $request->input('modal', 0);
        $time = (float) $request->input('time', 0);
        $cost = (float) $request->input('estimated_ad_cost', 0);
        $type = $request->input('type', 'digital');
        
        $result = PlannerPresetService::calculateFeasibility($modal, $cost, $time, $type);
        
        return response()->json($result);
    }
    public function upsell(Request $request)
    {
        $input = BusinessInputDTO::fromRequest($request);
        $upsellPrice = (float) $request->input('upsell_price', 0);
        $takeRate = (float) $request->input('take_rate', 0);
        
        $result = SimulationEngine::upsell($input, $upsellPrice, $takeRate);
        
        return response()->json([
            'success' => true,
            'upsell' => $result
        ]);
    }

    // --- Dynamic Roadmap API ---

    public function generateRoadmap(Request $request)
    {
        // 1. Get User's Latest Simulation
        // If snapshot wasn't saved (Guest), we can't generate personalized roadmap easily without saving first.
        // For V2, let's assume Auth user or temporary session.
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Please login to generate a roadmap.'], 401);
        }

        $simulation = Simulation::where('user_id', Auth::id())
            ->latest()
            ->first();

        if (!$simulation) {
            return response()->json(['success' => false, 'message' => 'No simulation found. Run simulation first.'], 404);
        }

        try {
            // Check if active roadmap exists to avoid spamming?
            // User might want to regenerate. Let's archive old ones or just create new.
            // For now, simple create.
            
            $roadmap = RoadmapGeneratorService::generate($simulation);
            
            return response()->json([
                'success' => true,
                'roadmap' => $roadmap
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getRoadmap()
    {
        if (!Auth::check()) return response()->json(['success' => false], 401);

        $roadmap = Roadmap::where('user_id', Auth::id())
            ->where('status', 'active')
            ->latest()
            ->with(['steps.actions'])
            ->first();

        return response()->json([
            'success' => true,
            'roadmap' => $roadmap
        ]);
    }

    public function toggleAction(Request $request, $actionId)
    {
        if (!Auth::check()) return response()->json(['success' => false], 401);

        $action = RoadmapAction::findOrFail($actionId);
        
        // Security check: belong to user?
        if ($action->step->roadmap->user_id !== Auth::id()) {
             return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $action->is_completed = !$action->is_completed;
        $action->save();

        // Check Step Completion
        $step = $action->step;
        $incompleteActions = $step->actions()->where('is_completed', false)->count();
        
        $stepCompleted = false;
        $nextStepUnlocked = false;

        if ($incompleteActions === 0 && $step->status !== 'completed') {
            $step->status = 'completed';
            $step->save();
            $stepCompleted = true;

            // Unlock Next Step
            $nextStep = RoadmapStep::where('roadmap_id', $step->roadmap_id)
                ->where('order', $step->order + 1)
                ->first();
            
            if ($nextStep) {
                $nextStep->status = 'unlocked';
                $nextStep->save();
                $nextStepUnlocked = true;
            } else {
                // Roadmap Completed!
                $step->roadmap->status = 'completed';
                $step->roadmap->save();
            }
        } elseif ($incompleteActions > 0 && $step->status === 'completed') {
            // Revert completion if user unchecks
            $step->status = 'unlocked';
            $step->save();
        }

        return response()->json([
            'success' => true,
            'action' => $action,
            'step_completed' => $stepCompleted,
            'next_step_unlocked' => $nextStepUnlocked
        ]);
    }
}
