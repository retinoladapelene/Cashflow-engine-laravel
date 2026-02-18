<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('profit_simulations');
        Schema::create('profit_simulations', function (Blueprint $table) {
            $table->id();
            // Context
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('business_profile_id'); 
            $table->unsignedBigInteger('reverse_goal_session_id')->nullable();
            
            // Baseline Snapshot
            $table->decimal('baseline_revenue', 15, 2);
            $table->decimal('baseline_net_profit', 15, 2);
            $table->decimal('baseline_margin', 5, 2);
            $table->decimal('baseline_break_even_units', 10, 2);

            // Simulation Input
            $table->string('leverage_zone'); // FK logically to presets, but string is fine for simplicity
            $table->decimal('adjustment_percentage', 5, 2); // e.g. 10.00 for +10%

            // Projected Output
            $table->decimal('projected_revenue', 15, 2);
            $table->decimal('projected_net_profit', 15, 2);
            $table->decimal('projected_margin', 5, 2);
            $table->decimal('projected_break_even_units', 10, 2);

            // Analysis
            $table->decimal('profit_delta', 15, 2);
            $table->integer('effort_score');
            $table->decimal('risk_multiplier', 3, 2);
            $table->decimal('leverage_impact_index', 8, 2); // LII Score
            
            // Intelligence Data
            $table->string('primary_constraint')->nullable(); // capital, time, margin
            $table->integer('constraint_severity')->default(0); 
            $table->string('mentor_focus_area')->nullable(); // structured hint for mentor lab
            $table->json('risk_flags')->nullable(); // ["margin_thin", "cashflow_risk"]

            // Meta & Learning
            $table->boolean('result_validation_flag')->default(false); // Validated by user action later?
            $table->string('logic_version')->default('v2.0');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['created_at']);
            $table->index(['user_id', 'business_profile_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profit_simulations');
    }
};
