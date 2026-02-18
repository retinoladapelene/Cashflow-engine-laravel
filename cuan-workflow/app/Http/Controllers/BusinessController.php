<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $settings = \App\Models\SystemSetting::all()->pluck('value', 'key');
        
        $latestSession = null;
        $latestSimulation = null;

        if (Auth::check()) {
            $latestSession = \App\Models\ReverseGoalSession::where('user_id', Auth::id())
                ->latest()
                ->first();

            if ($latestSession) {
                $latestSimulation = \App\Models\ProfitSimulation::where('reverse_goal_session_id', $latestSession->id)
                    ->latest()
                    ->first();
            }
        }

        return view('index', [
            'settings' => $settings,
            'latestSession' => $latestSession,
            'latestSimulation' => $latestSimulation
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'selling_price' => 'numeric',
            'variable_costs' => 'numeric',
            'fixed_costs' => 'numeric',
            'traffic' => 'integer',
            'conversion_rate' => 'numeric',
            'ad_spend' => 'numeric',
            'ad_spend' => 'numeric',
            'target_revenue' => 'numeric',
            'available_cash' => 'numeric',
            'max_capacity' => 'integer',
        ]);

        \Illuminate\Support\Facades\Log::info('Business Update Request:', $request->all());

        $profile = $request->user()->businessProfile;
        
        if (!$profile) {
            $profile = $request->user()->businessProfile()->create();
        }

        $profile->update($request->all());

        return response()->json($profile);
    }
}
