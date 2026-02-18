<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class ProfitSimulatorTest extends TestCase
{
    /**
     * Test Profit Simulator with Manual Baseline
     */
    public function test_profit_simulation_with_manual_baseline()
    {
        // $user = User::factory()->create();

        $payload = [
            'zone' => 'traffic',
            'adjustment_percentage' => 10, // +10% traffic
            'manual_baseline' => [
                'price' => 100000,
                'traffic' => 1000,
                'conversion_rate' => 2.0, // 20 sales
                'cogs' => 50000,
                'fixed_cost' => 500000,
                'ad_spend' => 200000
            ]
        ];

        // Baseline Calculation:
        // Revenue = 1000 * 2% * 100,000 = 20 * 100,000 = 2,000,000
        // Var Cost = 20 * 50,000 = 1,000,000
        // Fixed = 500,000
        // Ad = 200,000
        // Net = 2,000,000 - 1,000,000 - 500,000 - 200,000 = 300,000
        
        // Simulation (+10% Traffic):
        // Traffic = 1100
        // Sales = 22
        // Revenue = 2,200,000
        // Var Cost = 1,100,000
        // Ad Spend = 220,000 (Assuming linear scale in service)
        // Fixed = 500,000
        // Net = 2,200,000 - 1,100,000 - 500,000 - 220,000 = 380,000
        // Delta = +80,000

        // $response = $this->actingAs($user)
        //                 ->postJson('/profit-simulator/simulate', $payload);
        $response = $this->postJson('/profit-simulator/simulate', $payload);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'baseline',
                     'result' => [
                         'projected',
                         'delta'
                     ],
                     'analysis' => [
                         'primary_constraint',
                         'recommendation'
                     ]
                 ]);
        
        $data = $response->json();
        
        // Check Delta (Approx)
        $this->assertTrue($data['result']['projected']['net_profit'] > 300000);
        $this->assertEquals('traffic', $data['result']['zone']);
    }
}
