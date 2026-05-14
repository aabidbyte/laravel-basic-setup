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
        Schema::table('email_translations', function (Blueprint $table) {
            $table->string('subject')->nullable()->change();
            $table->longText('html_content')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_translations', function (Blueprint $table) {
            $table->string('subject')->nullable(false)->change();
            $table->longText('html_content')->nullable(false)->change();
        });
    }
};
