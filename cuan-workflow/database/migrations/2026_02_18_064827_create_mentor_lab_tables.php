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
        Schema::create('mentor_lab_tables', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('mentor_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('mode'); // 'optimizer' or 'planner'
            $table->json('input_json');
            $table->json('baseline_json')->nullable();
            $table->timestamps();
        });

        Schema::create('simulation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('mentor_sessions')->onDelete('cascade');
            $table->json('scenario_json')->nullable();
            $table->json('sensitivity_json')->nullable();
            $table->json('break_even_json')->nullable();
            $table->json('upsell_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulation_results');
        Schema::dropIfExists('mentor_sessions');
        Schema::dropIfExists('mentor_lab_tables');
    }
};
