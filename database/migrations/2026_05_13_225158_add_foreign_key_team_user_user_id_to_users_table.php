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
        if (! Schema::connection('central')->hasTable('team_user')) {
            return;
        }

        Schema::connection('central')->table('team_user', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::connection('central')->hasTable('team_user')) {
            return;
        }

        Schema::connection('central')->table('team_user', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
