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
        Schema::dropIfExists('reverse_adjustment_options');
        Schema::dropIfExists('reverse_goal_sessions');
        Schema::dropIfExists('benchmark_assumptions');

        // 1. Benchmark Assumptions Table
        Schema::create('benchmark_assumptions', function (Blueprint $table) {
            $table->id();
            $table->string('business_model')->unique(); // dropship, digital, service, etc.
            $table->decimal('avg_margin', 5, 2); // percentage
            $table->decimal('avg_conversion', 5, 2); // percentage
            $table->decimal('avg_cpc', 10, 2);
            $table->integer('traffic_capacity_per_hour')->default(50); // benchmark output
            $table->decimal('difficulty_index', 3, 2)->default(1.0);
            $table->timestamps();
        });

        // 2. Reverse Goal Sessions Table
        Schema::create('reverse_goal_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Versioning & Meta
            $table->string('logic_version')->default('v2.0');
            $table->json('constraint_snapshot')->nullable(); // {capital_gap: ..., execution_gap: ...}
            $table->boolean('is_stable_plan')->default(false);

            // Input Context
            $table->string('business_model');
            $table->string('traffic_strategy'); // organic, ads, hybrid
            $table->decimal('target_profit', 15, 2);
            $table->integer('timeline_days');
            $table->decimal('capital_available', 15, 2);
            $table->integer('hours_per_day');

            // Server-Determined Assumptions (Assumption Lock)
            $table->decimal('assumed_margin', 5, 2);
            $table->decimal('assumed_conversion', 5, 2);
            $table->decimal('assumed_cpc', 10, 2);

            // Calculated Output
            $table->decimal('unit_net_profit', 15, 2);
            $table->integer('required_units');
            $table->integer('required_traffic');
            $table->decimal('required_ad_budget', 15, 2)->default(0);
            $table->decimal('execution_load_ratio', 5, 2);

            // Scores
            $table->decimal('financial_score', 5, 2); // FFS
            $table->decimal('capital_score', 5, 2);   // CAS
            $table->decimal('execution_score', 5, 2); // EFS
            $table->decimal('overall_score', 5, 2);   // OFS
            $table->string('risk_level'); // Realistic, Challenging, High Risk

            // Final Adjusted Target (if user accepted adjustments)
            $table->decimal('final_target_profit', 15, 2)->nullable();
            $table->integer('final_timeline_days')->nullable();

            $table->timestamps();

            // Indexes for Analytics
            $table->index(['created_at']);
        });

        // 3. Adjustment Options Table
        Schema::create('reverse_adjustment_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reverse_goal_session_id')->constrained('reverse_goal_sessions')->onDelete('cascade');
            
            $table->string('option_type'); // extend_timeline, reduce_target, change_model
            $table->json('changes_snapshot'); // {new_timeline: 60, old_timeline: 30}
            $table->decimal('new_overall_score', 5, 2);
            $table->boolean('selected')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reverse_adjustment_options');
        Schema::dropIfExists('reverse_goal_sessions');
        Schema::dropIfExists('benchmark_assumptions');
    }
};
