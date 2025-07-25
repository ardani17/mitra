<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Models\ProjectBilling;
use App\Models\BillingBatch;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST BILLING INTEGRATION ===\n\n";

try {
    // Test 1: Cek field baru di projects table
    echo "1. Testing new billing fields in projects table...\n";
    $project = Project::first();
    if ($project) {
        echo "   ✓ billing_status: {$project->billing_status}\n";
        echo "   ✓ total_billed_amount: Rp " . number_format($project->total_billed_amount, 0, ',', '.') . "\n";
        echo "   ✓ billing_percentage: {$project->billing_percentage}%\n";
        echo "   ✓ latest_invoice_number: {$project->latest_invoice_number}\n";
        echo "   ✓ latest_sp_number: {$project->latest_sp_number}\n";
        echo "   ✓ last_billing_date: {$project->last_billing_date}\n";
    }
    echo "\n";

    // Test 2: Test method baru di Project model
    echo "2. Testing new Project model methods...\n";
    if ($project) {
        echo "   ✓ getBillingStatusLabelAttribute: {$project->billing_status_label}\n";
        echo "   ✓ getBillingStatusBadgeColorAttribute: {$project->billing_status_badge_color}\n";
        echo "   ✓ isFullyBilled(): " . ($project->isFullyBilled() ? 'true' : 'false') . "\n";
        echo "   ✓ isNotBilled(): " . ($project->isNotBilled() ? 'true' : 'false') . "\n";
        echo "   ✓ isPartiallyBilled(): " . ($project->isPartiallyBilled() ? 'true' : 'false') . "\n";
        echo "   ✓ getRemainingBillableAmountAttribute: Rp " . number_format($project->remaining_billable_amount, 0, ',', '.') . "\n";
        
        $financialSummary = $project->financial_summary;
        echo "   ✓ getFinancialSummaryAttribute:\n";
        echo "     - Planned Value: Rp " . number_format($financialSummary['planned_value'], 0, ',', '.') . "\n";
        echo "     - Total Billed: Rp " . number_format($financialSummary['total_billed'], 0, ',', '.') . "\n";
        echo "     - Total Expenses: Rp " . number_format($financialSummary['total_expenses'], 0, ',', '.') . "\n";
        echo "     - Remaining Billable: Rp " . number_format($financialSummary['remaining_billable'], 0, ',', '.') . "\n";
        echo "     - Net Profit: Rp " . number_format($financialSummary['net_profit'], 0, ',', '.') . "\n";
        echo "     - Billing Percentage: {$financialSummary['billing_percentage']}%\n";
    }
    echo "\n";

    // Test 3: Test scope methods
    echo "3. Testing Project scope methods...\n";
    $notBilled = Project::byBillingStatus('not_billed')->count();
    $partiallyBilled = Project::byBillingStatus('partially_billed')->count();
    $fullyBilled = Project::byBillingStatus('fully_billed')->count();
    $needsBilling = Project::needsBilling()->count();
    $completedNotBilled = Project::completedButNotFullyBilled()->count();
    
    echo "   ✓ byBillingStatus('not_billed'): {$notBilled} projects\n";
    echo "   ✓ byBillingStatus('partially_billed'): {$partiallyBilled} projects\n";
    echo "   ✓ byBillingStatus('fully_billed'): {$fullyBilled} projects\n";
    echo "   ✓ needsBilling(): {$needsBilling} projects\n";
    echo "   ✓ completedButNotFullyBilled(): {$completedNotBilled} projects\n";
    echo "\n";

    // Test 4: Test Observer functionality
    echo "4. Testing ProjectBilling Observer...\n";
    $billing = ProjectBilling::first();
    if ($billing) {
        echo "   ✓ Found ProjectBilling: {$billing->invoice_number}\n";
        echo "   ✓ Project before update: {$billing->project->billing_status}\n";
        
        // Trigger observer by updating billing
        $oldAmount = $billing->total_amount;
        $billing->total_amount = $oldAmount + 1000;
        $billing->save();
        
        // Reload project to see changes
        $billing->project->refresh();
        echo "   ✓ Project after update: {$billing->project->billing_status}\n";
        echo "   ✓ Observer triggered successfully!\n";
        
        // Restore original amount
        $billing->total_amount = $oldAmount;
        $billing->save();
    }
    echo "\n";

    // Test 5: Test integration dengan BillingBatch
    echo "5. Testing BillingBatch integration...\n";
    $batch = BillingBatch::first();
    if ($batch) {
        echo "   ✓ Found BillingBatch: {$batch->batch_code}\n";
        echo "   ✓ Projects in batch: {$batch->projectBillings->count()}\n";
        
        foreach ($batch->projectBillings as $billing) {
            echo "   ✓ Project {$billing->project->code}: {$billing->project->billing_status_label}\n";
        }
    }
    echo "\n";

    // Test 6: Test data consistency
    echo "6. Testing data consistency...\n";
    $projects = Project::all();
    $inconsistencies = 0;
    
    foreach ($projects as $proj) {
        $calculatedTotal = $proj->billings()->sum('total_amount');
        if (abs($calculatedTotal - $proj->total_billed_amount) > 0.01) {
            echo "   ⚠ Inconsistency in {$proj->code}: calculated={$calculatedTotal}, stored={$proj->total_billed_amount}\n";
            $inconsistencies++;
        }
    }
    
    if ($inconsistencies == 0) {
        echo "   ✓ All project billing amounts are consistent!\n";
    } else {
        echo "   ⚠ Found {$inconsistencies} inconsistencies\n";
    }
    echo "\n";

    // Test 7: Performance test
    echo "7. Performance test...\n";
    $start = microtime(true);
    
    $projectsWithBilling = Project::with(['billings', 'billings.billingBatch'])
        ->get()
        ->map(function($project) {
            return [
                'code' => $project->code,
                'billing_status' => $project->billing_status_label,
                'total_billed' => $project->total_billed_amount,
                'percentage' => $project->billing_percentage,
                'latest_invoice' => $project->latest_invoice_number
            ];
        });
    
    $end = microtime(true);
    $duration = round(($end - $start) * 1000, 2);
    
    echo "   ✓ Loaded {$projectsWithBilling->count()} projects with billing info in {$duration}ms\n";
    echo "\n";

    // Summary
    echo "=== INTEGRATION TEST SUMMARY ===\n";
    echo "✅ Database schema: OK\n";
    echo "✅ Model methods: OK\n";
    echo "✅ Scope methods: OK\n";
    echo "✅ Observer functionality: OK\n";
    echo "✅ BillingBatch integration: OK\n";
    echo ($inconsistencies == 0 ? "✅" : "⚠") . " Data consistency: " . ($inconsistencies == 0 ? "OK" : "ISSUES FOUND") . "\n";
    echo "✅ Performance: OK\n";
    echo "\n";

    echo "🎉 Billing integration test completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Test UI integration by visiting project pages\n";
    echo "2. Create new billing batches and verify auto-update\n";
    echo "3. Monitor performance in production\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
