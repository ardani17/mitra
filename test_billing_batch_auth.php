<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING BILLING BATCH AUTHORIZATION ===\n\n";

// Get Finance Manager user
$financeUser = \App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'finance_manager');
})->first();

if (!$financeUser) {
    echo "❌ Finance Manager user not found!\n";
    exit;
}

echo "Testing with user: {$financeUser->email}\n";
echo "User roles: " . $financeUser->roles->pluck('name')->implode(', ') . "\n\n";

// Test role methods
echo "1. TESTING ROLE METHODS:\n";
echo "   hasRole('finance_manager'): " . ($financeUser->hasRole('finance_manager') ? '✅ true' : '❌ false') . "\n";
echo "   hasAnyRole(['finance_manager', 'direktur']): " . ($financeUser->hasAnyRole(['finance_manager', 'direktur']) ? '✅ true' : '❌ false') . "\n";

// Test policy directly
echo "\n2. TESTING BILLING BATCH POLICY:\n";
$policy = new \App\Policies\BillingBatchPolicy();

echo "   create(): " . ($policy->create($financeUser) ? '✅ true' : '❌ false') . "\n";
echo "   viewAny(): " . ($policy->viewAny($financeUser) ? '✅ true' : '❌ false') . "\n";

// Test using Gate
echo "\n3. TESTING USING GATE:\n";
\Illuminate\Support\Facades\Auth::login($financeUser);

try {
    $canCreate = \Illuminate\Support\Facades\Gate::allows('create', \App\Models\BillingBatch::class);
    echo "   Gate::allows('create', BillingBatch::class): " . ($canCreate ? '✅ true' : '❌ false') . "\n";
} catch (Exception $e) {
    echo "   Gate::allows('create', BillingBatch::class): ❌ ERROR - {$e->getMessage()}\n";
}

// Test authorization in controller context
echo "\n4. TESTING CONTROLLER AUTHORIZATION:\n";
try {
    $controller = new \App\Http\Controllers\BillingBatchController();
    
    // Simulate the authorization call
    $reflection = new ReflectionClass($controller);
    if ($reflection->hasMethod('authorize')) {
        echo "   Controller has authorize method: ✅\n";
    } else {
        echo "   Controller has authorize method: ❌\n";
    }
    
} catch (Exception $e) {
    echo "   Controller test: ❌ ERROR - {$e->getMessage()}\n";
}

// Check if policy is registered
echo "\n5. CHECKING POLICY REGISTRATION:\n";
$policies = \Illuminate\Support\Facades\Gate::policies();
if (isset($policies[\App\Models\BillingBatch::class])) {
    echo "   BillingBatch policy registered: ✅ " . $policies[\App\Models\BillingBatch::class] . "\n";
} else {
    echo "   BillingBatch policy registered: ❌ Not found\n";
}

// Test with actual HTTP request simulation
echo "\n6. TESTING HTTP REQUEST SIMULATION:\n";
try {
    // Create a mock request
    $request = \Illuminate\Http\Request::create('/billing-batches/create', 'GET');
    $request->setUserResolver(function() use ($financeUser) {
        return $financeUser;
    });
    
    // Set the authenticated user
    \Illuminate\Support\Facades\Auth::setUser($financeUser);
    
    echo "   Auth::user(): " . (\Illuminate\Support\Facades\Auth::user() ? '✅ ' . \Illuminate\Support\Facades\Auth::user()->email : '❌ null') . "\n";
    echo "   Auth::check(): " . (\Illuminate\Support\Facades\Auth::check() ? '✅ true' : '❌ false') . "\n";
    
} catch (Exception $e) {
    echo "   HTTP simulation: ❌ ERROR - {$e->getMessage()}\n";
}

echo "\n=== TEST COMPLETE ===\n";
