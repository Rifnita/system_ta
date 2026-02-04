<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Check if role exists
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);

        // Create test user
        $user = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Test',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'alamat' => 'Jl. Test No. 123',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Update email verification if user already exists
        if (!$user->wasRecentlyCreated && !$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        // Assign role
        $user->assignRole($superAdminRole);

        $this->command->info('Test user created successfully!');
        $this->command->info('Email: admin@test.com');
        $this->command->info('Password: password');
    }
}
