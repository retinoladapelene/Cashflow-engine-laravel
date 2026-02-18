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
        // 1. Simulations (Snapshot of Mentor Lab Results)
        Schema::create('simulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mode')->default('optimizer'); // optimizer / planner
            $table->json('input_data'); // {traffic, conversion, price, cost...}
            $table->json('result_data'); // {revenue, profit, margin...}
            $table->json('health_score')->nullable(); // {margin_score, traffic_score...}
            $table->json('generated_tags')->nullable(); // ["Margin Improvement", "Traffic Scaling"]
            $table->timestamps();
        });

        // 2. Roadmaps (The Active Roadmap for a User)
        Schema::create('roadmaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('simulation_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active'); // active, completed, abandoned
            $table->integer('total_steps')->default(0);
            $table->integer('completed_steps')->default(0);
            $table->timestamps();
        });

        // 3. Roadmap Steps (The personalized steps generated)
        Schema::create('roadmap_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roadmap_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order');
            $table->string('status')->default('locked'); // locked, unlocked, completed
            $table->string('strategy_tag')->nullable(); // e.g. "Margin Improvement"
            $table->timestamps();
        });

        // 4. Roadmap Actions (Checklist items within a step)
        Schema::create('roadmap_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('step_id')->constrained('roadmap_steps')->cascadeOnDelete();
            $table->string('action_text');
            $table->boolean('is_completed')->default(false);
            $table->json('tool_recommendation')->nullable(); // [{name: "Canva", link: "..."}]
            $table->timestamps();
        });

        // 5. Strategy Blueprints (Templates for generating steps)
        Schema::create('strategy_blueprints', function (Blueprint $table) {
            $table->id();
            $table->string('strategy_tag'); // e.g. "Margin Improvement"
            $table->string('step_title');
            $table->text('step_description')->nullable();
            $table->json('default_actions'); // ["Audit Costs", "Negotiate Suppliers"]
            $table->integer('priority_level')->default(1); // 1 = Survival, 2 = Efficiency, 3 = Growth
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategy_blueprints');
        Schema::dropIfExists('roadmap_actions');
        Schema::dropIfExists('roadmap_steps');
        Schema::dropIfExists('roadmaps');
        Schema::dropIfExists('simulations');
    }
};
