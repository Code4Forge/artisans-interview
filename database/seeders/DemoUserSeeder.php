<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $users = [
            ['name' => 'School Admin', 'email' => 'admin@school.test', 'role' => 'admin'],
            ['name' => 'Demo Teacher', 'email' => 'teacher@school.test', 'role' => 'teacher'],
            ['name' => 'Demo Student', 'email' => 'student@school.test', 'role' => 'student'],
            ['name' => 'Demo Parent', 'email' => 'parent@school.test', 'role' => 'parent'],
        ];

        foreach ($users as $data) {
            $user = User::query()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $password,
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
