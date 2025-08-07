<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Modules\User\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@teste.com',
            'password' => Hash::make('123456'),
            'email_verified_at' => now(),
            'birth_date' => '1985-03-15',
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'Funcionário Teste',
            'email' => 'funcionario@teste.com',
            'password' => Hash::make('123456'),
            'email_verified_at' => now(),
            'birth_date' => '1990-07-22',
        ]);

        // Aniversariante do dia (usando a data atual)
        User::create([
            'name' => 'Maria Silva',
            'email' => 'maria@hemeracapital.com',
            'password' => Hash::make('123456'),
            'email_verified_at' => now(),
            'birth_date' => now()->format('Y') . '-' . now()->format('m-d'), // Aniversário hoje
        ]);

        // Próximos aniversariantes
        User::create([
            'name' => 'João Santos',
            'email' => 'joao@hemeracapital.com',
            'password' => Hash::make('123456'),
            'email_verified_at' => now(),
            'birth_date' => now()->addDays(2)->format('Y') . '-' . now()->addDays(2)->format('m-d'),
        ]);

        User::create([
            'name' => 'Ana Costa',
            'email' => 'ana@hemeracapital.com',
            'password' => Hash::make('123456'),
            'email_verified_at' => now(),
            'birth_date' => now()->addDays(5)->format('Y') . '-' . now()->addDays(5)->format('m-d'),
        ]);

        User::create([
            'name' => 'Carlos Oliveira',
            'email' => 'carlos@hemeracapital.com',
            'password' => Hash::make('123456'),
            'email_verified_at' => now(),
            'birth_date' => now()->addDays(8)->format('Y') . '-' . now()->addDays(8)->format('m-d'),
        ]);
    }
}