<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BenchmarkAssumptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Dropship: Low margin, low conversion, cheap-ish traffic? No, actually CPC is high.
        // Let's use the user's examples + common sense.
        
        $benchmarks = [
            [
                'business_model' => 'dropship',
                'avg_margin' => 20, // %
                'avg_conversion' => 1.5, // %
                'avg_cpc' => 1500, // IDR
                'traffic_capacity_per_hour' => 100, // Easy to scale
                'difficulty_index' => 1.2, // Harder due to low margin
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'business_model' => 'digital',
                'avg_margin' => 80, // High margin
                'avg_conversion' => 3.0, // Better conversion if funnel is good
                'avg_cpc' => 2000, 
                'traffic_capacity_per_hour' => 1000, // Infinitely scalable
                'difficulty_index' => 1.0, 
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'business_model' => 'service',
                'avg_margin' => 50,
                'avg_conversion' => 7.0, // High conversion (leads) but hard to scale
                'avg_cpc' => 2500,
                'traffic_capacity_per_hour' => 10, // Hard to scale hours
                'difficulty_index' => 1.5, // Operationally hard
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'business_model' => 'stock', // Own Product / Physical
                'avg_margin' => 40,
                'avg_conversion' => 2.0,
                'avg_cpc' => 1800,
                'traffic_capacity_per_hour' => 50, // Logistics constraint
                'difficulty_index' => 1.1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'business_model' => 'affiliate',
                'avg_margin' => 10, // Very low margin (commission)
                'avg_conversion' => 2.0,
                'avg_cpc' => 0, // Usually organic / free traffic assumption or paid
                'traffic_capacity_per_hour' => 200,
                'difficulty_index' => 1.0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('benchmark_assumptions')->insert($benchmarks);
    }
}
