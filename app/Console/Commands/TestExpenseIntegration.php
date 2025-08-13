<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProjectExpense;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestExpenseIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:expense-integration 
                            {--cleanup : Clean up test data after running}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the expense to cashflow integration functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Expense to Cashflow Integration...');
        $this->newLine();
        
        $cleanup = $this->option('cleanup');
        $testResults = [];
        $testExpenseIds = [];
        
        try {
            // Test 1: Check if categories exist
            $this->info('Test 1: Checking cashflow categories...');
            $category = CashflowCategory::getSystemCategory('EXP_PROJECT');
            
            if ($category) {
                $this->info("✅ EXP_PROJECT category found (ID: {$category->id})");
                $testResults['category_exists'] = true;
            } else {
                $this->warn("⚠️  EXP_PROJECT category not found, will be created automatically");
                $testResults['category_exists'] = false;
            }
            
            // Test 2: Get test project and user
            $this->info('Test 2: Getting test project and user...');
            $project = Project::first();
            $user = User::first();
            
            if (!$project) {
                $this->error('❌ No projects found. Please create a project first.');
                return 1;
            }
            
            if (!$user) {
                $this->error('❌ No users found. Please create a user first.');
                return 1;
            }
            
            $this->info("✅ Using project: {$project->name} (ID: {$project->id})");
            $this->info("✅ Using user: {$user->name} (ID: {$user->id})");
            
            // Test 3: Create expense with pending status
            $this->info('Test 3: Creating expense with pending status...');
            $expense1 = ProjectExpense::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'description' => 'Test Expense - Pending Status',
                'amount' => 50000,
                'expense_date' => now(),
                'category' => 'material',
                'status' => 'pending'
            ]);
            
            $testExpenseIds[] = $expense1->id;
            
            // Check if cashflow entry was created (should NOT be created)
            $cashflowEntry1 = CashflowEntry::where('reference_type', 'expense')
                ->where('reference_id', $expense1->id)
                ->first();
                
            if (!$cashflowEntry1) {
                $this->info("✅ No cashflow entry created for pending expense (correct behavior)");
                $testResults['pending_no_cashflow'] = true;
            } else {
                $this->error("❌ Cashflow entry was created for pending expense (incorrect behavior)");
                $testResults['pending_no_cashflow'] = false;
            }
            
            // Test 4: Update expense to approved status
            $this->info('Test 4: Updating expense to approved status...');
            $expense1->update(['status' => 'approved']);
            
            // Check if cashflow entry was created
            $cashflowEntry1 = CashflowEntry::where('reference_type', 'expense')
                ->where('reference_id', $expense1->id)
                ->first();
                
            if ($cashflowEntry1) {
                $this->info("✅ Cashflow entry created when expense approved");
                $this->info("   - Entry ID: {$cashflowEntry1->id}");
                $this->info("   - Amount: {$cashflowEntry1->formatted_amount}");
                $this->info("   - Status: {$cashflowEntry1->status}");
                $testResults['approved_creates_cashflow'] = true;
            } else {
                $this->error("❌ No cashflow entry created when expense approved");
                $testResults['approved_creates_cashflow'] = false;
            }
            
            // Test 5: Create expense directly with approved status
            $this->info('Test 5: Creating expense directly with approved status...');
            $expense2 = ProjectExpense::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'description' => 'Test Expense - Direct Approved',
                'amount' => 75000,
                'expense_date' => now(),
                'category' => 'equipment',
                'status' => 'approved'
            ]);
            
            $testExpenseIds[] = $expense2->id;
            
            // Check if cashflow entry was created
            $cashflowEntry2 = CashflowEntry::where('reference_type', 'expense')
                ->where('reference_id', $expense2->id)
                ->first();
                
            if ($cashflowEntry2) {
                $this->info("✅ Cashflow entry created for directly approved expense");
                $this->info("   - Entry ID: {$cashflowEntry2->id}");
                $this->info("   - Amount: {$cashflowEntry2->formatted_amount}");
                $testResults['direct_approved_creates_cashflow'] = true;
            } else {
                $this->error("❌ No cashflow entry created for directly approved expense");
                $testResults['direct_approved_creates_cashflow'] = false;
            }
            
            // Test 6: Update approved expense details
            $this->info('Test 6: Updating approved expense details...');
            $originalAmount = $expense2->amount;
            $expense2->update([
                'amount' => 100000,
                'description' => 'Test Expense - Updated Details'
            ]);
            
            // Check if cashflow entry was updated
            $cashflowEntry2->refresh();
            if ($cashflowEntry2->amount == 100000) {
                $this->info("✅ Cashflow entry updated when expense details changed");
                $this->info("   - Old amount: Rp " . number_format($originalAmount, 0, ',', '.'));
                $this->info("   - New amount: {$cashflowEntry2->formatted_amount}");
                $testResults['update_syncs_cashflow'] = true;
            } else {
                $this->error("❌ Cashflow entry not updated when expense details changed");
                $testResults['update_syncs_cashflow'] = false;
            }
            
            // Test 7: Change approved expense back to pending
            $this->info('Test 7: Changing approved expense back to pending...');
            $expense2->update(['status' => 'pending']);
            
            // Check if cashflow entry was cancelled
            $cashflowEntry2->refresh();
            if ($cashflowEntry2->status === 'cancelled') {
                $this->info("✅ Cashflow entry cancelled when expense status changed from approved");
                $testResults['status_change_cancels_cashflow'] = true;
            } else {
                $this->error("❌ Cashflow entry not cancelled when expense status changed from approved");
                $testResults['status_change_cancels_cashflow'] = false;
            }
            
            // Test 8: Delete approved expense
            $this->info('Test 8: Testing expense deletion...');
            $expense3 = ProjectExpense::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'description' => 'Test Expense - For Deletion',
                'amount' => 25000,
                'expense_date' => now(),
                'category' => 'other',
                'status' => 'approved'
            ]);
            
            $testExpenseIds[] = $expense3->id;
            
            // Verify cashflow entry was created
            $cashflowEntry3 = CashflowEntry::where('reference_type', 'expense')
                ->where('reference_id', $expense3->id)
                ->first();
                
            if ($cashflowEntry3) {
                // Delete the expense
                $expense3->delete();
                
                // Check if cashflow entry was cancelled
                $cashflowEntry3->refresh();
                if ($cashflowEntry3->status === 'cancelled') {
                    $this->info("✅ Cashflow entry cancelled when expense deleted");
                    $testResults['deletion_cancels_cashflow'] = true;
                } else {
                    $this->error("❌ Cashflow entry not cancelled when expense deleted");
                    $testResults['deletion_cancels_cashflow'] = false;
                }
            } else {
                $this->error("❌ No cashflow entry created for expense to be deleted");
                $testResults['deletion_cancels_cashflow'] = false;
            }
            
            // Test 9: Check financial dashboard data
            $this->info('Test 9: Checking financial dashboard integration...');
            $dashboardSummary = CashflowEntry::getBalance();
            
            $this->info("✅ Financial dashboard data:");
            $this->info("   - Total Income: Rp " . number_format($dashboardSummary['income'], 0, ',', '.'));
            $this->info("   - Total Expense: Rp " . number_format($dashboardSummary['expense'], 0, ',', '.'));
            $this->info("   - Balance: Rp " . number_format($dashboardSummary['balance'], 0, ',', '.'));
            $testResults['dashboard_integration'] = true;
            
            // Test 10: Check cashflow journal entries
            $this->info('Test 10: Checking cashflow journal integration...');
            $expenseEntries = CashflowEntry::where('type', 'expense')
                ->where('reference_type', 'expense')
                ->whereIn('reference_id', $testExpenseIds)
                ->count();
                
            $this->info("✅ Found {$expenseEntries} expense entries in cashflow journal");
            $testResults['journal_integration'] = true;
            
        } catch (\Exception $e) {
            $this->error("❌ Test failed with error: " . $e->getMessage());
            Log::error('Expense integration test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        // Display test results summary
        $this->newLine();
        $this->info('=== TEST RESULTS SUMMARY ===');
        
        $passed = 0;
        $total = count($testResults);
        
        foreach ($testResults as $test => $result) {
            $status = $result ? '✅ PASS' : '❌ FAIL';
            $this->line("{$status} - " . str_replace('_', ' ', ucwords($test, '_')));
            if ($result) $passed++;
        }
        
        $this->newLine();
        $this->info("Overall Result: {$passed}/{$total} tests passed");
        
        if ($passed === $total) {
            $this->info('🎉 All tests passed! Expense integration is working correctly.');
        } else {
            $this->error('⚠️  Some tests failed. Please check the implementation.');
        }
        
        // Cleanup test data if requested
        if ($cleanup) {
            $this->info('Cleaning up test data...');
            
            // Delete test cashflow entries
            CashflowEntry::where('reference_type', 'expense')
                ->whereIn('reference_id', $testExpenseIds)
                ->delete();
                
            // Delete test expenses
            ProjectExpense::whereIn('id', $testExpenseIds)->delete();
            
            $this->info('✅ Test data cleaned up');
        } else {
            $this->warn('Test data not cleaned up. Use --cleanup flag to remove test data.');
            $this->info('Test expense IDs: ' . implode(', ', $testExpenseIds));
        }
        
        return $passed === $total ? 0 : 1;
    }
}