<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProjectExpense;
use App\Models\CashflowEntry;
use App\Models\ExpenseApproval;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageExpenseStatus extends Command
{
    protected $signature = 'expense:manage 
                            {expense-id : The expense ID to manage}
                            {--action= : Action to perform (delete|reject|reset-to-pending)}
                            {--reason= : Reason for the action}';
    
    protected $description = 'Manage expense status - delete, reject, or reset to pending';

    public function handle()
    {
        $expenseId = $this->argument('expense-id');
        $expense = ProjectExpense::with(['project', 'approvals'])->find($expenseId);
        
        if (!$expense) {
            $this->error("Expense with ID {$expenseId} not found.");
            return 1;
        }
        
        // Display expense details
        $this->info("=== Expense Details ===");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $expense->id],
                ['Project', $expense->project->name ?? 'N/A'],
                ['Amount', 'Rp ' . number_format($expense->amount)],
                ['Description', $expense->description],
                ['Status', $expense->status],
                ['Created', $expense->created_at->format('Y-m-d H:i:s')],
                ['Vendor', $expense->vendor ?? '-'],
                ['Receipt', $expense->receipt_number ?? '-'],
            ]
        );
        
        // Check for existing cashflow entry
        $cashflowEntry = CashflowEntry::where('reference_type', 'expense')
            ->where('reference_id', $expense->id)
            ->first();
        
        if ($cashflowEntry) {
            $this->warn("\n⚠️  This expense has a cashflow entry:");
            $this->table(
                ['Cashflow Field', 'Value'],
                [
                    ['Cashflow ID', $cashflowEntry->id],
                    ['Amount', 'Rp ' . number_format($cashflowEntry->amount)],
                    ['Status', $cashflowEntry->status],
                    ['Type', $cashflowEntry->type],
                    ['Date', $cashflowEntry->transaction_date],
                ]
            );
        } else {
            $this->info("\n✓ No cashflow entry found for this expense.");
        }
        
        // Get action
        $action = $this->option('action');
        if (!$action) {
            $action = $this->choice(
                'What action do you want to perform?',
                [
                    'delete' => 'Delete expense completely',
                    'reject' => 'Change status to rejected',
                    'reset-to-pending' => 'Reset to pending (requires new approval)',
                    'cancel' => 'Cancel operation'
                ],
                'cancel'
            );
        }
        
        if ($action === 'cancel') {
            $this->info("Operation cancelled.");
            return 0;
        }
        
        // Get reason
        $reason = $this->option('reason') ?? $this->ask('Please provide a reason for this action');
        
        // Confirm action
        $this->warn("\n⚠️  WARNING: You are about to {$action} expense ID {$expenseId}");
        if (!$this->confirm('Are you sure you want to continue?')) {
            $this->info("Operation cancelled.");
            return 0;
        }
        
        // Execute action
        try {
            DB::beginTransaction();
            
            switch ($action) {
                case 'delete':
                    $this->deleteExpense($expense, $cashflowEntry, $reason);
                    break;
                    
                case 'reject':
                    $this->rejectExpense($expense, $cashflowEntry, $reason);
                    break;
                    
                case 'reset-to-pending':
                    $this->resetExpenseToPending($expense, $cashflowEntry, $reason);
                    break;
                    
                default:
                    throw new \Exception("Invalid action: {$action}");
            }
            
            DB::commit();
            $this->info("\n✓ Action '{$action}' completed successfully.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\n✗ Failed to execute action: " . $e->getMessage());
            Log::error('ManageExpenseStatus command failed', [
                'expense_id' => $expenseId,
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
    
    private function deleteExpense(ProjectExpense $expense, ?CashflowEntry $cashflowEntry, string $reason)
    {
        // Cancel cashflow entry if exists
        if ($cashflowEntry) {
            $cashflowEntry->update([
                'status' => 'cancelled',
                'notes' => ($cashflowEntry->notes ?? '') . " | Cancelled due to expense deletion: {$reason} | " . now()->format('Y-m-d H:i:s')
            ]);
            $this->info("  - Cashflow entry cancelled");
        }
        
        // Delete approvals
        $expense->approvals()->delete();
        $this->info("  - Approvals deleted");
        
        // Log the deletion
        Log::info('Expense deleted via manage command', [
            'expense_id' => $expense->id,
            'amount' => $expense->amount,
            'reason' => $reason,
            'deleted_by' => auth()->id() ?? 'console',
            'timestamp' => now()
        ]);
        
        // Delete the expense
        $expense->delete();
        $this->info("  - Expense deleted");
    }
    
    private function rejectExpense(ProjectExpense $expense, ?CashflowEntry $cashflowEntry, string $reason)
    {
        // Update expense status
        $expense->update([
            'status' => 'rejected',
            'notes' => ($expense->notes ?? '') . " | Rejected via command: {$reason} | " . now()->format('Y-m-d H:i:s')
        ]);
        $this->info("  - Expense status changed to 'rejected'");
        
        // Cancel cashflow entry if exists
        if ($cashflowEntry && $cashflowEntry->status !== 'cancelled') {
            $cashflowEntry->update([
                'status' => 'cancelled',
                'notes' => ($cashflowEntry->notes ?? '') . " | Cancelled due to expense rejection: {$reason} | " . now()->format('Y-m-d H:i:s')
            ]);
            $this->info("  - Cashflow entry cancelled");
        }
        
        // Update approvals to rejected
        $expense->approvals()->where('status', 'pending')->update([
            'status' => 'rejected',
            'notes' => "Rejected via command: {$reason}",
            'approved_at' => now()
        ]);
        $this->info("  - Approvals marked as rejected");
        
        // Log the rejection
        Log::info('Expense rejected via manage command', [
            'expense_id' => $expense->id,
            'amount' => $expense->amount,
            'reason' => $reason,
            'rejected_by' => auth()->id() ?? 'console',
            'timestamp' => now()
        ]);
    }
    
    private function resetExpenseToPending(ProjectExpense $expense, ?CashflowEntry $cashflowEntry, string $reason)
    {
        // Update expense status
        $expense->update([
            'status' => 'pending',
            'notes' => ($expense->notes ?? '') . " | Reset to pending via command: {$reason} | " . now()->format('Y-m-d H:i:s')
        ]);
        $this->info("  - Expense status changed to 'pending'");
        
        // Cancel cashflow entry if exists
        if ($cashflowEntry && $cashflowEntry->status !== 'cancelled') {
            $cashflowEntry->update([
                'status' => 'cancelled',
                'notes' => ($cashflowEntry->notes ?? '') . " | Cancelled due to expense reset: {$reason} | " . now()->format('Y-m-d H:i:s')
            ]);
            $this->info("  - Cashflow entry cancelled");
        }
        
        // Reset approvals
        $expense->approvals()->delete();
        $this->info("  - Old approvals deleted");
        
        // Create new approval workflow
        $this->createNewApprovalWorkflow($expense);
        $this->info("  - New approval workflow created");
        
        // Log the reset
        Log::info('Expense reset to pending via manage command', [
            'expense_id' => $expense->id,
            'amount' => $expense->amount,
            'reason' => $reason,
            'reset_by' => auth()->id() ?? 'console',
            'timestamp' => now()
        ]);
    }
    
    private function createNewApprovalWorkflow(ProjectExpense $expense)
    {
        $highAmountThreshold = \App\Models\Setting::get('expense_high_amount_threshold', 10000000);
        
        // Finance Manager approval (always required)
        $financeManager = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'finance_manager');
        })->first();
        
        ExpenseApproval::create([
            'expense_id' => $expense->id,
            'approver_id' => $financeManager ? $financeManager->id : 1,
            'level' => 'finance_manager',
            'status' => 'pending'
        ]);
        
        // Director or Project Manager approval based on amount
        if ($expense->amount > $highAmountThreshold) {
            $director = \App\Models\User::whereHas('roles', function($q) {
                $q->where('name', 'direktur');
            })->first();
            
            ExpenseApproval::create([
                'expense_id' => $expense->id,
                'approver_id' => $director ? $director->id : 1,
                'level' => 'direktur',
                'status' => 'pending'
            ]);
            
            $this->info("    → Requires Finance Manager + Director approval (amount > " . number_format($highAmountThreshold) . ")");
        } else {
            $projectManager = \App\Models\User::whereHas('roles', function($q) {
                $q->where('name', 'project_manager');
            })->first();
            
            ExpenseApproval::create([
                'expense_id' => $expense->id,
                'approver_id' => $projectManager ? $projectManager->id : 1,
                'level' => 'project_manager',
                'status' => 'pending'
            ]);
            
            $this->info("    → Requires Finance Manager + Project Manager approval");
        }
    }
}