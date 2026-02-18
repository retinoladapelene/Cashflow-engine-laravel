<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeverageZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $presets = [
            [
                'zone_name' => 'traffic',
                'effort_score' => 8,
                'risk_multiplier' => 1.3,
                'success_probability' => 0.6,
                'difficulty_level' => 'Hard',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'zone_name' => 'conversion',
                'effort_score' => 5,
                'risk_multiplier' => 1.0,
                'success_probability' => 0.7,
                'difficulty_level' => 'Medium',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'zone_name' => 'pricing',
                'effort_score' => 3,
                'risk_multiplier' => 1.1,
                'success_probability' => 0.75,
                'difficulty_level' => 'Easy',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'zone_name' => 'cost',
                'effort_score' => 4,
                'risk_multiplier' => 0.9,
                'success_probability' => 0.8,
                'difficulty_level' => 'Medium',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('leverage_zone_presets')->insertOrIgnore($presets);
    }
}
