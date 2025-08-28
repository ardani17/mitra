<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProjectExpense;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixMissingCashflowEntries extends Command
{
    protected $signature = 'fix:missing-cashflow 
                            {--expense-id= : Specific expense ID to fix}
                            {--dry-run : Show what would be fixed without making changes}
                            {--all : Fix all approved expenses without cashflow entries}';
    
    protected $description = 'Fix approved expenses that are missing cashflow entries';

    public function handle()
    {
        $this->info("=== Fixing Missing Cashflow Entries ===\n");
        
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No changes will be made\n");
        }
        
        // Get expenses to fix
        $expenses = $this->getExpensesToFix();
        
        if ($expenses->isEmpty()) {
            $this->info("No expenses found that need fixing.");
            return 0;
        }
        
        $this->info("Found {$expenses->count()} expense(s) to process:\n");
        
        // Display table of expenses
        $tableData = $expenses->map(function($expense) {
            return [
                $expense->id,
                $expense->project->name ?? 'N/A',
                number_format($expense->amount),
                $expense->description,
                $expense->status,
                $expense->created_at->format('Y-m-d H:i')
            ];
        })->toArray();
        
        $this->table(
            ['ID', 'Project', 'Amount (Rp)', 'Description', 'Status', 'Created'],
            $tableData
        );
        
        if (!$dryRun && !$this->confirm("\nDo you want to create cashflow entries for these expenses?")) {
            $this->info("Operation cancelled.");
            return 0;
        }
        
        // Process each expense
        $successful = 0;
        $failed = 0;
        
        foreach ($expenses as $expense) {
            $this->info("\nProcessing Expense ID: {$expense->id}");
            
            if ($dryRun) {
                $this->info("  [DRY RUN] Would create cashflow entry for expense {$expense->id}");
                $successful++;
            } else {
                if ($this->createCashflowEntry($expense)) {
                    $successful++;
                    $this->info("  ✓ Cashflow entry created successfully");
                } else {
                    $failed++;
                    $this->error("  ✗ Failed to create cashflow entry");
                }
            }
        }
        
        // Summary
        $this->info("\n=== Summary ===");
        $this->info("Successful: {$successful}");
        if ($failed > 0) {
            $this->error("Failed: {$failed}");
        }
        
        if (!$dryRun && $successful > 0) {
            $this->info("\nCashflow entries have been created. Please verify in the cashflow module.");
        }
        
        return $failed > 0 ? 1 : 0;
    }
    
    private function getExpensesToFix()
    {
        $query = ProjectExpense::with('project')
            ->where('status', 'approved');
        
        // If specific expense ID provided
        if ($expenseId = $this->option('expense-id')) {
            $query->where('id', $expenseId);
        }
        // If --all flag not provided, ask for date range
        elseif (!$this->option('all')) {
            $days = $this->ask('Check expenses from last how many days?', 30);
            $query->where('created_at', '>=', now()->subDays($days));
        }
        
        // Get expenses without cashflow entries
        $expenses = $query->get()->filter(function($expense) {
            $existingEntry = CashflowEntry::where('reference_type', 'expense')
                ->where('reference_id', $expense->id)
                ->whereIn('status', ['confirmed', 'pending'])
                ->exists();
            
            if (!$existingEntry) {
                $this->line("  - Expense {$expense->id}: No cashflow entry found");
                return true;
            }
            return false;
        });
        
        return $expenses;
    }
    
    private function createCashflowEntry(ProjectExpense $expense)
    {
        try {
            DB::beginTransaction();
            
            // Get or create the expense category
            $category = $this->getOrCreateExpenseCategory();
            
            if (!$category) {
                throw new \Exception("Failed to get or create expense category");
            }
            
            // Create cashflow entry
            $cashflowData = [
                'reference_type' => 'expense',
                'reference_id' => $expense->id,
                'project_id' => $expense->project_id,
                'category_id' => $category->id,
                'transaction_date' => $expense->expense_date ? $expense->expense_date->toDateString() : $expense->created_at->toDateString(),
                'description' => "Pengeluaran: {$expense->description}",
                'amount' => $expense->amount,
                'type' => 'expense',
                'payment_method' => 'cash',
                'notes' => $this->generateNotes($expense),
                'created_by' => $expense->user_id,
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => auth()->id() ?? 1
            ];
            
            $cashflowEntry = CashflowEntry::create($cashflowData);
            
            Log::info('Manual cashflow entry created via fix command', [
                'expense_id' => $expense->id,
                'cashflow_entry_id' => $cashflowEntry->id,
                'amount' => $expense->amount,
                'command_user' => auth()->id() ?? 'console'
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create cashflow entry via fix command', [
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error("    Error: " . $e->getMessage());
            return false;
        }
    }
    
    private function getOrCreateExpenseCategory()
    {
        // Try to get existing category
        $category = CashflowCategory::where('code', 'EXP_PROJECT')->first();
        
        if (!$category) {
            // Create the category if it doesn't exist
            $category = CashflowCategory::create([
                'name' => 'Pengeluaran Proyek',
                'type' => 'expense',
                'code' => 'EXP_PROJECT',
                'description' => 'Pengeluaran untuk keperluan proyek konstruksi',
                'is_active' => true,
                'is_system' => true
            ]);
            
            $this->info("  Created missing EXP_PROJECT category");
        }
        
        return $category;
    }
    
    private function generateNotes(ProjectExpense $expense)
    {
        $notes = "Auto-generated dari pengeluaran proyek (FIX via command)";
        
        if ($expense->receipt_number) {
            $notes .= " | No. Kuitansi: {$expense->receipt_number}";
        }
        
        if ($expense->vendor) {
            $notes .= " | Vendor: {$expense->vendor}";
        }
        
        if ($expense->category) {
            $notes .= " | Kategori: {$expense->category}";
        }
        
        $notes .= " | Fixed at: " . now()->format('Y-m-d H:i:s');
        
        return $notes;
    }
}