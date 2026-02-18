<?php

namespace App\Http\Controllers;

use App\Services\ContextEducationEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EducationController extends Controller
{
    protected $educationEngine;

    public function __construct(ContextEducationEngine $educationEngine)
    {
        $this->educationEngine = $educationEngine;
    }

    /**
     * Evaluate context and return education insights/prompts
     */
    public function evaluateContext(Request $request)
    {
        $userId = Auth::id() ?? $request->header('X-User-ID'); // Fallback for session-based if needed
        $action = $request->input('action');
        $meta = $request->only(['zone', 'level', 'status', 'term_key']);

        $evaluation = $this->educationEngine->evaluateContext($userId, $action, $meta);

        return response()->json([
            'success' => true,
            'data' => $evaluation
        ]);
    }

    /**
     * Get detailed term definition and record interaction
     */
    public function getTerm(Request $request, $termKey)
    {
        $userId = Auth::id() ?? $request->header('X-User-ID');
        
        $termData = $this->educationEngine->getTerm($termKey, $userId);

        if (!$termData) {
            return response()->json([
                'success' => false,
                'message' => 'Term not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $termData
        ]);
    }

    /**
     * Record a manual behavior log entry
     */
    public function logBehavior(Request $request)
    {
        $userId = Auth::id() ?? $request->header('X-User-ID');
        $action = $request->input('action');
        $meta = $request->input('meta', []);

        $this->educationEngine->evaluateContext($userId, $action, $meta);

        return response()->json([
            'success' => true
        ]);
    }
}
