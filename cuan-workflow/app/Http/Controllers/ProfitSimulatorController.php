<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProfitSimulatorService;
use Illuminate\Support\Facades\Log;

class ProfitSimulatorController extends Controller
{
    protected $simulator;

    public function __construct(ProfitSimulatorService $simulator)
    {
        $this->simulator = $simulator;
    }

    /**
     * Run Profit Simulation
     * Endpoint: POST /profit-simulator/simulate
     */
    public function simulate(Request $request) 
    {
        try {
            // 1. Validation
            $validated = $request->validate([
                'session_id' => 'nullable|exists:reverse_goal_sessions,id',
                'manual_baseline' => 'nullable|array', 
                'zone' => 'required|string|in:traffic,conversion,pricing,cost',
                'level' => 'required|integer|in:1,2,3', // New Input: Level instead of pct
            ]);

            $userId = auth()->id();

            // 2. Resolve Baseline
            $sessionId = $validated['session_id'] ?? null;
            $baseline = $this->simulator->resolveBaseline(
                $userId, 
                null, 
                $sessionId,
                $validated['manual_baseline'] ?? []
            );

            if (!$baseline) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unable to resolve baseline data. Please run Reverse Goal Planner first.'
                ], 400);
            }

            // 3. Determine Goal Status (Server-Side Security)
            $goalStatus = 'Adjustable'; // Default
            if ($sessionId) {
                $session = \App\Models\ReverseGoalSession::find($sessionId);
                if ($session) {
                    // Map risk_level to goal_status
                    // Realistic -> Ready
                    // Challenging -> Adjustable
                    // High Risk -> Heavy
                    switch ($session->risk_level) {
                        case 'Realistic': $goalStatus = 'Ready'; break;
                        case 'Challenging': $goalStatus = 'Adjustable'; break;
                        case 'High Risk': $goalStatus = 'Heavy'; break;
                        default: $goalStatus = 'Adjustable';
                    }
                }
            }

            // 4. Run Simulation for Selected Zone (Single Focus)
            $result = $this->simulator->simulate(
                $baseline, 
                $validated['zone'], 
                $validated['level'],
                $goalStatus
            );

            // 5. Return Response
            return response()->json([
                'status' => 'success',
                'baseline' => $baseline,
                'result' => $result,
                'goal_status' => $goalStatus
            ]);

        } catch (\Exception $e) {
            Log::error('Profit Simulator Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Simulation failed: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Store Simulation Plan
     * Endpoint: POST /profit-simulator/store
     */
    public function store(Request $request)
    {
        try {
            // 1. Validation
            $validated = $request->validate([
                'session_id' => 'nullable|exists:reverse_goal_sessions,id',
                'zone' => 'required|string',
                'level' => 'required|integer',
                'baseline' => 'required|array',
                'result' => 'required|array',
            ]);

            $userId = auth()->id();
            // Fallback for business profile ID (assuming single profile for now or fetched from user)
            // Ideally should be passed or resolved from context
            $businessProfileId = 1; // Default for MVP

            // 2. Create Record
            $simulation = \App\Models\ProfitSimulation::create([
                'user_id' => $userId,
                'business_profile_id' => $businessProfileId,
                'reverse_goal_session_id' => $validated['session_id'],
                
                // Baseline
                'baseline_revenue' => $validated['baseline']['revenue'] ?? 0,
                'baseline_net_profit' => $validated['baseline']['net_profit'] ?? 0,
                'baseline_margin' => $validated['baseline']['margin'] ?? 0,
                'baseline_break_even_units' => 0, // Not always calculated in baseline array
                
                // Input
                'leverage_zone' => $validated['zone'],
                'adjustment_percentage' => $validated['result']['pct_change'] ?? 0,
                
                // Output (Need to calculate absolute projected values if not present in result)
                // The current result array contains 'delta_val', so we can reconstruct
                'projected_revenue' => 0, // Placeholder, logically should be calculated
                'projected_net_profit' => ($validated['baseline']['net_profit'] ?? 0) + ($validated['result']['delta_val'] ?? 0),
                'projected_margin' => 0, // Placeholder
                'projected_break_even_units' => 0, // Placeholder

                // Analysis
                'profit_delta' => $validated['result']['delta_val'] ?? 0,
                'effort_score' => $validated['level'], // 1, 2, 3 maps nicely
                'risk_multiplier' => 1.0, // Default
                'leverage_impact_index' => 0, // Default

                // Meta
                'primary_constraint' => $validated['result']['risk_label'] ?? 'Unknown',
                'mentor_focus_area' => $validated['result']['insight'] ?? '',
                'logic_version' => 'v2.0'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Plan saved successfully',
                'data' => $simulation
            ]);

        } catch (\Exception $e) {
            Log::error('Profit Simulator Store Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save plan: ' . $e->getMessage()
            ], 500);
        }
    }
}
