<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Billing Batch Policy ===\n\n";

// Test Finance Manager
$financeUser = \App\Models\User::where('email', 'financemanager@mitra.com')->first();
if ($financeUser) {
    echo "Testing Finance Manager (financemanager@mitra.com):\n";
    echo "- Has finance_manager role: " . ($financeUser->hasRole('finance_manager') ? 'Yes' : 'No') . "\n";
    echo "- Has direktur role: " . ($financeUser->hasRole('direktur') ? 'Yes' : 'No') . "\n";
    echo "- Has any role [finance_manager, direktur]: " . ($financeUser->hasAnyRole(['finance_manager', 'direktur']) ? 'Yes' : 'No') . "\n";
    
    // Test policy
    $policy = new \App\Policies\BillingBatchPolicy();
    echo "- Can create billing batch: " . ($policy->create($financeUser) ? 'Yes' : 'No') . "\n";
    echo "- Can view any billing batch: " . ($policy->viewAny($financeUser) ? 'Yes' : 'No') . "\n";
} else {
    echo "Finance Manager not found!\n";
}

echo "\n";

// Test Direktur
$direkturUser = \App\Models\User::where('email', 'direktur@mitra.com')->first();
if ($direkturUser) {
    echo "Testing Direktur (direktur@mitra.com):\n";
    echo "- Has finance_manager role: " . ($direkturUser->hasRole('finance_manager') ? 'Yes' : 'No') . "\n";
    echo "- Has direktur role: " . ($direkturUser->hasRole('direktur') ? 'Yes' : 'No') . "\n";
    echo "- Has any role [finance_manager, direktur]: " . ($direkturUser->hasAnyRole(['finance_manager', 'direktur']) ? 'Yes' : 'No') . "\n";
    
    // Test policy
    $policy = new \App\Policies\BillingBatchPolicy();
    echo "- Can create billing batch: " . ($policy->create($direkturUser) ? 'Yes' : 'No') . "\n";
    echo "- Can view any billing batch: " . ($policy->viewAny($direkturUser) ? 'Yes' : 'No') . "\n";
} else {
    echo "Direktur not found!\n";
}

echo "\n";

// Test Project Manager (should not have access)
$pmUser = \App\Models\User::where('email', 'projectmanager@mitra.com')->first();
if ($pmUser) {
    echo "Testing Project Manager (projectmanager@mitra.com):\n";
    echo "- Has finance_manager role: " . ($pmUser->hasRole('finance_manager') ? 'Yes' : 'No') . "\n";
    echo "- Has direktur role: " . ($pmUser->hasRole('direktur') ? 'Yes' : 'No') . "\n";
    echo "- Has any role [finance_manager, direktur]: " . ($pmUser->hasAnyRole(['finance_manager', 'direktur']) ? 'Yes' : 'No') . "\n";
    
    // Test policy
    $policy = new \App\Policies\BillingBatchPolicy();
    echo "- Can create billing batch: " . ($policy->create($pmUser) ? 'Yes' : 'No') . "\n";
    echo "- Can view any billing batch: " . ($policy->viewAny($pmUser) ? 'Yes' : 'No') . "\n";
} else {
    echo "Project Manager not found!\n";
}
