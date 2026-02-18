<?php

namespace App\Services;

use App\Models\ReverseGoalSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfitSimulatorService
{
    /**
     * Module 1: Baseline Resolver
     * Get baseline data from Reverse Goal Session (Preferred) or Manual Input.
     */
    public function resolveBaseline($userId, $businessProfileId, $sessionId = null, $manualInput = [])
    {
        // 1. Try to load from Session ID if provided
        if ($sessionId) {
            $session = ReverseGoalSession::find($sessionId);
            if ($session) {
                // Determine revenue from profit and margin? 
                // We need explicit Price and COGS or at least Revenue.
                // Assuming session stores `unit_net_profit` and `required_units`.
                // Revenue = Units * Price.
                // If Price is not in session, we rely on manualInput or fetch from business profile.
                
                $price = $manualInput['current_price'] ?? 0;
                $cogs = $manualInput['current_cogs'] ?? 0;
                $fixedCost = $manualInput['fixed_cost'] ?? 0;
                $adSpend = $session->required_ad_budget;

                // Validate critical inputs
                if ($price <= 0) return null; // Cannot simulate without price

                // Reconstruct baseline financial snapshot
                $units = $session->required_units;
                $revenue = $units * $price;
                $variableCost = $units * $cogs;
                $grossProfit = $revenue - $variableCost;
                $netProfit = $grossProfit - $fixedCost - $adSpend;
                $margin = ($revenue > 0) ? ($netProfit / $revenue) * 100 : 0;
                
                // Break Even (Units) = Fixed Cost / (Price - Variable Cost)
                $unitMargin = $price - $cogs;
                $breakEvenUnits = ($unitMargin > 0) ? ceil(($fixedCost + $adSpend) / $unitMargin) : 0;

                return [
                    'source' => 'reverse_goal',
                    'session_id' => $session->id,
                    'price' => $price,
                    'cogs' => $cogs,
                    'traffic' => $session->required_traffic,
                    'conversion_rate' => $session->assumed_conversion,
                    'ad_spend' => $adSpend,
                    'fixed_cost' => $fixedCost,
                    
                    'revenue' => $revenue,
                    'net_profit' => $netProfit,
                    'margin' => $margin,
                    'break_even_units' => $breakEvenUnits,
                ];
            }
        }

        // 2. Manual Baseline (if no session or session invalid)
        if (!empty($manualInput)) {
            // Validation
            if (!isset($manualInput['price'], $manualInput['traffic'], $manualInput['conversion_rate'])) return null;

            $price = $manualInput['price'];
            $traffic = $manualInput['traffic'];
            $conv = $manualInput['conversion_rate'];
            $cogs = $manualInput['cogs'] ?? 0;
            $fixedCost = $manualInput['fixed_cost'] ?? 0;
            $adSpend = $manualInput['ad_spend'] ?? 0;

            $units = floor($traffic * ($conv / 100));
            $revenue = $units * $price;
            $variableCost = $units * $cogs;
            $netProfit = $revenue - $variableCost - $fixedCost - $adSpend;
            $margin = ($revenue > 0) ? ($netProfit / $revenue) * 100 : 0;
            $unitMargin = $price - $cogs;
            $breakEvenUnits = ($unitMargin > 0) ? ceil(($fixedCost + $adSpend) / $unitMargin) : 0;

            return [
                'source' => 'manual',
                'session_id' => null,
                'price' => $price,
                'cogs' => $cogs,
                'traffic' => $traffic,
                'conversion_rate' => $conv,
                'ad_spend' => $adSpend,
                'fixed_cost' => $fixedCost,
                
                'revenue' => $revenue,
                'net_profit' => $netProfit,
                'margin' => $margin,
                'break_even_units' => $breakEvenUnits,
            ];
        }

        return null;
    }

    /**
     * Module 2: Simulation Engine (Constraint-Aware)
     */
    /**
     * Module 2: Simulation Engine (Constraint-Aware & Mentally Safe)
     * 
     * @param array $baseline
     * @param string $zone
     * @param int $level (1, 2, 3)
     * @param string $goalStatus (Ready, Adjustable, Heavy)
     */
    public function simulate($baseline, $zone, $level, $goalStatus = 'Adjustable')
    {
        // 1. Level Mapping (Zone-Specific Realism)
        $adjustments = [
            'traffic' => [1 => 10, 2 => 20, 3 => 35],       // High Capital Risk
            'conversion' => [1 => 5, 2 => 10, 3 => 20],     // High Implementation Risk
            'pricing' => [1 => 3, 2 => 7, 3 => 12],         // High Conversion Risk
            'cost' => [1 => -5, 2 => -10, 3 => -15]         // High Operational Risk
        ];

        $pct = $adjustments[$zone][$level] ?? 0;
        $multiplier = 1 + ($pct / 100);

        // 2. Stability Modifier (The "Reality Guardrail")
        // If the base goal is shaky, optimization yields less result (dampener)
        // or we just flag it. The user asked to "dampen" the impact.
        $dampener = 1.0;
        if ($goalStatus === 'Adjustable') $dampener = 0.85;
        if ($goalStatus === 'Heavy') $dampener = 0.50; // Drastic reduction

        // Apply Adjustment
        $newBaseline = $baseline;
        
        switch ($zone) {
            case 'traffic':
                // Traffic increases linearly, but result is dampened
                $realTrafficParams = $baseline['traffic'] * $multiplier;
                // Apply dampener to the *effective* traffic that converts? 
                // Or just to the specific delta?
                // Let's apply dampener to value-add.
                
                $newBaseline['traffic'] = $realTrafficParams;
                // Cost also increases
                if ($baseline['ad_spend'] > 0) {
                    $newBaseline['ad_spend'] *= $multiplier; 
                }
                break;
            case 'conversion':
                // Conversion increase
                $newBaseline['conversion_rate'] *= $multiplier;
                if ($newBaseline['conversion_rate'] > 15) $newBaseline['conversion_rate'] = 15; // Hard cap
                break;
            case 'pricing':
                $newBaseline['price'] *= $multiplier;
                break;
            case 'cost':
                // Cost reduction (multiplier is < 1 e.g. 0.95)
                $newBaseline['cogs'] *= $multiplier;
                break;
        }

        // 3. Recalculate Financials
        $newUnits = floor($newBaseline['traffic'] * ($newBaseline['conversion_rate'] / 100));
        $newRevenue = $newUnits * $newBaseline['price'];
        $newVariableCost = $newUnits * $newBaseline['cogs'];
        $newNetProfit = $newRevenue - $newVariableCost - $newBaseline['fixed_cost'] - $newBaseline['ad_spend'];
        
        // 4. Calculate Range (Mentally Safe Output)
        // Instead of exact number, we show a range based on probability
        $delta = $newNetProfit - $baseline['net_profit'];
        
        // Apply Dampener to Delta (this represents the "friction" of a bad business model)
        $delta *= $dampener;
        $finalProfit = $baseline['net_profit'] + $delta;

        // Range: +/- 10% of the delta (uncertainty)
        $minProfit = $finalProfit - (abs($delta) * 0.1);
        $maxProfit = $finalProfit + (abs($delta) * 0.1);

        // 5. Effort & Risk Labeling
        $effort = 'Rendah';
        $risk = 'Stabil';
        $riskLabel = 'Resiko Minimal';

        if ($level == 2) {
            $effort = 'Sedang';
            $risk = 'Moderat';
        }
        if ($level == 3) {
            $effort = 'Tinggi';
            $risk = 'Tinggi';
        }

        // Specific Contextual Risk
        if ($level == 3) {
            switch($zone) {
                case 'traffic': $riskLabel = 'Resiko Modal Tinggi'; break;
                case 'conversion': $riskLabel = 'Resiko Implementasi'; break;
                case 'pricing': $riskLabel = 'Resiko Penurunan Konversi'; break;
                case 'cost': $riskLabel = 'Resiko Kualitas'; break;
            }
        }

        // 6. Reflection Prompt
        $prompt = $this->getReflectionPrompt($zone, $level);

        return [
            'zone' => $zone,
            'level' => $level,
            'pct_change' => $pct,
            'goal_status' => $goalStatus,
            'projected_range' => [
                'min' => $minProfit,
                'max' => $maxProfit,
                'label' => 'Rp ' . number_format($minProfit, 0, ',', '.') . ' - ' . number_format($maxProfit, 0, ',', '.')
            ],
            'delta_val' => $delta,
            'effort_level' => $effort,
            'risk_level' => $risk,
            'risk_label' => $riskLabel,
            'reflection_prompt' => $prompt,
            'insight' => $this->generateMicroLearning($zone)
        ];
    }

    private function getReflectionPrompt($zone, $level)
    {
        if ($level == 1) return "Perubahannya emang tipis-tipis, tapi kamu sanggup gak konsisten eksekusi ini selama 30 hari?";
        if ($level == 2) return "Ini butuh fokus ekstra. Udah ready buat alokasiin 1-2 jam/hari cuma buat ini?";
        
        switch ($zone) {
            case 'traffic': return "High stakes bro. Kamu ada cadangan cash gak kalo misal iklannya gak langsung convert sesuai ekspektasi?";
            case 'conversion': return "Skill issue alert! Kamu punya keahlian buat bedah landing page sendiri atau ada budget buat hire pro?";
            case 'pricing': return "Market resistance itu nyata. Udah siap mental kehilangan beberapa customer demi margin yang lebih tebel?";
            case 'cost': return "Quality risk. Bisa gak kamu pangkas biaya tanpa bikin customer kamu jadi ilfeel sama produknya?";
        }
        return "Yakin kamu mau gas yang ini?";
    }

    private function generateMicroLearning($zone)
    {
        switch ($zone) {
            case 'traffic': return "Buat pemula, paid traffic itu jalur paling sat-set tapi resikonya juga paling gede. Pastiin konversi kamu udah legit dulu.";
            case 'conversion': return "Ninggiin conversion rate itu satu-satunya cara 'gratis' buat naikin profit 2x lipat. Mastery di sini dulu, baru gas yang lain.";
            case 'pricing': return "Harga itu soal persepsi. Naik dikit seringnya gak berasa bagi buyer, tapi ngefek banget ke kantong kamu.";
            case 'cost': return "Efisiensi itu kunci, tapi jangan sampe pangkas biaya sampe ngerusak pengalaman belanja customer kamu.";
        }
    }

}
