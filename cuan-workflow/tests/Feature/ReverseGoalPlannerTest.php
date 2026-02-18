<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ReverseGoalPlannerTest extends TestCase
{
    // usage of RefreshDatabase might wipe DB, let's avoid it if we want to keep my manual seeding attempt?
    // Actually, testing environment usually uses sqlite or separate DB.
    // I'll skip RefreshDatabase for now to avoid messing with local dev DB if they share valid config.
    // usage of DatabaseTransactions is safer.
    
    // use DatabaseTransactions; 

    /**
     * Test basic calculation endpoint.
     */
    public function test_calculate_returns_successful_response()
    {
        $this->withoutExceptionHandling();
        $response = $this->postJson('/reverse-planner/calculate', [
            'target_profit' => 10000000, // 10jt
            'timeline_days' => 30,
            'capital_available' => 5000000, // 5jt
            'hours_per_day' => 4,
            'business_model' => 'dropship',
            'traffic_strategy' => 'ads',
            'selling_price' => 150000
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'input',
                         'output',
                         'scores',
                         'logic_version'
                     ]
                 ]);
                 
        // Check scores exist
        $response->assertJsonPath('data.scores.risk_level', 'Challenging'); // Calculated OFS ~68, Threshold 75
        // 5jt capital for 10jt profit in 30 days dropship (margin 20%) -> 
        // Need 50jt Revenue. Ad spend (at 1.5% conv, 1500 cpc) -> massive.
        // so risk should be high.
    }

    public function test_validation_failure()
    {
        $response = $this->postJson('/reverse-planner/calculate', [
            'target_profit' => -100, // Invalid
        ]);

        $response->assertStatus(422);
    }
}
