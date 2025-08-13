<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProjectExpense;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncExpensesToCashflow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cashflow:sync-expenses 
                            {--dry-run : Run without making changes}
                            {--force : Force sync even if cashflow entries exist}
                            {--from-date= : Sync expenses from this date (Y-m-d)}
                            {--to-date= : Sync expenses to this date (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync approved expenses to cashflow entries that are missing integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting expense to cashflow synchronization...');
        
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $fromDate = $this->option('from-date');
        $toDate = $this->option('to-date');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        // Build query for approved expenses
        $query = ProjectExpense::where('status', 'approved')
            ->with(['project']);
            
        if ($fromDate) {
            $query->where('expense_date', '>=', $fromDate);
            $this->info("Filtering expenses from: {$fromDate}");
        }
        
        if ($toDate) {
            $query->where('expense_date', '<=', $toDate);
            $this->info("Filtering expenses to: {$toDate}");
        }
        
        $approvedExpenses = $query->get();
        
        $this->info("Found {$approvedExpenses->count()} approved expenses to check");
        
        // Get or create expense category
        $category = $this->getOrCreateExpenseCategory();
        if (!$category) {
            $this->error('Failed to get or create EXP_PROJECT category');
            return 1;
        }
        
        $stats = [
            'total_checked' => 0,
            'already_synced' => 0,
            'newly_synced' => 0,
            'force_updated' => 0,
            'errors' => 0
        ];
        
        $progressBar = $this->output->createProgressBar($approvedExpenses->count());
        $progressBar->start();
        
        foreach ($approvedExpenses as $expense) {
            $stats['total_checked']++;
            
            try {
                // Check if cashflow entry already exists
                $existingEntry = CashflowEntry::where('reference_type', 'expense')
                    ->where('reference_id', $expense->id)
                    ->first();
                
                if ($existingEntry && !$force) {
                    $stats['already_synced']++;
                    $progressBar->advance();
                    continue;
                }
                
                if ($existingEntry && $force) {
                    // Update existing entry
                    if (!$dryRun) {
                        $existingEntry->update([
                            'project_id' => $expense->project_id,
                            'category_id' => $category->id,
                            'transaction_date' => $expense->expense_date ? $expense->expense_date->toDateString() : now()->toDateString(),
                            'description' => "Pengeluaran: {$expense->description}",
                            'amount' => $expense->amount,
                            'type' => 'expense',
                            'payment_method' => 'cash',
                            'notes' => $this->generateCashflowNotes($expense) . " | Disinkronisasi ulang pada " . now()->format('Y-m-d H:i:s'),
                            'status' => 'confirmed',
                            'confirmed_at' => now(),
                            'confirmed_by' => 1
                        ]);
                    }
                    $stats['force_updated']++;
                } else {
                    // Create new cashflow entry
                    if (!$dryRun) {
                        CashflowEntry::create([
                            'reference_type' => 'expense',
                            'reference_id' => $expense->id,
                            'project_id' => $expense->project_id,
                            'category_id' => $category->id,
                            'transaction_date' => $expense->expense_date ? $expense->expense_date->toDateString() : now()->toDateString(),
                            'description' => "Pengeluaran: {$expense->description}",
                            'amount' => $expense->amount,
                            'type' => 'expense',
                            'payment_method' => 'cash',
                            'notes' => $this->generateCashflowNotes($expense) . " | Disinkronisasi pada " . now()->format('Y-m-d H:i:s'),
                            'created_by' => $expense->user_id,
                            'status' => 'confirmed',
                            'confirmed_at' => now(),
                            'confirmed_by' => 1
                        ]);
                    }
                    $stats['newly_synced']++;
                }
                
            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error('Error syncing expense to cashflow', [
                    'expense_id' => $expense->id,
                    'error' => $e->getMessage()
                ]);
                
                if ($this->output->isVerbose()) {
                    $this->error("Error syncing expense {$expense->id}: " . $e->getMessage());
                }
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Display results
        $this->info('Synchronization completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Expenses Checked', $stats['total_checked']],
                ['Already Synced', $stats['already_synced']],
                ['Newly Synced', $stats['newly_synced']],
                ['Force Updated', $stats['force_updated']],
                ['Errors', $stats['errors']]
            ]
        );
        
        if ($dryRun) {
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        }
        
        // Log the sync operation
        Log::info('Expense to cashflow sync completed', [
            'dry_run' => $dryRun,
            'force' => $force,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'stats' => $stats
        ]);
        
        return 0;
    }
    
    /**
     * Get or create the expense category
     */
    private function getOrCreateExpenseCategory(): ?CashflowCategory
    {
        // Try to get existing category
        $category = CashflowCategory::where('code', 'EXP_PROJECT')->first();
        
        if (!$category) {
            $this->warn('EXP_PROJECT category not found, creating it...');
            
            try {
                $category = CashflowCategory::create([
                    'name' => 'Pengeluaran Proyek',
                    'type' => 'expense',
                    'code' => 'EXP_PROJECT',
                    'description' => 'Pengeluaran untuk keperluan proyek konstruksi',
                    'is_active' => true,
                    'is_system' => true
                ]);
                
                $this->info('Created EXP_PROJECT category successfully');
            } catch (\Exception $e) {
                $this->error('Failed to create EXP_PROJECT category: ' . $e->getMessage());
                return null;
            }
        }
        
        return $category;
    }
    
    /**
     * Generate comprehensive notes for cashflow entry
     */
    private function generateCashflowNotes(ProjectExpense $expense): string
    {
        $notes = "Auto-generated dari pengeluaran proyek";
        
        if ($expense->receipt_number) {
            $notes .= " (No. Kuitansi: {$expense->receipt_number})";
        }
        
        if ($expense->vendor) {
            $notes .= " | Vendor: {$expense->vendor}";
        }
        
        if ($expense->category) {
            $notes .= " | Kategori: {$expense->category}";
        }
        
        if ($expense->notes) {
            $notes .= " | Catatan: " . substr($expense->notes, 0, 100);
        }
        
        return $notes;
    }
}