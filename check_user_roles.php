<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking User Roles ===\n\n";

// Check all users and their roles
$users = DB::table('users')
    ->leftJoin('role_users', 'users.id', '=', 'role_users.user_id')
    ->leftJoin('roles', 'role_users.role_id', '=', 'roles.id')
    ->select('users.id', 'users.name', 'users.email', 'roles.name as role_name')
    ->get();

echo "Users and their roles:\n";
foreach ($users as $user) {
    echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: " . ($user->role_name ?? 'No Role') . "\n";
}

echo "\n=== Checking Roles Table ===\n";
$roles = DB::table('roles')->get();
foreach ($roles as $role) {
    echo "ID: {$role->id}, Name: {$role->name}\n";
}

echo "\n=== Checking Role Users Table ===\n";
$roleUsers = DB::table('role_users')
    ->join('users', 'role_users.user_id', '=', 'users.id')
    ->join('roles', 'role_users.role_id', '=', 'roles.id')
    ->select('users.email', 'roles.name as role_name')
    ->get();

foreach ($roleUsers as $roleUser) {
    echo "User: {$roleUser->email}, Role: {$roleUser->role_name}\n";
}

echo "\n=== Testing hasAnyRole for finance@mitra.com ===\n";
$user = \App\Models\User::where('email', 'finance@mitra.com')->first();
if ($user) {
    echo "User found: {$user->name}\n";
    echo "Has finance_manager role: " . ($user->hasRole('finance_manager') ? 'Yes' : 'No') . "\n";
    echo "Has direktur role: " . ($user->hasRole('direktur') ? 'Yes' : 'No') . "\n";
    echo "Has any role [finance_manager, direktur]: " . ($user->hasAnyRole(['finance_manager', 'direktur']) ? 'Yes' : 'No') . "\n";
    
    echo "User roles:\n";
    foreach ($user->roles as $role) {
        echo "- {$role->name}\n";
    }
} else {
    echo "User finance@mitra.com not found!\n";
}

echo "\n=== Testing hasAnyRole for direktur@mitra.com ===\n";
$user = \App\Models\User::where('email', 'direktur@mitra.com')->first();
if ($user) {
    echo "User found: {$user->name}\n";
    echo "Has finance_manager role: " . ($user->hasRole('finance_manager') ? 'Yes' : 'No') . "\n";
    echo "Has direktur role: " . ($user->hasRole('direktur') ? 'Yes' : 'No') . "\n";
    echo "Has any role [finance_manager, direktur]: " . ($user->hasAnyRole(['finance_manager', 'direktur']) ? 'Yes' : 'No') . "\n";
    
    echo "User roles:\n";
    foreach ($user->roles as $role) {
        echo "- {$role->name}\n";
    }
} else {
    echo "User direktur@mitra.com not found!\n";
}
