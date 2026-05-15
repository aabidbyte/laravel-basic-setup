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
        // 1. Users table (Central)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('username')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('pending_email')->nullable();
            $table->string('pending_email_token')->nullable();
            $table->timestampTz('pending_email_expires_at')->nullable();
            $table->timestampTz('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_super_admin')->default(false);
            $table->timestampTz('last_login_at')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestampTz('two_factor_confirmed_at')->nullable();
            $table->json('frontend_preferences')->nullable();
            $table->json('notification_preferences')->nullable();
            $table->rememberToken();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->index('username');
            $table->index('is_active');
            $table->index('created_by_user_id');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        // 2. Password reset tokens (Central)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique();
            $table->uuid('uuid')->unique()->index();
            $table->string('token');
            $table->timestampTz('created_at')->nullable();
        });

        // 3. Sessions table (Central)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 4. Personal access tokens (Central)
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

        // 5. Roles table (Central)
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->string('color')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // 6. Permissions table (Central)
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

        // 7. Role User pivot table (Central)
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        });

        // 8. Permission Role pivot table (Central)
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
        });

        // 9. Permission User pivot table (Central)
        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        });

        // 10. Tenants table (Central)
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->unique();
            $table->string('slug')->nullable()->unique();
            $table->string('name')->nullable();
            $table->string('plan')->nullable();
            $table->boolean('should_seed')->default(false);
            $table->string('color')->nullable();
            $table->timestampsTz();
            $table->json('data')->nullable();
        });

        // 11. Domains table (Central)
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 255)->unique();
            $table->string('tenant_id');
            $table->timestampsTz();
            $table->foreign('tenant_id')->references('tenant_id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
        });

        // 12. Tenant User Impersonation Tokens (Central)
        Schema::create('tenant_user_impersonation_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 128)->unique();
            $table->string('tenant_id');
            $table->string('user_id');
            $table->string('auth_guard');
            $table->string('redirect_url');
            $table->timestamp('created_at');
            $table->foreign('tenant_id')->references('tenant_id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
        });

        // 13. Tenant User table (Central)
        Schema::create('tenant_user', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->foreign('tenant_id')->references('tenant_id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'user_id']);
        });

        // 14. Notifications table (Central)
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 15. Telescope tables (Central)
        Schema::create('telescope_entries', function (Blueprint $table) {
            $table->bigIncrements('sequence');
            $table->uuid('uuid')->unique();
            $table->uuid('batch_id')->index();
            $table->string('family_hash')->nullable()->index();
            $table->boolean('should_display_on_index')->default(true);
            $table->string('type', 20);
            $table->longText('content');
            $table->timestamp('created_at')->nullable()->index();
        });

        Schema::create('telescope_entries_tags', function (Blueprint $table) {
            $table->id();
            $table->uuid('entry_uuid')->index();
            $table->string('tag')->index();

            $table->foreign('entry_uuid')->references('uuid')->on('telescope_entries')->onDelete('cascade');
        });

        Schema::create('telescope_monitoring', function (Blueprint $table) {
            $table->id();
            $table->string('tag')->unique();
        });

        // 16. Teams table (Central)
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('role')->nullable()->default('member');
            $table->timestampsTz();
            $table->index('user_id');
            $table->unique(['user_id', 'team_id']);
        });

        // 17. Plans & Subscriptions (Central)
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->json('name');
            $table->string('tier');
            $table->decimal('price', 15, 2);
            $table->string('currency')->default('USD');
            $table->string('billing_cycle');
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('tenant_id');
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('status');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->json('extras')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('tenant_id');
            $table->foreign('tenant_id')->references('tenant_id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
        });

        // 18. Email Templates (Central)
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('type')->default('marketing');
            $table->boolean('is_layout')->default(false);
            $table->foreignId('layout_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_default')->default(false);
            $table->boolean('all_teams')->default(false);
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->json('builder_data')->nullable();
            $table->json('entity_types')->nullable();
            $table->json('context_variables')->nullable();
            $table->text('preview')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create('email_translations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('translatable_type');
            $table->unsignedBigInteger('translatable_id');
            $table->string('locale')->index();
            $table->string('subject');
            $table->longText('html_content');
            $table->longText('text_content')->nullable();
            $table->string('preheader')->nullable();
            $table->string('draft_subject')->nullable();
            $table->longText('draft_html_content')->nullable();
            $table->longText('draft_text_content')->nullable();
            $table->string('draft_preheader')->nullable();
            $table->unique(['translatable_type', 'translatable_id', 'locale'], 'trans_locale_unique');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // 19. Error Logs (Central)
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference_id')->unique()->index();
            $table->string('exception_class')->index();
            $table->text('message');
            $table->longText('stack_trace');
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('context')->nullable();
            $table->json('resolved_data')->nullable();
            $table->timestampTz('resolved_at')->nullable()->index();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('error_logs');
        Schema::dropIfExists('email_translations');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('telescope_monitoring');
        Schema::dropIfExists('telescope_entries_tags');
        Schema::dropIfExists('telescope_entries');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('tenant_user');
        Schema::dropIfExists('tenant_user_impersonation_tokens');
        Schema::dropIfExists('domains');
        Schema::dropIfExists('tenants');
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
