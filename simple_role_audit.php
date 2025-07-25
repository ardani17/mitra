<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SIMPLE ROLE AUDIT ===\n\n";

// 1. Check database roles
echo "1. DATABASE ROLES:\n";
$roles = \App\Models\Role::all();
foreach($roles as $role) {
    echo "   - {$role->name}\n";
}

// 2. Check users and their roles
echo "\n2. USER ROLES:\n";
$users = \App\Models\User::with('roles')->get();
foreach($users as $user) {
    echo "   {$user->email}: ";
    $roleNames = $user->roles->pluck('name')->toArray();
    echo implode(', ', $roleNames) . "\n";
}

// 3. Check for inconsistencies in key files
echo "\n3. CHECKING KEY FILES:\n";

$filesToCheck = [
    'app/Http/Controllers/BillingBatchController.php',
    'app/Policies/BillingBatchPolicy.php',
    'app/Policies/BillingPolicy.php',
    'app/Http/Requests/BillingBatchRequest.php'
];

foreach($filesToCheck as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        echo "\n   Checking {$file}:\n";
        
        // Check for director vs direktur
        if (strpos($content, "'director'") !== false || strpos($content, '"director"') !== false) {
            echo "     ❌ Found 'director' - should be 'direktur'\n";
        }
        
        if (strpos($content, "'direktur'") !== false || strpos($content, '"direktur"') !== false) {
            echo "     ✅ Found 'direktur' - correct\n";
        }
        
        // Check for staff vs staf
        if (strpos($content, "'staff'") !== false || strpos($content, '"staff"') !== false) {
            echo "     ❌ Found 'staff' - should be 'staf'\n";
        }
        
        if (strpos($content, "'staf'") !== false || strpos($content, '"staf"') !== false) {
            echo "     ✅ Found 'staf' - correct\n";
        }
    }
}

echo "\n4. TESTING ROLE METHODS:\n";

// Test User model role methods
$testUser = \App\Models\User::first();
if ($testUser) {
    echo "   Testing with user: {$testUser->email}\n";
    
    // Test hasRole method
    try {
        $hasDirector = $testUser->hasRole('director');
        echo "     hasRole('director'): " . ($hasDirector ? 'true' : 'false') . "\n";
    } catch (Exception $e) {
        echo "     hasRole('director'): ERROR - {$e->getMessage()}\n";
    }
    
    try {
        $hasDirektur = $testUser->hasRole('direktur');
        echo "     hasRole('direktur'): " . ($hasDirektur ? 'true' : 'false') . "\n";
    } catch (Exception $e) {
        echo "     hasRole('direktur'): ERROR - {$e->getMessage()}\n";
    }
}

echo "\n=== AUDIT COMPLETE ===\n";
