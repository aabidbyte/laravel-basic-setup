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
        if (! Schema::connection('central')->hasTable('subscriptions')) {
            return;
        }

        Schema::connection('central')->table('subscriptions', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::connection('central')->hasTable('subscriptions')) {
            return;
        }

        Schema::connection('central')->table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });
    }
};
