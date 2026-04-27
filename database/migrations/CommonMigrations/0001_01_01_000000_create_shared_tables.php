<?php

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
        // Teams table
        Schema::createTable('teams', function (Blueprint $table) {
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
        });

        // Users table (no team_id - users belong to teams via pivot)
        Schema::createTable('users', function (Blueprint $table) {
            $table->string('name');
            $table->string('username')->nullable()->unique();
            $table->string('email')->nullable()->unique(); // Nullable for users without email
            $table->string('pending_email')->nullable(); // New email awaiting verification
            $table->string('pending_email_token')->nullable(); // Token for email change verification
            $table->timestampTz('pending_email_expires_at')->nullable(); // Expiry for pending email (7 days)
            $table->timestampTz('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(false);
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
        });

        // Team user pivot table (many-to-many: users <-> teams)
        Schema::createPivotTable('team_user', function (Blueprint $table) {
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->unique(['team_id', 'user_id']);
        });

        // Password reset tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('identifier')->primary(); // Can be email or username
            $table->uuid('uuid')->unique()->index();
            $table->string('token');
            $table->timestampTz('created_at')->nullable();
        });

        // Personal access tokens
        Schema::createPivotTable('personal_access_tokens', function (Blueprint $table) {
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestampTz('last_used_at')->nullable();
            $table->timestampTz('expires_at')->nullable()->index();
        });

        // Notifications table
        Schema::createTable('notifications', function (Blueprint $table) {
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestampTz('read_at')->nullable();
        });

        // Mail settings table (polymorphic for User, Team, or App-level)
        Schema::createTable('mail_settings', function (Blueprint $table) {
            // Polymorphic relationship (User, Team, or 'app' for global)
            $table->string('settable_type'); // 'App\Models\User', 'App\Models\Team', or 'app'
            $table->unsignedBigInteger('settable_id')->nullable(); // null for app-level

            // Mail provider configuration
            $table->string('provider')->default('smtp'); // smtp, ses, postmark, resend, etc.

            // SMTP settings
            $table->string('host')->nullable();
            $table->integer('port')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // Encrypted via model cast
            $table->string('encryption')->nullable(); // tls, ssl, null

            // From settings
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            // Indexes for common lookups
            $table->index(['settable_type', 'settable_id']);
            $table->index('is_active');
        });

        // Sessions table (for session management - view/revoke active sessions)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // ============================================
        // CUSTOM RBAC TABLES (replaces Spatie Permission)
        // ============================================

        // Roles table
        Schema::createTable('roles', function (Blueprint $table) {
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
        });

        // Permissions table
        Schema::createTable('permissions', function (Blueprint $table) {
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
        });

        // Role-user pivot table (many-to-many: users <-> roles)
        Schema::createPivotTable('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->unique(['role_id', 'user_id']);
        });

        // Permission-role pivot table (many-to-many: roles <-> permissions)
        Schema::createPivotTable('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();

            $table->unique(['permission_id', 'role_id']);
        });

        // Permission-user pivot table (many-to-many: users <-> permissions for direct permissions)
        Schema::createPivotTable('permission_user', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->unique(['permission_id', 'user_id']);
        });

        // ============================================
        // EMAIL TEMPLATE TABLES
        // ============================================

        // Unified email templates table (layouts + contents)
        Schema::createTable('email_templates', function (Blueprint $table) {
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_layout')->default(false);
            $table->string('type')->default('transactional');
            $table->json('entity_types')->nullable();
            $table->json('context_variables')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_system')->default(false);
            $table->boolean('is_default')->default(false);
            $table->boolean('all_teams')->default(true);
            $table->string('preview')->nullable(); // Future: image preview path

            $table->foreignId('layout_id')->nullable()->constrained('email_templates')->nullOnDelete();

            $table->index(['is_layout', 'status']);
            $table->index('type');
            $table->index('is_system');
            $table->index('is_default');
            $table->index('all_teams');
        });

        // Email translations table (polymorphic for templates)
        Schema::createTable('email_translations', function (Blueprint $table) {
            $table->morphs('translatable'); // translatable_type, translatable_id
            $table->string('locale', 10);
            $table->string('subject')->nullable(); // Nullable for layouts
            $table->longText('html_content')->nullable();
            $table->longText('text_content')->nullable();
            $table->string('preheader')->nullable();

            // Draft columns (for draft/publish workflow)
            $table->string('draft_subject')->nullable();
            $table->longText('draft_html_content')->nullable();
            $table->longText('draft_text_content')->nullable();
            $table->string('draft_preheader')->nullable();

            $table->unique(['translatable_type', 'translatable_id', 'locale'], 'translatable_locale_unique');
            $table->index('locale');
        });

        // Email template team pivot table
        Schema::createPivotTable('email_template_team', function (Blueprint $table) {
            $table->foreignId('email_template_id')->constrained('email_templates')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();

            $table->unique(['email_template_id', 'team_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop RBAC tables
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');

        // Drop email template tables
        Schema::dropIfExists('email_template_team');
        Schema::dropIfExists('email_translations');
        Schema::dropIfExists('email_templates');

        // Drop other tables
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('mail_settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('users');
        Schema::dropIfExists('teams');
    }
};
