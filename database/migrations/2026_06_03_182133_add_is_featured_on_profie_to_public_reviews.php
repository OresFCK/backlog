<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_reviews', function (Blueprint $table) {
            $table
                ->boolean('is_featured_on_profile')
                ->default(false)
                ->after('not_recommended');
        });
    }

    public function down(): void
    {
        Schema::table('public_reviews', function (Blueprint $table) {
            $table->dropColumn('is_featured_on_profile');
        });
    }
};