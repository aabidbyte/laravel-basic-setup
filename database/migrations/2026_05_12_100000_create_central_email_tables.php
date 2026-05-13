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
        // Email templates (Central database - Global)
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_translations');
        Schema::dropIfExists('email_templates');
    }
};
