<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Setting;
use App\Models\ProjectExpense;
use App\Models\Project;
use App\Models\CashflowEntry;
use App\Services\BypassApprovalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestBypassApprovalScenario extends Command
{
    protected $signature = 'test:bypass-approval {--amount=20000000} {--user-id=}';
    protected $description = 'Test bypass approval scenario with high value expense';

    public function handle()
    {
        $amount = $this->option('amount');
        $userId = $this->option('user-id');
        
        $this->info("=== Testing Bypass Approval Scenario ===");
        $this->info("Amount: Rp " . number_format($amount));
        
        // Get or find director user
        if ($userId) {
            $user = User::find($userId);
        } else {
            $user = User::whereHas('roles', function($q) {
                $q->where('name', 'direktur');
            })->first();
        }
        
        if (!$user) {
            $this->error("No director user found. Please specify --user-id");
            return 1;
        }
        
        $this->info("User: {$user->name} (ID: {$user->id})");
        $this->info("User Roles: " . $user->roles->pluck('name')->implode(', '));
        
        // Check current settings
        $bypassEnabled = Setting::isDirectorBypassEnabled();
        $highAmountThreshold = Setting::get('expense_high_amount_threshold', 10000000);
        
        $this->info("\n=== Current Settings ===");
        $this->info("Bypass Approval Enabled: " . ($bypassEnabled ? 'YES' : 'NO'));
        $this->info("High Amount Threshold: Rp " . number_format($highAmountThreshold));
        $this->info("Amount > Threshold: " . ($amount > $highAmountThreshold ? 'YES (requires director approval)' : 'NO'));
        
        // Check bypass conditions
        $canBypass = BypassApprovalService::canBypass($user);
        $this->info("\n=== Bypass Check ===");
        $this->info("Can User Bypass: " . ($canBypass ? 'YES' : 'NO'));
        
        // Get a project for testing
        $project = Project::first();
        if (!$project) {
            $this->error("No project found for testing");
            return 1;
        }
        
        $this->info("\n=== Creating Test Expense ===");
        $this->info("Project: {$project->name} (ID: {$project->id})");
        
        // Simulate expense creation
        $expenseData = [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'description' => 'Test Expense - Bypass Approval Scenario',
            'amount' => $amount,
            'expense_date' => now(),
            'category' => 'other',
            'receipt_number' => 'TEST-' . time(),
            'vendor' => 'Test Vendor',
            'notes' => 'Testing bypass approval with amount ' . number_format($amount),
            'status' => 'pending' // Default status
        ];
        
        // Check what would happen
        if ($canBypass) {
            $this->warn("\n⚠️  BYPASS WILL BE APPLIED!");
            $this->info("Status will be set to: approved");
            $this->info("No approval workflow will be created");
            $expenseData['status'] = 'approved';
        } else {
            $this->info("\nNormal approval workflow will be applied");
            $this->info("Status will be set to: pending");
            if ($amount > $highAmountThreshold) {
                $this->info("Approvals needed: Finance Manager + Director");
            } else {
                $this->info("Approvals needed: Finance Manager + Project Manager");
            }
        }
        
        if ($this->confirm('Do you want to create this test expense?')) {
            DB::beginTransaction();
            try {
                // Create expense
                $expense = ProjectExpense::create($expenseData);
                $this->info("\n✓ Expense created with ID: {$expense->id}");
                $this->info("  Status: {$expense->status}");
                
                // Check if cashflow entry was created
                sleep(1); // Give observer time to run
                
                $cashflowEntry = CashflowEntry::where('reference_type', 'expense')
                    ->where('reference_id', $expense->id)
                    ->first();
                
                if ($cashflowEntry) {
                    $this->info("\n✓ Cashflow entry created!");
                    $this->info("  Cashflow ID: {$cashflowEntry->id}");
                    $this->info("  Amount: Rp " . number_format($cashflowEntry->amount));
                    $this->info("  Status: {$cashflowEntry->status}");
                    $this->info("  Type: {$cashflowEntry->type}");
                } else {
                    $this->error("\n✗ NO CASHFLOW ENTRY CREATED!");
                    $this->warn("This is the problem - expense approved but no cashflow entry");
                }
                
                // Check approvals
                $approvals = $expense->approvals()->with('approver')->get();
                if ($approvals->count() > 0) {
                    $this->info("\nApprovals created:");
                    foreach ($approvals as $approval) {
                        $this->info("  - Level: {$approval->level}, Status: {$approval->status}");
                    }
                } else {
                    $this->info("\nNo approvals created (bypass applied or error)");
                }
                
                if ($this->confirm('Keep this test data?')) {
                    DB::commit();
                    $this->info("Test data saved.");
                } else {
                    DB::rollback();
                    $this->info("Test data rolled back.");
                }
                
            } catch (\Exception $e) {
                DB::rollback();
                $this->error("Error: " . $e->getMessage());
                Log::error('Test bypass approval failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        $this->info("\n=== Check Laravel Logs ===");
        $this->info("Check storage/logs/laravel.log for detailed logging");
        
        return 0;
    }
}