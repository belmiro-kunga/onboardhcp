<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Modules\User\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar administrador principal
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@hemeracapital.com',
            'birth_date' => '1985-01-15',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Criar funcionários de exemplo
        $funcionarios = [
            [
                'name' => 'João Silva',
                'email' => 'joao.silva@hemeracapital.com',
                'birth_date' => '1990-03-22',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@hemeracapital.com',
                'birth_date' => '1988-07-10',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ],
            [
                'name' => 'Pedro Costa',
                'email' => 'pedro.costa@hemeracapital.com',
                'birth_date' => '1992-11-05',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ],
            [
                'name' => 'Ana Ferreira',
                'email' => 'ana.ferreira@hemeracapital.com',
                'birth_date' => '1987-09-18',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ],
            [
                'name' => 'Carlos Oliveira',
                'email' => 'carlos.oliveira@hemeracapital.com',
                'birth_date' => '1991-12-03',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ],
            [
                'name' => 'Sofia Rodrigues',
                'email' => 'sofia.rodrigues@hemeracapital.com',
                'birth_date' => '1989-04-25',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ],
        ];

        foreach ($funcionarios as $funcionario) {
            $funcionario['email_verified_at'] = now();
            User::create($funcionario);
        }

        // Criar um segundo administrador
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@hemeracapital.com',
            'birth_date' => '1980-06-12',
            'password' => Hash::make('superadmin123'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }
}