<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'direktur',
                'description' => 'Direktur perusahaan dengan akses penuh'
            ],
            [
                'name' => 'project_manager',
                'description' => 'Manager proyek dengan akses manajemen proyek'
            ],
            [
                'name' => 'finance_manager',
                'description' => 'Manager keuangan dengan akses manajemen keuangan'
            ],
            [
                'name' => 'staf',
                'description' => 'Staf dengan akses input data dasar'
            ]
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
