<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // User Groups Table
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Group User Pivot Table
        Schema::create('group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('user_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['group_id', 'user_id']);
        });

        // Permissions Table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('model_type')->nullable();
            $table->timestamps();
        });

        // Permission Group Pivot Table
        Schema::create('group_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('user_groups')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['group_id', 'permission_id']);
        });

        // Course Access Levels Table
        Schema::create('course_access_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('level')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Course Access Rules Table
        Schema::create('course_access_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('access_level_id')->constrained('course_access_levels');
            $table->boolean('is_restricted')->default(false);
            $table->json('restricted_to')->nullable(); // ['group_ids' => [], 'user_ids' => []]
            $table->timestamps();
            
            $table->unique(['course_id', 'access_level_id']);
        });

        // User Skill Levels Table
        Schema::create('user_skill_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('skill_name');
            $table->foreignId('access_level_id')->constrained('course_access_levels');
            $table->timestamps();
            
            $table->unique(['user_id', 'skill_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_skill_levels');
        Schema::dropIfExists('course_access_rules');
        Schema::dropIfExists('course_access_levels');
        Schema::dropIfExists('group_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('group_user');
        Schema::dropIfExists('user_groups');
    }
};
