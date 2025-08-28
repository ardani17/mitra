<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create default users for each role
        $users = [
            [
                'name' => 'Direktur Mitra',
                'email' => 'direktur@mitra.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Project Manager Mitra',
                'email' => 'projectmanager@mitra.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Finance Manager Mitra',
                'email' => 'financemanager@mitra.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Staf Mitra',
                'email' => 'staf@mitra.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Role assignments
        $roleAssignments = [
            'direktur@mitra.com' => 'direktur',
            'projectmanager@mitra.com' => 'project_manager',
            'financemanager@mitra.com' => 'finance_manager',
            'staf@mitra.com' => 'staf'
        ];

        // Insert users
        foreach ($users as $user) {
            // Check if user doesn't exist before inserting
            if (!DB::table('users')->where('email', $user['email'])->exists()) {
                $userId = DB::table('users')->insertGetId($user);
                
                // Assign role to user
                $roleName = $roleAssignments[$user['email']];
                $role = DB::table('roles')->where('name', $roleName)->first();
                
                if ($role && !DB::table('role_users')->where('user_id', $userId)->where('role_id', $role->id)->exists()) {
                    DB::table('role_users')->insert([
                        'user_id' => $userId,
                        'role_id' => $role->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't delete users on rollback as they might have created data
        // This is a safety measure to prevent breaking existing relationships
        
        // Optional: You can add a comment to identify default users
        DB::table('users')
            ->whereIn('email', [
                'direktur@mitra.com',
                'projectmanager@mitra.com',
                'financemanager@mitra.com',
                'staf@mitra.com'
            ])
            ->update(['remember_token' => 'DEFAULT_USER_MIGRATION']);
    }
};