<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Authorization ===\n\n";

// Test Finance Manager
$financeUser = \App\Models\User::where('email', 'financemanager@mitra.com')->first();
if ($financeUser) {
    echo "Testing Finance Manager (financemanager@mitra.com):\n";
    
    // Simulate login
    auth()->login($financeUser);
    
    echo "- User authenticated: " . (auth()->check() ? 'Yes' : 'No') . "\n";
    echo "- Current user: " . (auth()->user() ? auth()->user()->email : 'None') . "\n";
    echo "- User ID: " . (auth()->id() ?? 'None') . "\n";
    
    // Test roles
    echo "- Has finance_manager role: " . ($financeUser->hasRole('finance_manager') ? 'Yes' : 'No') . "\n";
    echo "- Has any role [finance_manager, direktur]: " . ($financeUser->hasAnyRole(['finance_manager', 'direktur']) ? 'Yes' : 'No') . "\n";
    
    // Test policy directly
    $policy = new \App\Policies\BillingBatchPolicy();
    echo "- Policy create() result: " . ($policy->create($financeUser) ? 'Yes' : 'No') . "\n";
    
    // Test using Gate
    try {
        $canCreate = \Illuminate\Support\Facades\Gate::allows('create', \App\Models\BillingBatch::class);
        echo "- Gate allows create: " . ($canCreate ? 'Yes' : 'No') . "\n";
    } catch (Exception $e) {
        echo "- Gate error: " . $e->getMessage() . "\n";
    }
    
    // Test using authorize method
    try {
        $controller = new \App\Http\Controllers\BillingBatchController();
        $controller->authorize('create', \App\Models\BillingBatch::class);
        echo "- Controller authorize: Success\n";
    } catch (Exception $e) {
        echo "- Controller authorize error: " . $e->getMessage() . "\n";
    }
    
    auth()->logout();
} else {
    echo "Finance Manager not found!\n";
}

echo "\n";

// Test Direktur
$direkturUser = \App\Models\User::where('email', 'direktur@mitra.com')->first();
if ($direkturUser) {
    echo "Testing Direktur (direktur@mitra.com):\n";
    
    // Simulate login
    auth()->login($direkturUser);
    
    echo "- User authenticated: " . (auth()->check() ? 'Yes' : 'No') . "\n";
    echo "- Current user: " . (auth()->user() ? auth()->user()->email : 'None') . "\n";
    
    // Test roles
    echo "- Has direktur role: " . ($direkturUser->hasRole('direktur') ? 'Yes' : 'No') . "\n";
    echo "- Has any role [finance_manager, direktur]: " . ($direkturUser->hasAnyRole(['finance_manager', 'direktur']) ? 'Yes' : 'No') . "\n";
    
    // Test policy directly
    $policy = new \App\Policies\BillingBatchPolicy();
    echo "- Policy create() result: " . ($policy->create($direkturUser) ? 'Yes' : 'No') . "\n";
    
    // Test using Gate
    try {
        $canCreate = \Illuminate\Support\Facades\Gate::allows('create', \App\Models\BillingBatch::class);
        echo "- Gate allows create: " . ($canCreate ? 'Yes' : 'No') . "\n";
    } catch (Exception $e) {
        echo "- Gate error: " . $e->getMessage() . "\n";
    }
    
    // Test using authorize method
    try {
        $controller = new \App\Http\Controllers\BillingBatchController();
        $controller->authorize('create', \App\Models\BillingBatch::class);
        echo "- Controller authorize: Success\n";
    } catch (Exception $e) {
        echo "- Controller authorize error: " . $e->getMessage() . "\n";
    }
    
    auth()->logout();
} else {
    echo "Direktur not found!\n";
}

echo "\n";

// Test Project Manager (should fail)
$pmUser = \App\Models\User::where('email', 'projectmanager@mitra.com')->first();
if ($pmUser) {
    echo "Testing Project Manager (projectmanager@mitra.com):\n";
    
    // Simulate login
    auth()->login($pmUser);
    
    echo "- User authenticated: " . (auth()->check() ? 'Yes' : 'No') . "\n";
    echo "- Current user: " . (auth()->user() ? auth()->user()->email : 'None') . "\n";
    
    // Test roles
    echo "- Has project_manager role: " . ($pmUser->hasRole('project_manager') ? 'Yes' : 'No') . "\n";
    echo "- Has any role [finance_manager, direktur]: " . ($pmUser->hasAnyRole(['finance_manager', 'direktur']) ? 'Yes' : 'No') . "\n";
    
    // Test policy directly
    $policy = new \App\Policies\BillingBatchPolicy();
    echo "- Policy create() result: " . ($policy->create($pmUser) ? 'Yes' : 'No') . "\n";
    
    // Test using Gate
    try {
        $canCreate = \Illuminate\Support\Facades\Gate::allows('create', \App\Models\BillingBatch::class);
        echo "- Gate allows create: " . ($canCreate ? 'Yes' : 'No') . "\n";
    } catch (Exception $e) {
        echo "- Gate error: " . $e->getMessage() . "\n";
    }
    
    // Test using authorize method
    try {
        $controller = new \App\Http\Controllers\BillingBatchController();
        $controller->authorize('create', \App\Models\BillingBatch::class);
        echo "- Controller authorize: Success\n";
    } catch (Exception $e) {
        echo "- Controller authorize error: " . $e->getMessage() . "\n";
    }
    
    auth()->logout();
} else {
    echo "Project Manager not found!\n";
}
