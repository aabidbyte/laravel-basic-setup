<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Users table (Tenant-specific)
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

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique();
            $table->uuid('uuid')->unique()->index();
            $table->string('token');
            $table->timestampTz('created_at')->nullable();
        });

        $this->createSessionTable();
        $this->createQueueTables();

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

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
        });

        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        });

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

        Schema::create('team_permissions', function (Blueprint $table) {
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

        Schema::create('team_roles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create('team_permission_team_role', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('team_permission_id')->constrained('team_permissions')->cascadeOnDelete();
            $table->foreignId('team_role_id')->constrained('team_roles')->cascadeOnDelete();
            $table->unique(['team_permission_id', 'team_role_id'], 'team_permission_role_unique');
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('team_role_id')->nullable()->constrained('team_roles')->nullOnDelete();
            $table->string('role')->nullable()->default('member');
            $table->timestampsTz();
            $table->unique(['user_id', 'team_id']);
        });

        // 2. Mail settings (Tenant-specific)
        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('settable_type');
            $table->unsignedBigInteger('settable_id')->nullable();
            $table->string('provider')->default('smtp');
            $table->string('host')->nullable();
            $table->integer('port')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('encryption')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampTz('last_used_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->index(['settable_type', 'settable_id']);
        });

        // 3. Email templates (Tenant-specific)
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
            $table->string('subject')->nullable();
            $table->longText('html_content')->nullable();
            $table->longText('text_content')->nullable();
            $table->string('preheader')->nullable();
            // Draft columns
            $table->string('draft_subject')->nullable();
            $table->longText('draft_html_content')->nullable();
            $table->longText('draft_text_content')->nullable();
            $table->string('draft_preheader')->nullable();
            $table->unique(['translatable_type', 'translatable_id', 'locale'], 'trans_locale_unique');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // 4. Email Template Team pivot (Tenant-specific)
        Schema::create('email_template_team', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('email_template_id')->constrained('email_templates')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->timestampsTz();
            $table->unique(['email_template_id', 'team_id']);
        });

        // 5. Error logs (Tenant-specific)
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference_id')->unique()->index();
            $table->string('exception_class')->index();
            $table->text('message');
            $table->longText('stack_trace');
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->string('tenant_id')->nullable()->index();
            $table->string('tenant_name')->nullable();
            $table->string('tenant_domain')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_uuid')->nullable();
            $table->string('actor_type')->nullable()->index();
            $table->string('actor_name')->nullable();
            $table->string('actor_email')->nullable();
            $table->unsignedBigInteger('impersonator_id')->nullable()->index();
            $table->string('impersonator_name')->nullable();
            $table->string('impersonator_email')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('runtime_context')->nullable()->index();
            $table->string('command')->nullable();
            $table->string('job_id')->nullable()->index();
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
        Schema::dropIfExists('email_template_team');
        Schema::dropIfExists('email_translations');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('mail_settings');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('team_permission_team_role');
        Schema::dropIfExists('team_roles');
        Schema::dropIfExists('team_permissions');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }

    /**
     * Create the tenant database sessions table.
     */
    private function createSessionTable(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('uuid')->nullable()->unique()->index();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Create queue runtime tables.
     */
    private function createQueueTables(): void
    {
        $this->createJobsTable();
        $this->createJobBatchesTable();
        $this->createFailedJobsTable();
    }

    /**
     * Create the database queue jobs table.
     */
    private function createJobsTable(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    /**
     * Create the queued job batches table.
     */
    private function createJobBatchesTable(): void
    {
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });
    }

    /**
     * Create the failed queue jobs table.
     */
    private function createFailedJobsTable(): void
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }
};
