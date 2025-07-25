<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role sudah ada
        $roles = [
            'direktur' => 'Direktur perusahaan dengan akses penuh',
            'project_manager' => 'Manager proyek dengan akses manajemen proyek',
            'finance_manager' => 'Manager keuangan dengan akses manajemen keuangan',
            'staf' => 'Staf dengan akses input data dasar'
        ];
        
        foreach ($roles as $name => $description) {
            Role::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }
        
        // Buat user untuk setiap role
        $users = [
            [
                'name' => 'Direktur Mitra',
                'email' => 'direktur@mitra.com',
                'password' => Hash::make('password123'),
                'role' => 'direktur'
            ],
            [
                'name' => 'Project Manager Mitra',
                'email' => 'projectmanager@mitra.com',
                'password' => Hash::make('password123'),
                'role' => 'project_manager'
            ],
            [
                'name' => 'Finance Manager Mitra',
                'email' => 'financemanager@mitra.com',
                'password' => Hash::make('password123'),
                'role' => 'finance_manager'
            ],
            [
                'name' => 'Staf Mitra',
                'email' => 'staf@mitra.com',
                'password' => Hash::make('password123'),
                'role' => 'staf'
            ]
        ];
        
        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                    'email_verified_at' => now()
                ]
            );
            
            // Assign role ke user
            $role = Role::where('name', $userData['role'])->first();
            if ($role && !$user->hasRole($userData['role'])) {
                $user->assignRole($role);
            }
        }
    }
}
