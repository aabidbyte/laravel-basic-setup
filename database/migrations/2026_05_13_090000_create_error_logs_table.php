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
    }
};
