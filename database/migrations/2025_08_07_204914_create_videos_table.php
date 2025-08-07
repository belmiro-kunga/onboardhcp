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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('source_type', ['local', 'youtube', 'r2']);
            $table->string('source_url', 1000);
            $table->integer('duration')->nullable(); // em segundos
            $table->string('thumbnail', 500)->nullable();
            $table->json('metadata')->nullable(); // informações específicas por tipo de fonte
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('processed_at')->nullable(); // quando o vídeo foi processado
            $table->timestamps();
            
            $table->index(['course_id', 'is_active', 'order_index']);
            $table->index('source_type');
            $table->index('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
