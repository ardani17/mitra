<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProjectExpense;
use App\Models\ExpenseModificationApproval;
use App\Models\CashflowEntry;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestExpenseModificationWorkflow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:expense-modification-workflow 
                            {--cleanup : Clean up test data after running}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the expense modification approval workflow functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Expense Modification Workflow...');
        $this->newLine();
        
        $cleanup = $this->option('cleanup');
        $testResults = [];
        $testExpenseIds = [];
        $testModificationIds = [];
        
        try {
            // Test 1: Get test data
            $this->info('Test 1: Getting test project and users...');
            $project = Project::first();
            $user = User::first();
            $approver = User::whereHas('roles', function($q) {
                $q->where('name', 'finance_manager');
            })->first() ?? User::skip(1)->first();
            
            if (!$project || !$user || !$approver) {
                $this->error('❌ Missing test data. Need at least 1 project and 2 users.');
                return 1;
            }
            
            $this->info("✅ Using project: {$project->name}");
            $this->info("✅ Using user: {$user->name}");
            $this->info("✅ Using approver: {$approver->name}");
            $testResults['test_data_available'] = true;
            
            // Test 2: Create approved expense
            $this->info('Test 2: Creating approved expense...');
            $expense = ProjectExpense::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'description' => 'Test Expense for Modification',
                'amount' => 100000,
                'expense_date' => now(),
                'category' => 'material',
                'status' => 'approved'
            ]);
            
            $testExpenseIds[] = $expense->id;
            
            // Verify cashflow entry was created
            $cashflowEntry = CashflowEntry::where('reference_type', 'expense')
                ->where('reference_id', $expense->id)
                ->first();
                
            if ($cashflowEntry) {
                $this->info("✅ Approved expense created with cashflow integration");
                $testResults['approved_expense_created'] = true;
            } else {
                $this->error("❌ Cashflow entry not created for approved expense");
                $testResults['approved_expense_created'] = false;
            }
            
            // Test 3: Test expense modification permissions
            $this->info('Test 3: Testing expense modification permissions...');
            
            if ($expense->canBeModified()) {
                $this->info("✅ Approved expense can be modified");
                $testResults['can_be_modified'] = true;
            } else {
                $this->error("❌ Approved expense cannot be modified");
                $testResults['can_be_modified'] = false;
            }
            
            if (!$expense->canBeDirectlyEdited()) {
                $this->info("✅ Approved expense cannot be directly edited (correct)");
                $testResults['cannot_direct_edit'] = true;
            } else {
                $this->error("❌ Approved expense can be directly edited (incorrect)");
                $testResults['cannot_direct_edit'] = false;
            }
            
            // Test 4: Create edit modification request
            $this->info('Test 4: Creating edit modification request...');
            $proposedData = [
                'project_id' => $project->id,
                'description' => 'Modified Test Expense',
                'amount' => 150000,
                'expense_date' => now()->format('Y-m-d'),
                'category' => 'equipment',
                'receipt_number' => 'MOD-001',
                'vendor' => 'Test Vendor',
                'notes' => 'Modified notes'
            ];
            
            $editRequest = $expense->requestEdit($proposedData, 'Testing edit modification workflow', $user);
            $testModificationIds[] = $editRequest->id;
            
            if ($editRequest && $editRequest->isPending()) {
                $this->info("✅ Edit modification request created successfully");
                $this->info("   - Request ID: {$editRequest->id}");
                $this->info("   - Status: {$editRequest->status}");
                $testResults['edit_request_created'] = true;
            } else {
                $this->error("❌ Failed to create edit modification request");
                $testResults['edit_request_created'] = false;
            }
            
            // Test 5: Test changes summary
            $this->info('Test 5: Testing changes summary...');
            $changes = $editRequest->getChangesSummary();
            
            if (!empty($changes)) {
                $this->info("✅ Changes summary generated:");
                foreach ($changes as $field => $change) {
                    $this->info("   - {$change['field_name']}: {$change['old']} → {$change['new']}");
                }
                $testResults['changes_summary'] = true;
            } else {
                $this->error("❌ No changes detected in summary");
                $testResults['changes_summary'] = false;
            }
            
            // Test 6: Approve edit request
            $this->info('Test 6: Approving edit modification request...');
            $editRequest->approve($approver, 'Approved for testing');
            
            // Check if expense was updated
            $expense->refresh();
            if ($expense->description === 'Modified Test Expense' && $expense->amount == 150000) {
                $this->info("✅ Expense updated after approval");
                $this->info("   - New description: {$expense->description}");
                $this->info("   - New amount: Rp " . number_format($expense->amount, 0, ',', '.'));
                $testResults['edit_applied'] = true;
            } else {
                $this->error("❌ Expense not updated after approval");
                $testResults['edit_applied'] = false;
            }
            
            // Check if cashflow entry was updated
            $cashflowEntry->refresh();
            if ($cashflowEntry->amount == 150000) {
                $this->info("✅ Cashflow entry updated after expense modification");
                $testResults['cashflow_updated'] = true;
            } else {
                $this->error("❌ Cashflow entry not updated after expense modification");
                $testResults['cashflow_updated'] = false;
            }
            
            // Test 7: Create delete modification request
            $this->info('Test 7: Creating delete modification request...');
            $deleteRequest = $expense->requestDelete('Testing delete modification workflow', $user);
            $testModificationIds[] = $deleteRequest->id;
            
            if ($deleteRequest && $deleteRequest->isPending()) {
                $this->info("✅ Delete modification request created successfully");
                $this->info("   - Request ID: {$deleteRequest->id}");
                $this->info("   - Status: {$deleteRequest->status}");
                $testResults['delete_request_created'] = true;
            } else {
                $this->error("❌ Failed to create delete modification request");
                $testResults['delete_request_created'] = false;
            }
            
            // Test 8: Test high-level approval requirement
            $this->info('Test 8: Testing high-level approval requirements...');
            if ($deleteRequest->requiresHighLevelApproval()) {
                $this->info("✅ Delete request requires high-level approval (correct)");
                $testResults['requires_high_level'] = true;
            } else {
                $this->error("❌ Delete request doesn't require high-level approval (incorrect)");
                $testResults['requires_high_level'] = false;
            }
            
            $requiredLevels = $deleteRequest->getRequiredApprovalLevels();
            if (in_array('direktur', $requiredLevels)) {
                $this->info("✅ Director approval required for delete request");
                $testResults['director_approval_required'] = true;
            } else {
                $this->error("❌ Director approval not required for delete request");
                $testResults['director_approval_required'] = false;
            }
            
            // Test 9: Approve delete request
            $this->info('Test 9: Approving delete modification request...');
            $deleteRequest->approve($approver, 'Approved for testing');
            
            // Check if expense was soft deleted
            $expense->refresh();
            if ($expense->trashed()) {
                $this->info("✅ Expense soft deleted after approval");
                $testResults['expense_deleted'] = true;
            } else {
                $this->error("❌ Expense not deleted after approval");
                $testResults['expense_deleted'] = false;
            }
            
            // Check if cashflow entry was cancelled
            $cashflowEntry->refresh();
            if ($cashflowEntry->status === 'cancelled') {
                $this->info("✅ Cashflow entry cancelled after expense deletion");
                $testResults['cashflow_cancelled'] = true;
            } else {
                $this->error("❌ Cashflow entry not cancelled after expense deletion");
                $testResults['cashflow_cancelled'] = false;
            }
            
            // Test 10: Test rejection workflow
            $this->info('Test 10: Testing rejection workflow...');
            $expense2 = ProjectExpense::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'description' => 'Test Expense for Rejection',
                'amount' => 75000,
                'expense_date' => now(),
                'category' => 'other',
                'status' => 'approved'
            ]);
            
            $testExpenseIds[] = $expense2->id;
            
            $rejectRequest = $expense2->requestEdit([
                'description' => 'Should be rejected',
                'amount' => 80000
            ], 'Testing rejection workflow', $user);
            
            $testModificationIds[] = $rejectRequest->id;
            
            // Reject the request
            $rejectRequest->reject($approver, 'Rejected for testing');
            
            // Check if expense was NOT updated
            $expense2->refresh();
            if ($expense2->description === 'Test Expense for Rejection') {
                $this->info("✅ Expense not updated after rejection (correct)");
                $testResults['rejection_works'] = true;
            } else {
                $this->error("❌ Expense updated after rejection (incorrect)");
                $testResults['rejection_works'] = false;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Test failed with error: " . $e->getMessage());
            Log::error('Expense modification workflow test failed', [
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
            $this->info('🎉 All tests passed! Expense modification workflow is working correctly.');
        } else {
            $this->error('⚠️  Some tests failed. Please check the implementation.');
        }
        
        // Cleanup test data if requested
        if ($cleanup) {
            $this->info('Cleaning up test data...');
            
            // Delete test modification requests
            ExpenseModificationApproval::whereIn('id', $testModificationIds)->delete();
            
            // Delete test cashflow entries
            CashflowEntry::where('reference_type', 'expense')
                ->whereIn('reference_id', $testExpenseIds)
                ->delete();
                
            // Force delete test expenses (including soft deleted ones)
            ProjectExpense::whereIn('id', $testExpenseIds)->withTrashed()->forceDelete();
            
            $this->info('✅ Test data cleaned up');
        } else {
            $this->warn('Test data not cleaned up. Use --cleanup flag to remove test data.');
            $this->info('Test expense IDs: ' . implode(', ', $testExpenseIds));
            $this->info('Test modification IDs: ' . implode(', ', $testModificationIds));
        }
        
        return $passed === $total ? 0 : 1;
    }
}