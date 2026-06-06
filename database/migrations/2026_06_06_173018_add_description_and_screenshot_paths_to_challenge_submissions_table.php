<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_submissions', function (Blueprint $table) {
            $table->text('description')->nullable()->after('screenshot_path');
            $table->json('screenshot_paths')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('challenge_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'screenshot_paths',
            ]);
        });
    }
};