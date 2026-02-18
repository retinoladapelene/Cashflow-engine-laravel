<?php

namespace App\Services;

use App\DTO\BusinessInputDTO;

class PlannerPresetService
{
    private static $presets = [
        'digital' => ['conversion' => 2.0, 'margin' => 0.8],
        'physical' => ['conversion' => 1.2, 'margin' => 0.4],
        'service' => ['conversion' => 5.0, 'margin' => 0.6],
    ];

    public static function getPreset(string $type): array
    {
        return self::$presets[$type] ?? self::$presets['digital'];
    }

    public static function calculateFeasibility(float $modal, float $estimatedAdCost, float $timeAvailable, string $type): array
    {
        // Logic:
        // IF modal < estimated_ad_cost -> Low Feasibility
        // IF time < 10 (hours/week) AND type = service -> High Risk

        $status = "Realistic";
        $color = "emerald";
        $message = "Rencana bisnis terlihat solid dengan sumber daya yang ada.";

        if ($modal < $estimatedAdCost) {
            $status = "Challenging";
            $color = "amber";
            $message = "Modal iklan mungkin kurang. Pertimbangkan strategi organik.";
        }

        if ($timeAvailable < 10 && $type === 'service') {
            $status = "High Risk";
            $color = "rose";
            $message = "Bisnis jasa butuh waktu eksekusi tinggi. Risiko burnout.";
        }
        
        // Double Whammy
        if ($modal < $estimatedAdCost && $timeAvailable < 5) {
             $status = "Very High Risk";
             $color = "rose";
             $message = "Modal dan waktu sangat terbatas. Saran: Mulai dari freelance/dropship.";
        }

        return [
            'status' => $status,
            'color' => $color,
            'message' => $message
        ];
    }
}
