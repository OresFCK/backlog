<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_reviews', function (Blueprint $table) {
            $table->unsignedInteger('time_to_beat_minutes')->nullable()->after('screenshot_path');
        });
    }

    public function down(): void
    {
        Schema::table('public_reviews', function (Blueprint $table) {
            $table->dropColumn('time_to_beat_minutes');
        });
    }
};