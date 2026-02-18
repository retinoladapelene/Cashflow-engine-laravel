<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReverseGoalPlannerService;
use App\Models\ReverseGoalSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReverseGoalPlannerController extends Controller
{
    protected $plannerService;

    public function __construct(ReverseGoalPlannerService $plannerService)
    {
        $this->plannerService = $plannerService;
    }

    /**
     * Handle the Reverse Goal Planning calculation.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculate(Request $request)
    {
        // 1. Validate Input
        $validated = $request->validate([
            'target_profit' => 'required|numeric|min:0',
            'timeline_days' => 'required|integer|min:1',
            'capital_available' => 'required|numeric|min:0',
            'hours_per_day' => 'required|integer|min:1|max:24',
            'business_model' => 'required|string|in:dropship,digital,service,stock,affiliate',
            'traffic_strategy' => 'required|string|in:organic,ads,hybrid',
            'selling_price' => 'nullable|numeric|min:0', // Optional context
        ]);

        try {
            // 2. Process via Service
            $result = $this->plannerService->process($validated);

            // 3. Save Session (if user is authenticated)
            $sessionId = null;
            if (Auth::check()) {
                $session = $this->saveSession(Auth::id(), $result);
                if ($session) {
                    $sessionId = $session->id;
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $result,
                'session_id' => $sessionId
            ]);

        } catch (\Exception $e) {
            Log::error('Reverse Goal Planner Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Calculation failed. Please check your inputs.'
            ], 500);
        }
    }

    /**
     * Save the calculation session to the database.
     */
    private function saveSession($userId, $result)
    {
        try {
            return ReverseGoalSession::create([
                'user_id' => $userId,
                'logic_version' => $result['logic_version'],
                'constraint_snapshot' => json_encode([
                    'capital_gap' => $result['output']['capital_gap'],
                    'execution_load_ratio' => $result['output']['execution_load_ratio'],
                    'difficulty_index' => 1.0 // Placeholder
                ]),
                'is_stable_plan' => ($result['scores']['ofs'] >= 75), // Initial definition
                
                // Inputs
                'business_model' => $result['input']['business_model'],
                'traffic_strategy' => $result['input']['traffic_strategy'],
                'target_profit' => $result['input']['target_profit'],
                'timeline_days' => $result['input']['timeline_days'],
                'capital_available' => $result['input']['capital_available'],
                'hours_per_day' => $result['input']['hours_per_day'],

                // Assumptions
                'assumed_margin' => $result['input']['assumed_margin'],
                'assumed_conversion' => $result['input']['assumed_conversion'],
                'assumed_cpc' => $result['input']['assumed_cpc'],

                // Outputs
                'unit_net_profit' => $result['output']['unit_profit'],
                'required_units' => $result['output']['required_units'],
                'required_traffic' => $result['output']['required_traffic'],
                'required_ad_budget' => $result['output']['total_ad_spend'],
                'execution_load_ratio' => $result['output']['execution_load_ratio'],

                // Scores
                'financial_score' => $result['scores']['ffs'],
                'capital_score' => $result['scores']['cas'],
                'execution_score' => $result['scores']['efs'],
                'overall_score' => $result['scores']['ofs'],
                'risk_level' => $result['scores']['risk_level'],
            ]);
        } catch (\Exception $e) {
            // Don't fail the request if saving fails, just log it
            Log::error('Failed to save Reverse Goal Session: ' . $e->getMessage());
            return null;
        }
    }
}
