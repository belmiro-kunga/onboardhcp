<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\User\Models\User;

class ListUsers extends Command
{
    protected $signature = 'users:list';
    protected $description = 'List all users in the system';

    public function handle()
    {
        $users = User::all(['name', 'email', 'is_admin']);
        
        $this->info('Usuários no sistema:');
        $this->info('==================');
        
        foreach ($users as $user) {
            $role = $user->is_admin ? 'Admin' : 'Funcionário';
            $this->line("{$user->name} - {$user->email} - {$role}");
        }
        
        $this->info('==================');
        $this->info("Total: {$users->count()} usuários");
    }
}