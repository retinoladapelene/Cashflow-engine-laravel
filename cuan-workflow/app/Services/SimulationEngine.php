<?php

namespace App\Services;

use App\DTO\BusinessInputDTO;

class SimulationEngine
{
    public static function simulateScenario(array $baseline, array $changes)
    {
        // Changes are percentage deltas (e.g., 0.1 for +10%)
        // Except conversion which is absolute delta (e.g., +1%)

        $traffic = $baseline['traffic'] * (1 + ($changes['traffic_pct'] ?? 0));
        
        // Price Elasticity Logic: If price increase, conversion drops
        // Assumption: 10% price increase -> 5% conversion drop relative to base
        $priceChangePct = $changes['price_pct'] ?? 0;
        $elasticityFactor = 0.5; // 0.5 means 1% price increase leads to 0.5% conversion drop
        
        $elasticityImpact = 0;
        if ($priceChangePct > 0) {
            $elasticityImpact = -1 * ($priceChangePct * $elasticityFactor * $baseline['conversion_rate']); 
            // Wait, conversion rate is often small (e.g., 2%). 
            // If price +10%, conversion drops by 0.1 * 0.5 * 2 = 0.1% absolute? Or relative?
            // "conversion drops slightly". Let's say relative.
            // New Conversion = Old Conversion * (1 - (PriceChange * Elasticity))
            
            // Re-reading user request: "Jika price naik -> conversion turun sedikit".
            // Let's apply relative drop.
            // If Price +30%, Conversion drops 15% (relative).
        }

        $price = $baseline['price'] * (1 + $priceChangePct);
        
        $baseConversion = $baseline['conversion_rate'];
        $conversionDelta = $changes['conversion_delta'] ?? 0; // Absolute addition like +1%
        
        // Apply elasticity (relative drop logic)
        $conversionElasticityMultiplier = 1;
        if ($priceChangePct > 0) {
             $conversionElasticityMultiplier = 1 - ($priceChangePct * $elasticityFactor);
        }
        
        $conversion = ($baseConversion * $conversionElasticityMultiplier) + $conversionDelta;
        
        // Prevent negative conversion
        if ($conversion < 0) $conversion = 0;

        $revenue = $traffic * ($conversion / 100) * $price;

        return [
            'new_revenue' => $revenue,
            'revenue_change' => $baseline['revenue'] > 0 ? ($revenue - $baseline['revenue']) / $baseline['revenue'] : 0,
            'traffic' => $traffic,
            'conversion' => $conversion,
            'price' => $price
        ];
    }

    public static function sensitivity(BusinessInputDTO $input)
    {
        $base = FinancialEngine::calculateBaseline($input);

        // Test +10% scenarios
        $trafficUp = FinancialEngine::calculateBaseline(
            $input->withTraffic($input->traffic * 1.1)
        );

        $conversionUp = FinancialEngine::calculateBaseline(
            $input->withConversion($input->conversion * 1.1) // 10% relative increase
        );

        $priceUp = FinancialEngine::calculateBaseline(
            $input->withPrice($input->price * 1.1)
        );
        
        // Elasticity Check for Price Sensitivity? 
        // Standard sensitivity analysis usually isolates variables (ceteris paribus).
        // So we do NOT apply elasticity here to see pure price impact.
        // User asked for "Uji masing-masing variabel +10% secara terpisah".

        return [
            'traffic_impact' => $trafficUp['revenue'] - $base['revenue'],
            'conversion_impact' => $conversionUp['revenue'] - $base['revenue'],
            'price_impact' => $priceUp['revenue'] - $base['revenue'],
        ];
    }

    public static function breakEven(BusinessInputDTO $input)
    {
        $cm = $input->price - $input->cost;

        if ($cm <= 0) {
            return ['error' => 'No contribution margin (Price <= Cost)'];
        }

        $beUnits = $input->fixed_cost > 0 ? $input->fixed_cost / $cm : 0;
        $beTraffic = $input->conversion > 0 ? $beUnits / ($input->conversion / 100) : 0;

        return [
            'break_even_units' => ceil($beUnits),
            'break_even_traffic' => ceil($beTraffic),
            'is_traffic_warning' => $beTraffic > $input->traffic
        ];
    }

    public static function upsell(BusinessInputDTO $input, float $upsellPrice, float $takeRate)
    {
        $unitsSold = $input->traffic * ($input->conversion / 100);
        
        $upsellUnits = $unitsSold * ($takeRate / 100);
        $upsellRevenue = $upsellUnits * $upsellPrice;
        
        $baseRevenue = $unitsSold * $input->price;
        $totalRevenue = $baseRevenue + $upsellRevenue;

        return [
            'base_revenue' => $baseRevenue,
            'upsell_revenue' => $upsellRevenue,
            'total_revenue' => $totalRevenue,
            'increase_pct' => $baseRevenue > 0 ? ($upsellRevenue / $baseRevenue) * 100 : 0
        ];
    }
}
