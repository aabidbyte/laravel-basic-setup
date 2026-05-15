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
        // 1. Mail settings (Tenant-specific)
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

        // 2. Email templates (Tenant-specific)
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
            // Draft columns
            $table->string('draft_subject')->nullable();
            $table->longText('draft_html_content')->nullable();
            $table->longText('draft_text_content')->nullable();
            $table->string('draft_preheader')->nullable();
            $table->unique(['translatable_type', 'translatable_id', 'locale'], 'trans_locale_unique');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // 3. Email Template Team pivot (Tenant-specific)
        Schema::create('email_template_team', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('email_template_id')->constrained('email_templates')->cascadeOnDelete();
            $table->unsignedBigInteger('team_id');
            $table->timestampsTz();
            $table->unique(['email_template_id', 'team_id']);
            $table->index('team_id');
        });

        // 4. Error logs (Tenant-specific)
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
        Schema::dropIfExists('email_template_team');
        Schema::dropIfExists('email_translations');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('mail_settings');
    }
};
