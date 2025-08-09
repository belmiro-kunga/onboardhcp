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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('activity_type', 50); // login, logout, page_view, action, etc.
            $table->string('activity_description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, PUT, DELETE
            $table->json('metadata')->nullable(); // Additional data like form data, etc.
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['activity_type', 'created_at']);
            $table->index(['created_at']);
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
