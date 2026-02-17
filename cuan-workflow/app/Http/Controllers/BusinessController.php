<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $profile = $request->user()->businessProfile;
        
        if (!$profile) {
            $profile = $request->user()->businessProfile()->create();
        }

        return response()->json($profile);
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
            'target_revenue' => 'numeric',
            'available_cash' => 'numeric',
            'max_capacity' => 'integer',
        ]);

        $profile = $request->user()->businessProfile;
        
        if (!$profile) {
            $profile = $request->user()->businessProfile()->create();
        }

        $profile->update($request->all());

        return response()->json($profile);
    }
}
