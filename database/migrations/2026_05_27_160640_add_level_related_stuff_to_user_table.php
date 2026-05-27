<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (
            Blueprint $table
        ) {

            $table->unsignedBigInteger('xp')
                ->default(0)
                ->after('banner_url');

            $table->unsignedInteger('level')
                ->default(1)
                ->after('xp');

            $table->boolean(
                'profile_level_multiplier_enabled'
            )
                ->default(false)
                ->after('level');

            $table->decimal(
                'xp_multiplier',
                5,
                2
            )
                ->default(1.00)
                ->after(
                    'profile_level_multiplier_enabled'
                );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (
            Blueprint $table
        ) {

            $table->dropColumn([
                'xp',
                'level',
                'profile_level_multiplier_enabled',
                'xp_multiplier',
            ]);
        });
    }
};