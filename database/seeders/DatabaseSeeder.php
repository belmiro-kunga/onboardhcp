<?php

namespace Database\Seeders;

use App\Modules\User\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class, // Add this first to ensure permissions exist before users
            UserSeeder::class,
            CategorySeeder::class,
            VideoTrainingSeeder::class,
        ]);
    }
}
