<?php

use App\Models\PublicReview;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_review_reports', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignIdFor(PublicReview::class)
                ->constrained()
                ->cascadeOnDelete();

            $table
                ->foreignIdFor(User::class, 'reporter_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('reason')->nullable();
            $table->string('status')->default('open');

            $table->timestamps();

            $table->unique([
                'public_review_id',
                'reporter_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_review_reports');
    }
};