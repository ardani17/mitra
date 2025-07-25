<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Current User Data ===\n\n";

$users = \App\Models\User::with('roles')->get();
foreach($users as $user) {
    echo "ID: {$user->id}, Email: {$user->email}, Name: {$user->name}\n";
    foreach($user->roles as $role) {
        echo "  - Role: {$role->name}\n";
    }
    echo "\n";
}

echo "=== Updating User Data ===\n\n";

// Update users to match the required emails and roles
$userUpdates = [
    [
        'old_email' => 'direktur@mitra.com',
        'new_email' => 'direktur@mitra.com',
        'name' => 'Direktur Mitra',
        'role' => 'direktur'
    ],
    [
        'old_email' => 'projectmanager@mitra.com', 
        'new_email' => 'projectmanager@mitra.com',
        'name' => 'Project Manager Mitra',
        'role' => 'project_manager'
    ],
    [
        'old_email' => 'financemanager@mitra.com',
        'new_email' => 'financemanager@mitra.com', 
        'name' => 'Finance Manager Mitra',
        'role' => 'finance_manager'
    ],
    [
        'old_email' => 'staf@mitra.com',
        'new_email' => 'staf@mitra.com',
        'name' => 'Staf Mitra', 
        'role' => 'staf'
    ]
];

foreach($userUpdates as $update) {
    $user = \App\Models\User::where('email', $update['old_email'])->first();
    
    if ($user) {
        echo "Updating existing user: {$update['old_email']}\n";
        
        // Update user data
        $user->update([
            'email' => $update['new_email'],
            'name' => $update['name'],
            'password' => bcrypt('password123')
        ]);
        
        // Get the role
        $role = \App\Models\Role::where('name', $update['role'])->first();
        if ($role) {
            // Remove all existing roles
            $user->roles()->detach();
            // Assign new role
            $user->roles()->attach($role->id);
            echo "  - Assigned role: {$update['role']}\n";
        }
    } else {
        echo "Creating new user: {$update['new_email']}\n";
        
        // Create new user
        $user = \App\Models\User::create([
            'email' => $update['new_email'],
            'name' => $update['name'],
            'password' => bcrypt('password123'),
            'email_verified_at' => now()
        ]);
        
        // Get the role
        $role = \App\Models\Role::where('name', $update['role'])->first();
        if ($role) {
            // Assign role
            $user->roles()->attach($role->id);
            echo "  - Assigned role: {$update['role']}\n";
        }
    }
}

echo "\n=== Updated User Data ===\n\n";

$users = \App\Models\User::with('roles')->get();
foreach($users as $user) {
    echo "ID: {$user->id}, Email: {$user->email}, Name: {$user->name}\n";
    foreach($user->roles as $role) {
        echo "  - Role: {$role->name}\n";
    }
    echo "\n";
}

echo "User data has been updated successfully!\n";
