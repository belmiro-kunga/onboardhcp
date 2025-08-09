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
            // Composite index for common search patterns
            $table->index(['status', 'is_admin'], 'idx_users_status_admin');
            
            // Index for department filtering
            $table->index('department', 'idx_users_department');
            
            // Index for position filtering
            $table->index('position', 'idx_users_position');
            
            // Index for hire date filtering and sorting
            $table->index('hire_date', 'idx_users_hire_date');
            
            // Index for last login activity filtering
            $table->index('last_login_at', 'idx_users_last_login');
            
            // Composite index for common sorting patterns
            $table->index(['created_at', 'name'], 'idx_users_created_name');
            
            // Index for phone search
            $table->index('phone', 'idx_users_phone');
        });

        // Add indexes to audit_logs for performance
        Schema::table('audit_logs', function (Blueprint $table) {
            // Composite index for user activity queries
            $table->index(['user_id', 'performed_at'], 'idx_audit_user_date');
            
            // Index for action filtering
            $table->index('action', 'idx_audit_action');
            
            // Index for performed_by queries
            $table->index('performed_by', 'idx_audit_performed_by');
        });

        // Add indexes to user_roles pivot table
        Schema::table('user_roles', function (Blueprint $table) {
            // Index for role-based queries
            $table->index('role_id', 'idx_user_roles_role');
            
            // Index for assignment tracking
            $table->index('assigned_at', 'idx_user_roles_assigned');
            
            // Index for assigned_by queries
            $table->index('assigned_by', 'idx_user_roles_assigned_by');
        });

        // Add indexes to user_group_members pivot table
        Schema::table('user_group_members', function (Blueprint $table) {
            // Index for group-based queries
            $table->index('group_id', 'idx_user_groups_group');
            
            // Index for join date queries
            $table->index('joined_at', 'idx_user_groups_joined');
        });

        // Add indexes to login_attempts for security queries
        Schema::table('login_attempts', function (Blueprint $table) {
            // Composite index for failed login tracking
            $table->index(['email', 'attempted_at'], 'idx_login_attempts_email_date');
            
            // Index for IP-based tracking
            $table->index(['ip_address', 'attempted_at'], 'idx_login_attempts_ip_date');
            
            // Index for success/failure filtering
            $table->index('success', 'idx_login_attempts_success');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_status_admin');
            $table->dropIndex('idx_users_department');
            $table->dropIndex('idx_users_position');
            $table->dropIndex('idx_users_hire_date');
            $table->dropIndex('idx_users_last_login');
            $table->dropIndex('idx_users_created_name');
            $table->dropIndex('idx_users_phone');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_user_date');
            $table->dropIndex('idx_audit_action');
            $table->dropIndex('idx_audit_performed_by');
        });

        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropIndex('idx_user_roles_role');
            $table->dropIndex('idx_user_roles_assigned');
            $table->dropIndex('idx_user_roles_assigned_by');
        });

        Schema::table('user_group_members', function (Blueprint $table) {
            $table->dropIndex('idx_user_groups_group');
            $table->dropIndex('idx_user_groups_joined');
        });

        Schema::table('login_attempts', function (Blueprint $table) {
            $table->dropIndex('idx_login_attempts_email_date');
            $table->dropIndex('idx_login_attempts_ip_date');
            $table->dropIndex('idx_login_attempts_success');
        });
    }
};