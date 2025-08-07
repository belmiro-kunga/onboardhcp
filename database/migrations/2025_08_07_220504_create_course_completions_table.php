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
        Schema::create('course_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_seconds_watched')->default(0);
            $table->decimal('final_score', 5, 2)->nullable();
            $table->json('assessment_results')->nullable();
            $table->text('certificate_url')->nullable();
            $table->timestamps();
            
            // Add composite unique index
            $table->unique(['user_id', 'course_id']);
            
            // Add index for faster lookups
            $table->index(['user_id', 'completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_completions');
    }
};
