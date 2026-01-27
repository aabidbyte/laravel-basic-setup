<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Users table (no team_id - users belong to teams via pivot)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('name');
            $table->string('username')->nullable()->unique();
            $table->string('email')->nullable()->unique(); // Nullable for users without email
            $table->string('pending_email')->nullable(); // New email awaiting verification
            $table->string('pending_email_token')->nullable(); // Token for email change verification
            $table->timestamp('pending_email_expires_at')->nullable(); // Expiry for pending email (7 days)
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->foreignId('created_by_user_id')->nullable(); // Track who created user (FK added after table exists)
            $table->boolean('is_active')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->json('frontend_preferences')->nullable(); // Stores timezone, locale, and other preferences
            $table->json('notification_preferences')->nullable(); // Email, browser, and per-type notification settings
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('username');
            $table->index('is_active');
            $table->index('created_by_user_id');
        });

        // Add self-referencing foreign key for created_by_user_id after table exists
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('created_by_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // Team user pivot table (many-to-many: users <-> teams)
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
        });

        // Password reset tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('identifier')->primary(); // Can be email or username
            $table->uuid('uuid')->unique()->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Personal access tokens
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        // Notifications table
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

        // Mail settings table (polymorphic for User, Team, or App-level)
        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();

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

            $table->timestamps();
            $table->softDeletes();

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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Role-user pivot table (many-to-many: users <-> roles)
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->unique(['role_id', 'user_id']);
        });

        // Permission-role pivot table (many-to-many: roles <-> permissions)
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();

            $table->unique(['permission_id', 'role_id']);
        });

        // Permission-user pivot table (many-to-many: users <-> permissions for direct permissions)
        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->unique(['permission_id', 'user_id']);
        });

        // ============================================
        // TELESCOPE TABLES
        // ============================================

        // Telescope tables
        $schema = Schema::connection($this->getTelescopeConnection());

        $schema->create('telescope_entries', function (Blueprint $table) {
            $table->bigIncrements('sequence');
            $table->uuid('uuid');
            $table->uuid('batch_id');
            $table->string('family_hash')->nullable();
            $table->boolean('should_display_on_index')->default(true);
            $table->string('type', 20);
            $table->longText('content');
            $table->dateTime('created_at')->nullable();

            $table->unique('uuid');
            $table->index('batch_id');
            $table->index('family_hash');
            $table->index('created_at');
            $table->index(['type', 'should_display_on_index']);
        });

        $schema->create('telescope_entries_tags', function (Blueprint $table) {
            $table->uuid('entry_uuid');
            $table->string('tag');

            $table->primary(['entry_uuid', 'tag']);
            $table->index('tag');

            $table->foreign('entry_uuid')
                ->references('uuid')
                ->on('telescope_entries')
                ->onDelete('cascade');
        });

        $schema->create('telescope_monitoring', function (Blueprint $table) {
            $table->string('tag')->primary();
            $table->uuid('uuid')->unique()->index();
        });

        // ============================================
        // ERROR LOGS TABLE (for ticketing system)
        // ============================================

        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('reference_id')->unique()->index(); // ERR-20260108-ABC123
            $table->string('exception_class');
            $table->text('message');
            $table->longText('stack_trace');
            $table->string('url', 2048)->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, etc.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('context')->nullable(); // Request data, headers, etc.
            $table->json('resolved_data')->nullable(); // For future ticketing (assignee, notes)
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('exception_class');
            $table->index('created_at');
        });

        // ============================================
        // EMAIL TEMPLATE TABLES
        // ============================================

        // Unified email templates table (layouts + contents)
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_layout')->default(false);
            $table->unsignedBigInteger('layout_id')->nullable(); // Self-ref FK added after
            $table->string('type')->default('transactional');
            $table->json('entity_types')->nullable();
            $table->json('context_variables')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_system')->default(false);
            $table->boolean('is_default')->default(false);
            $table->boolean('all_teams')->default(true);
            $table->string('preview')->nullable(); // Future: image preview path
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_layout', 'status']);
            $table->index('type');
            $table->index('is_system');
            $table->index('is_default');
            $table->index('all_teams');
        });

        // Self-referential FK for layout_id (contents reference their layout)
        Schema::table('email_templates', function (Blueprint $table) {
            $table->foreign('layout_id')
                ->references('id')
                ->on('email_templates')
                ->nullOnDelete();
        });

        // Email translations table (polymorphic for templates)
        Schema::create('email_translations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
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
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['translatable_type', 'translatable_id', 'locale'], 'translatable_locale_unique');
            $table->index('locale');
        });

        // Email template team pivot table
        Schema::create('email_template_team', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('email_template_id')->constrained('email_templates')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['email_template_id', 'team_id']);
        });

        // ============================================
        // DATABASE TRIGGERS (MySQL only)
        // ============================================

        // Create database triggers to protect user ID 1 (MySQL only)
        if (DB::getDriverName() === 'mysql') {
            // Trigger to prevent deletion of user ID 1
            DB::unprepared('
                CREATE TRIGGER prevent_user_id_1_delete
                BEFORE DELETE ON users
                FOR EACH ROW
                BEGIN
                    IF OLD.id = 1 THEN
                        SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Cannot delete user ID 1 - this user is protected";
                    END IF;
                END
            ');

            // Trigger to prevent direct database updates to user ID 1
            DB::unprepared('
                CREATE TRIGGER prevent_user_id_1_update
                BEFORE UPDATE ON users
                FOR EACH ROW
                BEGIN
                    -- Check if trying to update user ID 1
                    IF OLD.id = 1 THEN
                        -- Allow the update only if it\'s coming from the application
                        -- We check for a session variable that Laravel sets
                        IF @laravel_user_id_1_self_edit IS NULL OR @laravel_user_id_1_self_edit != 1 THEN
                            SIGNAL SQLSTATE "45000"
                            SET MESSAGE_TEXT = "Cannot update user ID 1 - only user ID 1 can edit themselves through the application";
                        END IF;
                    END IF;
                END
            ');

            // Trigger to prevent changing the ID of user ID 1
            DB::unprepared('
                CREATE TRIGGER prevent_user_id_1_id_change
                BEFORE UPDATE ON users
                FOR EACH ROW
                BEGIN
                    -- Prevent changing the ID of user ID 1
                    IF OLD.id = 1 AND NEW.id != 1 THEN
                        SIGNAL SQLSTATE "45000"
                        SET MESSAGE_TEXT = "Cannot change the ID of user ID 1 - this user is protected";
                    END IF;
                END
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the triggers first
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_user_id_1_delete');
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_user_id_1_update');
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_user_id_1_id_change');
        }

        // Drop Telescope tables
        $schema = Schema::connection($this->getTelescopeConnection());

        $schema->dropIfExists('telescope_entries_tags');
        $schema->dropIfExists('telescope_entries');
        $schema->dropIfExists('telescope_monitoring');

        // Drop RBAC tables
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');

        // Drop error logs table
        Schema::dropIfExists('error_logs');

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
