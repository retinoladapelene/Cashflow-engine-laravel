<?php

namespace App\Http\Controllers;

use App\Models\AdArsenal;
use Illuminate\Http\Request;

class AdArsenalController extends Controller
{
    // Public API
    public function index()
    {
        return AdArsenal::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    // Admin API
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'link' => 'required',
            'tag' => 'in:HOT,NEW,FOUNDATION,PREMIUM',
        ]);

        $ad = AdArsenal::create([
            'title' => $request->title,
            'description' => $request->description,
            'link' => $request->link,
            'tag' => $request->tag ?? 'NEW',
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->is_active ?? true,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($ad, 201);
    }

    public function update(Request $request, AdArsenal $adArsenal)
    {
        $adArsenal->update($request->all());
        return response()->json($adArsenal);
    }

    public function destroy(AdArsenal $adArsenal)
    {
        $adArsenal->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
