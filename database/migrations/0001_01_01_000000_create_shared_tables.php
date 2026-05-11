<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Get the migration connection name for Telescope.
     */
    public function getTelescopeConnection(): ?string
    {
        return config('telescope.storage.database.connection');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Users table (Central)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('username')->nullable()->unique();
            $table->string('email')->nullable()->unique(); // Nullable for users without email
            $table->string('pending_email')->nullable(); // New email awaiting verification
            $table->string('pending_email_token')->nullable(); // Token for email change verification
            $table->timestampTz('pending_email_expires_at')->nullable(); // Expiry for pending email (7 days)
            $table->timestampTz('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_super_admin')->default(false);
            $table->timestampTz('last_login_at')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestampTz('two_factor_confirmed_at')->nullable();
            $table->json('frontend_preferences')->nullable(); // Stores timezone, locale, and other preferences
            $table->json('notification_preferences')->nullable(); // Email, browser, and per-type notification settings
            $table->rememberToken();

            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->index('username');
            $table->index('is_active');
            $table->index('created_by_user_id');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // Password reset tokens (Central)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('identifier')->primary(); // Can be email or username
            $table->uuid('uuid')->unique()->index();
            $table->string('token');
            $table->timestampTz('created_at')->nullable();
        });

        // Sessions table (Central)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Personal access tokens (Central)
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestampTz('last_used_at')->nullable();
            $table->timestampTz('expires_at')->nullable()->index();
            $table->timestampsTz();
        });

        // Roles table (Central)
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // Permissions table (Central)
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->string('entity')->nullable();
            $table->string('action')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // Role User pivot table (Central)
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        });

        // Permission Role pivot table (Central)
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
        });

        // Permission User pivot table (Central)
        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
