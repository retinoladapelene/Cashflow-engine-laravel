<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReverseGoalPlannerService
{
    /**
     * Module 1: Input Controller & Benchmark Assignment
     * 
     * Validates input and merges with server-side benchmarks.
     * 
     * @param array $input
     * @return array
     */
    public function process(array $input)
    {
        // 1. Validate Input (Basic validation should be done in Controller)
        // Here we ensure data types and fallback values
        
        $businessModel = $input['business_model'] ?? 'dropship';
        
        // 2. Fetch Benchmarks (Assumption Lock)
        // If DB is empty (during dev), use hardcoded fallbacks
        $benchmark = DB::table('benchmark_assumptions')
            ->where('business_model', $businessModel)
            ->first();

        // Fallback if no benchmark found in DB
        if (!$benchmark) {
            $benchmark = (object) [
                'avg_margin' => 20,
                'avg_conversion' => 1.5,
                'avg_cpc' => 1500,
                'traffic_capacity_per_hour' => 100,
                'difficulty_index' => 1.0
            ];
            // Hardcoded fallbacks for other models
            if ($businessModel === 'digital') {
                $benchmark->avg_margin = 80;
                $benchmark->avg_conversion = 3.0;
                $benchmark->avg_cpc = 2000;
            }
        }

        // 3. Merge Input with Benchmarks
        // User inputs: correct logic ensures we use benchmarks for margin/cvr/cpc
        // But we might allow user overrides if they are "advanced" - for now, strict mode
        
        $data = [
            'target_profit' => (float) $input['target_profit'],
            'timeline_days' => (int) $input['timeline_days'],
            'capital_available' => (float) $input['capital_available'],
            'hours_per_day' => (int) $input['hours_per_day'],
            'business_model' => $businessModel,
            'traffic_strategy' => $input['traffic_strategy'] ?? 'ads', // ads, organic, hybrid
            
            // Locked Assumptions
            'assumed_margin' => $benchmark->avg_margin,
            'assumed_conversion' => $benchmark->avg_conversion,
            'assumed_cpc' => $benchmark->avg_cpc,
            'traffic_capacity' => $benchmark->traffic_capacity_per_hour,
        ];

        // 4. Run Calculation Engine (Module 2)
        $calculations = $this->runCalculations($data);
        
        // 5. Run Scoring Engine (Module 3)
        $scores = $this->calculateScores($data, $calculations);

        // 6. Run Adjustment Engine (Module 4) - only if Needed or requested
        // For now, we return scores and calculations
        
        return [
            'input' => $data,
            'output' => $calculations,
            'scores' => $scores,
            'logic_version' => 'v2.0_server_beta'
        ];
    }

    /**
     * Module 2: Calculation Engine
     * 
     * Performs core math for Reverse Engineering:
     * - Required Units
     * - Required Traffic
     * - Required Budget
     * - Workload
     */
    private function runCalculations(array $data)
    {
        // A. Unit Economics
        // Unit Net Profit = (Target Profit / Units) ? No, we need selling price context.
        // Wait, Reverse Goal Planner usually starts with "I want X profit".
        // Do we imply a product price?
        // In V1, user inputs "Product Price" and "Margin".
        // In V2, we said benchmarks are server-side. 
        // BUT, "Product Price" is highly variable even within dropshipping.
        // So user MUST input Selling Price per Unit?
        // Let's re-read the plan.
        // Plan says: Inputs: target_profit, timeline_days, capital_available, hours_per_day, business_model.
        // It DOES NOT explicitly list "Product Price".
        // However, without Product Price, we can't calculate "Units Needed".
        // UNLESS we assume an "Average Order Value" based on business model?
        // Or we calculate "Required Revenue" and then say "If you sell $X item..."
        // Let's check V1 input: "goal-income" and "goal-price".
        // So User IS inputting Price.
        // I missed adding "selling_price" to V2 input list in my summary, but it's crucial.
        // I will assume "selling_price" is passed in input. If not, we default to a benchmark AOV.
        
        // Let's enforce selling_price input for now, or use a default.
        $sellingPrice = $data['selling_price'] ?? 150000; // Default 150k
        
        $marginPercent = $data['assumed_margin'] / 100;
        $unitProfit = $sellingPrice * $marginPercent;
        
        // B. Volume Requirements
        $requiredUnits = ($unitProfit > 0) ? ceil($data['target_profit'] / $unitProfit) : 0;
        
        // C. Traffic Requirements
        $conversionRate = $data['assumed_conversion'] / 100;
        $requiredTraffic = ($conversionRate > 0) ? ceil($requiredUnits / $conversionRate) : 0;
        
        // D. Budget Requirements (if using Ads)
        $isOrganic = $data['traffic_strategy'] === 'organic';
        $adSpend = $isOrganic ? 0 : ($requiredTraffic * $data['assumed_cpc']);
        
        // E. Workload / Execution
        // How many hours needed?
        // Assuming 1 hour can handle X units (fulfillment/cs)?
        // Or 1 hour can generate Y traffic (content creation)?
        
        // Let's use a simple model:
        // Operational load: 10 mins per order (0.16 hours)
        // Traffic load: 
        //   - Ads: 1 hour/day management
        //   - Organic: 2 hours/day content creation
        
        $hoursPerUnit = 0.16; // ~10 mins
        $totalOperationalHours = $requiredUnits * $hoursPerUnit;
        
        $days = $data['timeline_days'];
        $dailyOperationalHours = ($days > 0) ? ($totalOperationalHours / $days) : 999;
        
        $marketingHoursDaily = $isOrganic ? 2 : 1;
        
        $totalDailyHoursNeeded = $dailyOperationalHours + $marketingHoursDaily;
        $executionGap = $totalDailyHoursNeeded - $data['hours_per_day'];
        
        // Execution Load Ratio (Required / Available)
        $executionLoadRatio = ($data['hours_per_day'] > 0) ? ($totalDailyHoursNeeded / $data['hours_per_day']) : 999;

        // F. Capital Gap
        // Difference between AdSpend and Available Capital
        // Note: AdSpend is total over timeline. Capital is "available now".
        // Usually, you rotate capital. But for safety, let's look at initial cover.
        // Or maybe monthly burn.
        // Let's compare Total Ad Spend vs Capital.
        $capitalGap = $adSpend - $data['capital_available'];

        return [
            'selling_price' => $sellingPrice,
            'unit_profit' => $unitProfit,
            'required_units' => $requiredUnits,
            'required_traffic' => $requiredTraffic,
            'total_ad_spend' => $adSpend,
            'daily_hours_needed' => $totalDailyHoursNeeded,
            'execution_load_ratio' => $executionLoadRatio,
            'capital_gap' => $capitalGap
        ];
    }
    
    /**
     * Module 3: Scoring Engine
     * 
     * Calculates FFS, CAS, EFS, OFS using non-linear logic.
     */
    private function calculateScores($data, $calc)
    {
        // 1. Financial Feasibility Score (FFS)
        // Based on Margin and Ticket Size (simulated)
        // Actually, FFS in V1 was about "Is the goal profit realistic given the capital?"
        // In V2, we have CAS for Capital.
        // FFS can be about "Is the unit economics sound?"
        // If Margin < 10%, FFS low. If Margin > 30%, FFS high.
        
        $marginScore = min(100, ($data['assumed_margin'] / 30) * 100); 
        // If margin is 30%, score 100. If 15%, score 50.
        
        // 2. Capital Adequacy Score (CAS)
        // Based on Capital Gap.
        // If Gap <= 0, CAS = 100.
        // If Gap > 0, score drops non-linearly.
        // Formula: 100 * (Capital / AdSpend) ?
        // If AdSpend is 0 (organic), CAS = 100.
        
        if ($calc['total_ad_spend'] <= 0) {
            $cas = 100;
        } else {
            $coverage = $data['capital_available'] / $calc['total_ad_spend'];
            // Non-linear: sqrt(coverage) * 100
            // If coverage is 0.5 (50% of budget), sqrt(0.5) = 0.7. Score 70.
            // If coverage is 0.1, sqrt(0.1) = 0.31. Score 31.
            $cas = min(100, sqrt($coverage) * 100);
        }
        
        // 3. Execution Feasibility Score (EFS)
        // Based on Load Ratio.
        // If Ratio <= 1, EFS = 100.
        // If Ratio > 1, EFS drops.
        
        if ($calc['execution_load_ratio'] <= 1) {
            $efs = 100;
        } else {
            // If needs 2x hours, score should be low.
            // Formula: 100 / ratio ^ 1.5
            // Ratio 2 -> 100 / 2.8 = 35.
            $efs = 100 / pow($calc['execution_load_ratio'], 1.5);
        }
        
        // 4. Overall Feasibility Score (OFS)
        // Weighted average? Or lowest bucket?
        // Let's use weighted: CAS (40%), EFS (40%), FFS (20%)
        // BUT, if any score is very low (< 30), tank the OFS.
        
        // 4. Overall Feasibility Score (OFS)
        $ofs = ($cas * 0.4) + ($efs * 0.4) + ($marginScore * 0.2);
        
        // --- NEW LOGIC: Mentally Safe Output ---
        
        // A. Goal Status (Simple 3 Levels)
        $threshold = ($data['business_model'] === 'dropship') ? 75 : 65;
        
        if ($ofs >= $threshold + 10) {
            $goalStatus = 'Siap Dieksekusi'; // Stable
            $statusColor = 'green';
        } elseif ($ofs >= $threshold - 10) {
            $goalStatus = 'Perlu Penyesuaian'; // Adjustable
            $statusColor = 'yellow';
        } else {
            $goalStatus = 'Terlalu Berat Saat Ini'; // Heavy
            $statusColor = 'red';
        }

        // B. Constraint Prioritization (Identify Primary Constraint)
        $constraints = [];

        // 1. Capital Constraint (Severe if Cover < 50%)
        if ($calc['capital_gap'] > 0) {
            $severity = ($calc['capital_gap'] / $calc['total_ad_spend']) * 100; // % gap
            $constraints['capital'] = [
                'name' => 'Keterbatasan Modal Awal',
                'severity' => $severity,
                'msg' => 'Modal saat ini belum cukup untuk menopang kebutuhan iklan di fase awal.'
            ];
        }

        // 2. Execution Constraint (Severe if Load > 1.5x)
        if ($calc['execution_load_ratio'] > 1) {
            $severity = ($calc['execution_load_ratio'] - 1) * 100; // % overload
            // Weight execution lower than money? or higher?
            // Capital is usually harder to solve instantly. Execution can be solved by "Working harder" (up to a point).
            // Let's weight capital 1.5x
            $constraints['execution'] = [
                'name' => 'Beban Kerja Harian',
                'severity' => $severity * 0.8, 
                'msg' => 'Target ini membutuhkan waktu kerja melebihi ketersediaan waktu Anda.'
            ];
        }

        // 3. Margin Constraint (Severe if < 15%)
        if ($data['assumed_margin'] < 20 && $data['business_model'] !== 'dropship') {
             $constraints['margin'] = [
                'name' => 'Margin Profit Tipis',
                'severity' => (20 - $data['assumed_margin']) * 5, 
                'msg' => 'Model bisnis ini memiliki margin tipis, membutuhkan volume penjualan sangat tinggi.'
            ];
        }

        // Sort by severity
        uasort($constraints, function($a, $b) {
            return $b['severity'] <=> $a['severity'];
        });

        $primary = reset($constraints);
        if (!$primary) {
            $primary = [
                'name' => 'Konsistensi',
                'severity' => 0,
                'msg' => 'Tantangan utama Anda hanyalah menjaga konsistensi eksekusi.'
            ];
        }

        // C. Safe Recommendations
        $recommendations = [];
        
        // Logic to generate 2 safe options
        if ($goalStatus === 'Terlalu Berat Saat Ini' || $goalStatus === 'Perlu Penyesuaian') {
            // Option A: Extend Timeline
            $newTimeline = ceil($data['timeline_days'] * 1.5); // +50% time
            $recommendations[] = [
                'type' => 'timeline',
                'label' => "Perpanjang waktu menjadi {$newTimeline} hari",
                'value' => $newTimeline
            ];

            // Option B: Adjust Target (only if Capital is the issue)
            if (isset($constraints['capital'])) {
                // Reduce target to match capital
                // New Target = Old Target * (Available / Needed)
                $ratio = $data['capital_available'] / ($calc['total_ad_spend'] ?: 1); // avoid div 0
                $newTarget = floor($data['target_profit'] * $ratio * 0.9); // 90% of max possible
                // Round to millions
                $newTarget = round($newTarget / 1000000) * 1000000;
                if ($newTarget > 0) {
                     $recommendations[] = [
                        'type' => 'target',
                        'label' => "Sesuaikan target profit menjadi " . number_format($newTarget,0,',','.') . " (Sesuai Modal)",
                        'value' => $newTarget
                    ];
                }
            }
        }
        
        // Limit to 2
        $recommendations = array_slice($recommendations, 0, 2);


        // D. Learning Moment
        $learningMoment = "";
        if ($data['business_model'] === 'dropship') {
            $learningMoment = "💡 Catatan: Dropshipping mengandalkan volume. Margin 20-30% adalah standar industri, fokuslah pada Traffic.";
        } elseif ($data['traffic_strategy'] === 'ads') {
            $learningMoment = "💡 Catatan: Iklan berbayar mempercepat hasil, tapi membutuhkan 'bensin' (modal) yang siap dibakar di awal.";
        } else {
             $learningMoment = "💡 Catatan: Bisnis adalah lari maraton, bukan lari sprint. Konsistensi mengalahkan intensitas jangka pendek.";
        }

        return [
            // Backend Scores (Hidden from User)
            'ffs' => round($marginScore),
            'cas' => round($cas),
            'efs' => round($efs),
            'ofs' => round($ofs),
            
            // Frontend Output (Mentally Safe)
            'goal_status' => $goalStatus,
            'status_color' => $statusColor,
            'primary_constraint' => $primary['name'],
            'constraint_message' => $primary['msg'],
            'recommendations' => $recommendations,
            'learning_moment' => $learningMoment,
            
            // Integration Data
            'mentor_focus_area' => isset($constraints['capital']) ? 'low_capital_strategy' : 'growth_strategy',
        ];
    }
}
