<?php

namespace App\Services;

use App\DTO\BusinessInputDTO;

class FinancialEngine
{
    public static function calculateBaseline(BusinessInputDTO $input): array
    {
        $revenue = $input->traffic * ($input->conversion / 100) * $input->price;
        
        // Gross Profit = Revenue - COGS
        // COGS = Traffic * Conversion * Cost Per Unit
        $unitsSold = $input->traffic * ($input->conversion / 100);
        $cogs = $unitsSold * $input->cost;
        
        $grossProfit = $revenue - $cogs;

        $netProfit = $grossProfit - $input->fixed_cost;

        $margin = $input->price > 0 ? ($input->price - $input->cost) / $input->price : 0;

        return [
            'revenue' => $revenue,
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit,
            'margin' => $margin,
            'units_sold' => $unitsSold,
            // Pass inputs back for simulation
            'traffic' => $input->traffic,
            'conversion_rate' => $input->conversion, // Raw % e.g. 2.5
            'price' => $input->price,
            'cost' => $input->cost,
            'fixed_cost' => $input->fixed_cost
        ];
    }
}
