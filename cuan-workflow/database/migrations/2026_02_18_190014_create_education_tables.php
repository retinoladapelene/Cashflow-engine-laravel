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
        // 1. Term Definitions
        Schema::create('term_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('term_key')->unique();
            $table->text('short_text');
            $table->text('long_text');
            $table->text('contextual_template')->nullable();
            $table->json('trigger_rules')->nullable();
            $table->string('context_type')->default('general'); // planner, simulator, mentor
            $table->timestamps();
        });

        // 2. User Term Familiarity
        Schema::create('user_term_familiarity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('term_key');
            $table->integer('click_count')->default(0);
            $table->integer('familiarity_score')->default(0);
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'term_key']);
        });

        // 3. User Behavior Log
        Schema::create('user_behavior_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action_type'); // zone_selected, level_selected, etc
            $table->string('selected_zone')->nullable();
            $table->string('selected_level')->nullable();
            $table->string('goal_status')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_behavior_log');
        Schema::dropIfExists('user_term_familiarity');
        Schema::dropIfExists('term_definitions');
    }
};
