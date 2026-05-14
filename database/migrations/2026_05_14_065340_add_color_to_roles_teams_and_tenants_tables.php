<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('color')->default('neutral')->after('description');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->string('color')->default('neutral')->after('description');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('color')->default('neutral')->after('plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('color');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('color');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
