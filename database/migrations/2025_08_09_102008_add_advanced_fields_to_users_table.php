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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('department', 100)->nullable()->after('phone');
            $table->string('position', 100)->nullable()->after('department');
            $table->date('hire_date')->nullable()->after('position');
            $table->enum('status', ['active', 'inactive', 'pending', 'blocked', 'suspended'])
                  ->default('active')->after('hire_date');
            $table->string('avatar')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'department', 
                'position',
                'hire_date',
                'status',
                'avatar'
            ]);
        });
    }
};
