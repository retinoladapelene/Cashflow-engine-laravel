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
        // 1. Users Table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('username')->unique()->nullable();
            $table->string('password')->nullable(); // Nullable for Google Auth
            $table->string('whatsapp')->nullable();
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->string('firebase_uid')->nullable()->index(); // For migration/hybrid support
            $table->string('auth_provider')->default('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // 3. Ad Arsenal (Promotional Cards)
        Schema::create('ad_arsenals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('tag', ['HOT', 'NEW', 'FOUNDATION', 'PREMIUM'])->default('NEW');
            $table->string('link');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Activity Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // LOGIN, REGISTER, UPDATE_ARSENAL
            $table->text('details')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('ad_arsenals');
        Schema::dropIfExists('users');
    }
};
