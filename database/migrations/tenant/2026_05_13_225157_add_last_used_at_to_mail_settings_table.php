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
        Schema::table('mail_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('mail_settings', 'last_used_at')) {
                $table->timestampTz('last_used_at')->nullable()->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_settings', function (Blueprint $table) {
            if (Schema::hasColumn('mail_settings', 'last_used_at')) {
                $table->dropColumn('last_used_at');
            }
        });
    }
};
