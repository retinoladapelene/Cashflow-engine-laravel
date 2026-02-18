<?php

namespace App\Services;

use App\Models\TermDefinition;
use App\Models\UserTermFamiliarity;
use App\Models\UserBehaviorLog;
use App\Models\ReverseGoalSession;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ContextEducationEngine
{
    /**
     * Evaluate context and return appropriate education components (tooltip, insight, prompt)
     */
    public function evaluateContext($userId, $action = null, $meta = [])
    {
        $context = $this->gatherContext($userId, $meta);
        
        $response = [
            'tooltip' => null,
            'contextual_insight' => null,
            'behavioral_prompt' => null
        ];

        // 1. Log behavior if action is provided
        if ($action) {
            $this->logBehavior($userId, $action, $meta);
        }

        // 2. Evaluate Layer 2: Context Insight (Situational)
        $response['contextual_insight'] = $this->evaluateInsights($context);

        // 3. Evaluate Layer 3: Guided Learning Prompt (Behavioral)
        $response['behavioral_prompt'] = $this->evaluatePrompts($userId, $context);

        return $response;
    }

    /**
     * Get a specific term with contextual data and record interaction
     */
    public function getTerm($termKey, $userId)
    {
        $term = TermDefinition::where('term_key', $termKey)->first();
        if (!$term) return null;

        $familiarity = $this->updateFamiliarity($userId, $termKey);
        $score = $this->calculateFamiliarityScore($familiarity);

        $data = [
            'term' => $term->term_key,
            'short_text' => $term->short_text,
            'long_text' => $term->long_text,
            'familiarity_score' => $score,
            'display_mode' => ($score > 5) ? 'short' : 'extended'
        ];

        // Apply contextual template if available
        if ($term->contextual_template) {
            $session = ReverseGoalSession::where('user_id', $userId)->latest()->first();
            $data['contextual_text'] = $this->parseTemplate($term->contextual_template, $session);
        }

        return $data;
    }

    protected function gatherContext($userId, $meta = [])
    {
        $session = ReverseGoalSession::where('user_id', $userId)->latest()->first();
        $behavior = UserBehaviorLog::where('user_id', $userId)->latest()->take(10)->get();
        
        return [
            'session' => $session,
            'behavior' => $behavior,
            'current_meta' => $meta
        ];
    }

    protected function evaluateInsights($context)
    {
        $session = $context['session'];
        if (!$session) return null;

        // Example Rule: Heavy Growth Status
        if ($session->risk_level === 'High Risk' || $session->risk_level === 'Challenging') {
            return [
                'type' => 'warning',
                'title' => 'Insight Strategis',
                'message' => "Dengan margin cuma " . ($session->assumed_margin) . "%, nge-gas traffic secara bar-bar itu high risk banget. Coba beresin conversion rate dulu biar lebih efisien.",
                'suggestion' => "Optimasi landing page dulu yuk sebelum bakar duit di iklan."
            ];
        }

        // Add more rule logic here or pull from DB
        $rules = TermDefinition::whereNotNull('trigger_rules')
            ->where('context_type', 'insight')
            ->get();

        foreach ($rules as $rule) {
            if ($this->checkRule($rule->trigger_rules, $context)) {
                return [
                    'type' => 'info',
                    'message' => $this->parseTemplate($rule->contextual_template, $session)
                ];
            }
        }

        return null;
    }

    protected function evaluatePrompts($userId, $context)
    {
        // Example Layer 3: Behavioral Trigger
        $behavior = $context['behavior'];
        
        // Count high risk selections in last 5 actions
        $highRiskCount = $behavior->where('selected_level', 'High Risk')->count();
        if ($highRiskCount >= 3) {
            return [
                'title' => 'Mini Coach',
                'message' => 'Liat kamu sering milih strategi yang agak bar-bar (high risk). Mau intip alternatif yang lebih safe tapi tetep cuan?',
                'action_text' => 'Lihat Opsi Stabil',
                'action_url' => '#'
            ];
        }

        return null;
    }

    public function updateFamiliarity($userId, $termKey)
    {
        $familiarity = UserTermFamiliarity::firstOrNew([
            'user_id' => $userId,
            'term_key' => $termKey
        ]);

        $familiarity->click_count++;
        $familiarity->last_interaction_at = now();
        $familiarity->save();

        return $familiarity;
    }

    public function calculateFamiliarityScore($familiarity)
    {
        $daysSinceLast = $familiarity->last_interaction_at 
            ? $familiarity->last_interaction_at->diffInDays(now()) 
            : 99;

        $bonus = ($daysSinceLast < 7) ? 1 : 0;
        $score = ($familiarity->click_count * 2) + $bonus;

        $familiarity->familiarity_score = $score;
        $familiarity->save();

        return $score;
    }

    protected function logBehavior($userId, $action, $meta = [])
    {
        return UserBehaviorLog::create([
            'user_id' => $userId,
            'action_type' => $action,
            'selected_zone' => $meta['zone'] ?? null,
            'selected_level' => $meta['level'] ?? null,
            'goal_status' => $meta['status'] ?? null,
            'meta' => $meta
        ]);
    }

    protected function checkRule($rules, $context)
    {
        // Simple rule expansion - can be made more complex
        foreach ($rules as $key => $value) {
            $session = $context['session'];
            if (!$session) return false;

            if ($key === 'risk_level' && $session->risk_level !== $value) return false;
            // Add more conditions
        }
        return true;
    }

    protected function parseTemplate($template, $session)
    {
        if (!$session) return $template;

        $vars = [
            '{{ margin }}' => $session->assumed_margin . '%',
            '{{ conversion }}' => $session->assumed_conversion . '%',
            '{{ traffic }}' => number_format($session->required_traffic),
            '{{ revenue }}' => 'Rp ' . number_format($session->target_profit),
            '{{ risk }}' => $session->risk_level
        ];

        return str_replace(array_keys($vars), array_values($vars), $template);
    }
}
