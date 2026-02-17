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
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('business_name')->default('My Business');
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('variable_costs', 15, 2)->default(0);
            $table->decimal('fixed_costs', 15, 2)->default(0);
            $table->integer('traffic')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->decimal('ad_spend', 15, 2)->default(0);
            $table->decimal('target_revenue', 15, 2)->default(0);
            $table->decimal('available_cash', 15, 2)->default(0);
            $table->integer('max_capacity')->default(1000);
            $table->string('currency')->default('IDR');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
    }
};
