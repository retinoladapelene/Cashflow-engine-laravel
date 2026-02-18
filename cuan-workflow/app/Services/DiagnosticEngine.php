<?php

namespace App\Services;

use App\DTO\BusinessInputDTO;

class DiagnosticEngine
{
    public static function analyze(array $baseline, BusinessInputDTO $input): array
    {
        $primaryIssue = "";
        $score = 100;

        // 1. Conversion Issue
        if ($input->conversion < 1) { // < 1%
            $primaryIssue = "Konversi Rendah (< 1%). Validasi penawaran atau landing page Anda.";
            $score -= 30;
        }
        // 2. Margin Issue
        else if ($baseline['margin'] < 0.3) { // < 30%
            $primaryIssue = "Margin Tipis (< 30%). Cek struktur biaya atau naikkan harga.";
            $score -= 20;
        }
        // 3. Traffic Issue
        else {
            // Check if profitable but low volume? OR just general scalability
            $primaryIssue = "Skalabilitas Traffic. Fokus pada marketing channel baru.";
            $score -= 10;
        }

        return [
            'primary_issue' => $primaryIssue,
            'health_score' => $score
        ];
    }
}
