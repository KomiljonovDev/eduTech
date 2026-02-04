<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $manager = Role::create(['name' => 'manager']);
        $teacher = Role::create(['name' => 'teacher']);
        $receptionist = Role::create(['name' => 'receptionist']);

        $admin = User::create([
            'name' => 'Admin Manager',
            'email' => 'admin@edutech.uz',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $admin->assignRole('manager');
    }
}
