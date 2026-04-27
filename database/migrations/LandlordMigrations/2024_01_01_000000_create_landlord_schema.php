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
        Schema::createTable('masters', function (Blueprint $table) {
            $table->string('db_name')->unique();
            $table->string('name')->unique();
            $table->uuid('created_by_user_uuid')->nullable()->index();
        });

        Schema::createTable('tenants', function (Blueprint $table) {
            $table->string('db_name')->unique();
            $table->string('name')->unique();
            $table->foreignId('master_id')->constrained('masters')->cascadeOnDelete();
            $table->uuid('created_by_user_uuid')->nullable()->index();
        });

        Schema::createTable('domains', function (Blueprint $table) {
            $table->string('domain')->unique();
            $table->string('tenant_type');
            $table->boolean('is_primary')->default(false);

            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'tenant_type']);
        });

        Schema::createTable('error_logs', function (Blueprint $table) {
            $table->string('reference_id')->unique();
            $table->string('exception_class');
            $table->text('message');
            $table->longText('stack_trace');
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('context')->nullable();
            $table->json('resolved_data')->nullable();
            $table->timestampTz('resolved_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('error_logs');
        Schema::dropIfExists('domains');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('masters');
    }
};
