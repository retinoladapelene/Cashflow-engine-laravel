<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoadmapController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->roadmapProgress;
    }

    public function update(Request $request)
    {
        $request->validate([
            'step_id' => 'required',
            'status' => 'required|in:completed,unlocked',
        ]);

        $progress = $request->user()->roadmapProgress()->updateOrCreate(
            ['step_id' => $request->step_id],
            ['status' => $request->status, 'completed_at' => now()]
        );

        return response()->json($progress);
    }
}
