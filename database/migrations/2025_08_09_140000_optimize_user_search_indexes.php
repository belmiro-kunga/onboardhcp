<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            return count($result) > 0;
        } catch (\Exception $e) {
            // Table doesn't exist
            return false;
        }
    }

    /**
     * Check if a table exists
     */
    private function tableExists(string $table): bool
    {
        try {
            $result = DB::select("SHOW TABLES LIKE ?", [$table]);
            return count($result) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Check if index doesn't exist before creating
            if (!$this->indexExists('users', 'idx_users_status_admin')) {
                // Composite index for common search patterns
                $table->index(['status', 'is_admin'], 'idx_users_status_admin');
            }
            
            // Index for department filtering
            if (!$this->indexExists('users', 'idx_users_department')) {
                $table->index('department', 'idx_users_department');
            }
            
            // Index for position filtering
            if (!$this->indexExists('users', 'idx_users_position')) {
                $table->index('position', 'idx_users_position');
            }
            
            // Index for hire date filtering and sorting
            if (!$this->indexExists('users', 'idx_users_hire_date')) {
                $table->index('hire_date', 'idx_users_hire_date');
            }
            
            // Index for last login activity filtering
            if (!$this->indexExists('users', 'idx_users_last_login')) {
                $table->index('last_login_at', 'idx_users_last_login');
            }
            
            // Composite index for common sorting patterns
            if (!$this->indexExists('users', 'idx_users_created_name')) {
                $table->index(['created_at', 'name'], 'idx_users_created_name');
            }
            
            // Index for phone search
            if (!$this->indexExists('users', 'idx_users_phone')) {
                $table->index('phone', 'idx_users_phone');
             }
         });

        // Add indexes to audit_logs for performance
        if ($this->tableExists('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
            // Composite index for user activity queries
            if (!$this->indexExists('audit_logs', 'idx_audit_user_date')) {
                $table->index(['user_id', 'performed_at'], 'idx_audit_user_date');
            }
            
            // Index for action filtering
            if (!$this->indexExists('audit_logs', 'idx_audit_action')) {
                $table->index('action', 'idx_audit_action');
            }
            
            // Index for performed_by queries
            if (!$this->indexExists('audit_logs', 'idx_audit_performed_by')) {
                $table->index('performed_by', 'idx_audit_performed_by');
            }
         });
        }

        // Add indexes to user_roles pivot table
        if ($this->tableExists('user_roles')) {
            Schema::table('user_roles', function (Blueprint $table) {
            // Index for role-based queries
            if (!$this->indexExists('user_roles', 'idx_user_roles_role')) {
                $table->index('role_id', 'idx_user_roles_role');
            }
            
            // Index for assignment tracking
            if (!$this->indexExists('user_roles', 'idx_user_roles_assigned')) {
                $table->index('assigned_at', 'idx_user_roles_assigned');
            }
            
            // Index for assigned_by queries
            if (!$this->indexExists('user_roles', 'idx_user_roles_assigned_by')) {
                $table->index('assigned_by', 'idx_user_roles_assigned_by');
            }
         });
        }

        // Add indexes to group_user pivot table
        if ($this->tableExists('group_user')) {
            Schema::table('group_user', function (Blueprint $table) {
                // Index for group-based queries
                if (!$this->indexExists('group_user', 'idx_group_user_group')) {
                    $table->index('group_id', 'idx_group_user_group');
                }
                
                // Index for user queries
                if (!$this->indexExists('group_user', 'idx_group_user_user')) {
                    $table->index('user_id', 'idx_group_user_user');
                }
            });
        }

        // Add indexes to login_attempts for security queries
        if ($this->tableExists('login_attempts')) {
            Schema::table('login_attempts', function (Blueprint $table) {
            // Composite index for failed login tracking
            if (!$this->indexExists('login_attempts', 'idx_login_attempts_email_date')) {
                $table->index(['email', 'attempted_at'], 'idx_login_attempts_email_date');
            }
            
            // Index for IP-based tracking
            if (!$this->indexExists('login_attempts', 'idx_login_attempts_ip_date')) {
                $table->index(['ip_address', 'attempted_at'], 'idx_login_attempts_ip_date');
            }
            
            // Index for success/failure filtering
            if (!$this->indexExists('login_attempts', 'idx_login_attempts_success')) {
                 $table->index('success', 'idx_login_attempts_success');
             }
         });
        }
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