<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use App\Models\SalaryRelease;
use App\Models\CashflowEntry;

echo "=== TEST CASHFLOW PAID FIX ===\n\n";

// Find a salary release that is currently 'released'
$salaryRelease = SalaryRelease::where('status', 'released')->first();

if (!$salaryRelease) {
    echo "No salary release with 'released' status found. Creating test scenario...\n";
    
    // Find any draft salary release
    $salaryRelease = SalaryRelease::where('status', 'draft')->first();
    
    if (!$salaryRelease) {
        echo "No salary releases found at all.\n";
        exit;
    }
    
    // Release it first
    echo "Releasing salary release ID: {$salaryRelease->id}\n";
    $salaryRelease->update([
        'status' => 'released',
        'released_by' => 1,
        'released_at' => now()
    ]);
    
    // Refresh to get updated data
    $salaryRelease->refresh();
}

echo "Testing with Salary Release ID: {$salaryRelease->id}\n";
echo "Employee: {$salaryRelease->employee->name}\n";
echo "Current Status: {$salaryRelease->status}\n";
echo "Net Amount: Rp " . number_format($salaryRelease->net_amount, 0, ',', '.') . "\n";

// Check if cashflow entry exists
$cashflowEntry = $salaryRelease->cashflowEntry;
if ($cashflowEntry) {
    echo "✓ Cashflow Entry exists (ID: {$cashflowEntry->id})\n";
    echo "  - Description: {$cashflowEntry->description}\n";
    echo "  - Amount: Rp " . number_format($cashflowEntry->amount, 0, ',', '.') . "\n";
    echo "  - Type: {$cashflowEntry->type}\n";
    echo "  - Status: {$cashflowEntry->status}\n";
} else {
    echo "✗ No cashflow entry found\n";
    exit;
}

echo "\n--- MARKING AS PAID ---\n";

// Mark as paid
$salaryRelease->update([
    'status' => 'paid',
    'paid_at' => now()
]);

// Refresh to get updated data
$salaryRelease->refresh();
$cashflowEntry->refresh();

echo "New Status: {$salaryRelease->status}\n";
echo "Paid At: {$salaryRelease->paid_at}\n";

// Check if cashflow entry still exists
if ($salaryRelease->cashflowEntry) {
    echo "✓ Cashflow Entry still exists (ID: {$salaryRelease->cashflowEntry->id})\n";
    echo "  - Description: {$salaryRelease->cashflowEntry->description}\n";
    echo "  - Amount: Rp " . number_format($salaryRelease->cashflowEntry->amount, 0, ',', '.') . "\n";
    echo "  - Type: {$salaryRelease->cashflowEntry->type}\n";
    echo "  - Status: {$salaryRelease->cashflowEntry->status}\n";
    echo "  - Transaction Date: {$salaryRelease->cashflowEntry->transaction_date}\n";
    echo "  - Notes: {$salaryRelease->cashflowEntry->notes}\n";
} else {
    echo "✗ Cashflow entry was removed (THIS IS THE BUG!)\n";
}

echo "\n--- TESTING REVERT TO DRAFT ---\n";

// Test reverting to draft (should remove cashflow entry)
$salaryRelease->update(['status' => 'draft']);
$salaryRelease->refresh();

echo "Reverted Status: {$salaryRelease->status}\n";

if ($salaryRelease->cashflowEntry) {
    echo "✗ Cashflow Entry still exists (should be removed when reverted to draft)\n";
} else {
    echo "✓ Cashflow Entry correctly removed when reverted to draft\n";
}

echo "\n=== TEST COMPLETE ===\n";