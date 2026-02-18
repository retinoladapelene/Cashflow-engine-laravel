<?php

namespace App\Services;

use App\Models\Roadmap;
use App\Models\Simulation;
use App\Models\StrategyBlueprint;
use App\Models\RoadmapStep;
use App\Models\RoadmapAction;
use Illuminate\Support\Facades\DB;

class RoadmapGeneratorService
{
    public static function generate(Simulation $simulation)
    {
        return DB::transaction(function () use ($simulation) {
            // 1. Analyze & Generate Tags
            $tags = self::generateTags($simulation);
            
            // Save tags to simulation
            $simulation->update(['generated_tags' => $tags]);

            // 2. Create Roadmap Record
            $roadmap = Roadmap::create([
                'user_id' => $simulation->user_id,
                'simulation_id' => $simulation->id,
                'status' => 'active',
                'total_steps' => 0,
                'completed_steps' => 0
            ]);

            // 3. Blueprint Merging & Step Creation
            self::buildSteps($roadmap, $tags);

            return $roadmap->load('steps.actions');
        });
    }

    private static function generateTags(Simulation $simulation)
    {
        $tags = [];
        $data = $simulation->result_data; // {margin: 0.08, net_profit: ...}
        // Need to ensure result_data has keys we expect.
        // FinancialEngine returns: revenue, gross_profit, net_profit, margin, etc.
        
        $margin = $data['margin'] ?? 0;
        $breakEvenTime = $simulation->health_score['break_even_time'] ?? 99; // In months? Not in engine yet.
        // Let's use simple logic based on what we have.
        
        // A. Survival / Efficiency
        if ($margin < 0.15) {
            $tags[] = 'Margin Improvement';
        }

        // B. Traffic / Growth
        // If traffic < break even traffic -> Traffic Scaling (Urgent)
        // If traffic > break even but low growth -> Traffic Scaling
        $tags[] = 'Traffic Scaling'; // Almost always needed for growth

        // C. Monetization / Upsell
        // Unless margin is terrible, Upsell is good.
        if ($margin > 0.05) {
            $tags[] = 'Monetization Expansion';
        }
        
        return array_unique($tags);
    }

    private static function buildSteps(Roadmap $roadmap, array $tags)
    {
        // Fetch Blueprints
        $blueprints = StrategyBlueprint::whereIn('strategy_tag', $tags)
            ->orderBy('priority_level', 'asc') // 1 (Survival) First
            ->get();

        // If no DB blueprints (e.g. first run), fallback to code defaults
        if ($blueprints->isEmpty()) {
            $blueprints = self::getFallbackBlueprints($tags);
        }

        $order = 1;

        foreach ($blueprints as $bp) {
            // Create Step
            $step = RoadmapStep::create([
                'roadmap_id' => $roadmap->id,
                'title' => $bp->step_title, // Use attribute access
                'description' => $bp->step_description,
                'order' => $order,
                'status' => $order === 1 ? 'unlocked' : 'locked',
                'strategy_tag' => $bp->strategy_tag
            ]);

            // Create Actions
            $actions = is_string($bp->default_actions) ? json_decode($bp->default_actions, true) : $bp->default_actions;
            
            if (is_array($actions)) {
                foreach ($actions as $actionText) {
                    RoadmapAction::create([
                        'step_id' => $step->id,
                        'action_text' => $actionText,
                        'is_completed' => false
                    ]);
                }
            }
            
            $order++;
        }

        $roadmap->update(['total_steps' => $order - 1]);
    }

    private static function getFallbackBlueprints(array $tags)
    {
        // Temporary hardcoded until seeder is run
        $collection = collect();
        
        if (in_array('Margin Improvement', $tags)) {
            $collection->push((object)[
                'strategy_tag' => 'Margin Improvement',
                'step_title' => 'Audit Structure Biaya & COGS',
                'step_description' => 'Margin Anda di bawah 15%. Saatnya bedah pengeluaran.',
                'default_actions' => ['List semua Fixed Cost bulanan', 'Cek harga modal (COGS) vs Harga Jual', 'Negosiasi ulang dengan supplier', 'Eliminasi biaya operasional tidak penting'],
                'priority_level' => 1
            ]);
            $collection->push((object)[
                'strategy_tag' => 'Margin Improvement',
                'step_title' => 'Optimasi Pricing Strategy',
                'step_description' => 'Harga jual mungkin terlalu rendah.',
                'default_actions' => ['Riset harga kompetitor', 'Cek elastisitas harga (bisa naik 10%?)', 'Buat paket bundling (High Margin)'],
                'priority_level' => 1
            ]);
        }
        
        if (in_array('Traffic Scaling', $tags)) {
            $collection->push((object)[
                'strategy_tag' => 'Traffic Scaling',
                'step_title' => 'Distribusi Konten Organik',
                'step_description' => 'Tingkatkan traffic tanpa biaya iklan.',
                'default_actions' => ['Buat Content Calendar 1 Bulan', 'Posting di TikTok/Reels setiap hari', 'Optimasi Bio & Link'],
                'priority_level' => 2
            ]);
             $collection->push((object)[
                'strategy_tag' => 'Traffic Scaling',
                'step_title' => 'Paid Traffic Experiment',
                'step_description' => 'Mulai mendatangkan traffic berbayar.',
                'default_actions' => ['Setup Meta Ads Manager', 'Siapkan Creative (Image/Video)', 'Test Budget Kecil (50rb/hari)'],
                'priority_level' => 2
            ]);
        }

        if (in_array('Monetization Expansion', $tags)) {
             $collection->push((object)[
                'strategy_tag' => 'Monetization Expansion',
                'step_title' => 'Upsell & Cross-sell System',
                'step_description' => 'Tingkatkan nilai transaksi rata-rata (AOV).',
                'default_actions' => ['Tentukan produk penamping (Upsell)', 'Buat script penawaran tambahan', 'Test penawaran di halaman checkout'],
                'priority_level' => 3
            ]);
        }
        
        // Sort by Priority
        return $collection->sortBy('priority_level');
    }
}
