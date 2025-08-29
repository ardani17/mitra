<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProjectExpense;
use App\Models\ProjectBilling;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;
use Illuminate\Support\Facades\DB;

class SyncProjectToCashflow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cashflow:sync-project {--dry-run : Preview changes without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync project expenses and billings to cashflow entries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be saved');
        }

        $this->info('Starting sync process...');
        
        // Check if tables exist
        if (!DB::getSchemaBuilder()->hasTable('cashflow_entries')) {
            $this->error('Table cashflow_entries does not exist! Please run migrations first.');
            $this->info('Run: php artisan migrate');
            return 1;
        }

        if (!DB::getSchemaBuilder()->hasTable('cashflow_categories')) {
            $this->error('Table cashflow_categories does not exist! Please run migrations first.');
            $this->info('Run: php artisan migrate');
            return 1;
        }

        // Check if categories exist
        $expenseCategory = CashflowCategory::where('code', 'EXP_PROJECT')->first();
        $incomeCategory = CashflowCategory::where('code', 'INC_PROJECT')->first();

        if (!$expenseCategory || !$incomeCategory) {
            $this->warn('Required cashflow categories not found. Creating default categories...');
            
            if (!$dryRun) {
                // Create default categories if not exist
                if (!$expenseCategory) {
                    $expenseCategory = CashflowCategory::create([
                        'name' => 'Pengeluaran Proyek',
                        'type' => 'expense',
                        'code' => 'EXP_PROJECT',
                        'description' => 'Pengeluaran untuk proyek',
                        'is_active' => true,
                        'is_system' => true,
                        'group' => 'project',
                        'sort_order' => 1
                    ]);
                    $this->info('Created expense category: EXP_PROJECT');
                }

                if (!$incomeCategory) {
                    $incomeCategory = CashflowCategory::create([
                        'name' => 'Pendapatan Proyek',
                        'type' => 'income',
                        'code' => 'INC_PROJECT',
                        'description' => 'Pendapatan dari proyek',
                        'is_active' => true,
                        'is_system' => true,
                        'group' => 'project',
                        'sort_order' => 1
                    ]);
                    $this->info('Created income category: INC_PROJECT');
                }
            }
        }

        DB::beginTransaction();
        try {
            // Sync Expenses
            $this->info("\n📊 Syncing Project Expenses...");
            $expenseCount = $this->syncExpenses($expenseCategory, $dryRun);
            
            // Sync Billings
            $this->info("\n💰 Syncing Project Billings...");
            $billingCount = $this->syncBillings($incomeCategory, $dryRun);
            
            if ($dryRun) {
                DB::rollback();
                $this->info("\n✅ DRY RUN COMPLETED");
                $this->info("Would sync: {$expenseCount} expenses, {$billingCount} billings");
            } else {
                DB::commit();
                $this->info("\n✅ SYNC COMPLETED SUCCESSFULLY!");
                $this->info("Synced: {$expenseCount} expenses, {$billingCount} billings");
            }
            
            // Show current totals
            $this->showTotals();
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('❌ Sync failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Sync expenses to cashflow entries
     */
    private function syncExpenses($category, $dryRun)
    {
        // Get expenses that are not yet in cashflow_entries
        $expenses = ProjectExpense::where('status', 'approved')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('cashflow_entries')
                    ->whereColumn('cashflow_entries.reference_id', 'project_expenses.id')
                    ->where('cashflow_entries.reference_type', 'expense');
            })
            ->get();

        $this->info("Found {$expenses->count()} expenses to sync");

        if ($expenses->isEmpty()) {
            $this->info("No new expenses to sync");
            return 0;
        }

        $bar = $this->output->createProgressBar($expenses->count());
        $bar->start();

        foreach ($expenses as $expense) {
            if (!$dryRun) {
                CashflowEntry::create([
                    'reference_type' => 'expense',
                    'reference_id' => $expense->id,
                    'project_id' => $expense->project_id,
                    'category_id' => $category->id,
                    'transaction_date' => $expense->expense_date ?? $expense->created_at->format('Y-m-d'),
                    'description' => $expense->description ?? 'Pengeluaran proyek',
                    'amount' => $expense->amount,
                    'type' => 'expense',
                    'payment_method' => 'bank_transfer',
                    'created_by' => $expense->user_id ?? 1,
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                    'confirmed_by' => 1
                ]);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        
        return $expenses->count();
    }

    /**
     * Sync billings to cashflow entries
     */
    private function syncBillings($category, $dryRun)
    {
        // Get billings that are not yet in cashflow_entries
        $billings = ProjectBilling::whereIn('status', ['paid', 'partial'])
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('cashflow_entries')
                    ->whereColumn('cashflow_entries.reference_id', 'project_billings.id')
                    ->where('cashflow_entries.reference_type', 'billing');
            })
            ->get();

        $this->info("Found {$billings->count()} billings to sync");

        if ($billings->isEmpty()) {
            $this->info("No new billings to sync");
            return 0;
        }

        $bar = $this->output->createProgressBar($billings->count());
        $bar->start();

        foreach ($billings as $billing) {
            if (!$dryRun) {
                // Get the amount to record (paid amount or total amount)
                $amount = $billing->paid_amount ?? $billing->total_amount;
                
                CashflowEntry::create([
                    'reference_type' => 'billing',
                    'reference_id' => $billing->id,
                    'project_id' => $billing->project_id,
                    'category_id' => $category->id,
                    'transaction_date' => $billing->paid_date ? $billing->paid_date->format('Y-m-d') :
                                         ($billing->billing_date ? $billing->billing_date->format('Y-m-d') :
                                          $billing->created_at->format('Y-m-d')),
                    'description' => "Pembayaran invoice {$billing->invoice_number}",
                    'amount' => $amount,
                    'type' => 'income',
                    'payment_method' => 'bank_transfer',
                    'created_by' => 1,
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                    'confirmed_by' => 1
                ]);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        
        return $billings->count();
    }

    /**
     * Show current totals
     */
    private function showTotals()
    {
        $this->newLine();
        $this->info('📈 CURRENT TOTALS:');
        
        // Project data
        $totalExpenses = ProjectExpense::where('status', 'approved')->sum('amount');
        $totalBillings = ProjectBilling::whereIn('status', ['paid', 'partial'])->sum('total_amount');
        
        $this->table(
            ['Source', 'Total Income', 'Total Expense', 'Balance'],
            [
                [
                    'Project Data',
                    'Rp ' . number_format($totalBillings, 0, ',', '.'),
                    'Rp ' . number_format($totalExpenses, 0, ',', '.'),
                    'Rp ' . number_format($totalBillings - $totalExpenses, 0, ',', '.')
                ]
            ]
        );

        // Cashflow data
        if (DB::getSchemaBuilder()->hasTable('cashflow_entries')) {
            $cashflowIncome = CashflowEntry::where('type', 'income')
                ->where('status', 'confirmed')
                ->sum('amount');
            $cashflowExpense = CashflowEntry::where('type', 'expense')
                ->where('status', 'confirmed')
                ->sum('amount');
            
            $this->table(
                ['Source', 'Total Income', 'Total Expense', 'Balance'],
                [
                    [
                        'Cashflow Data',
                        'Rp ' . number_format($cashflowIncome, 0, ',', '.'),
                        'Rp ' . number_format($cashflowExpense, 0, ',', '.'),
                        'Rp ' . number_format($cashflowIncome - $cashflowExpense, 0, ',', '.')
                    ]
                ]
            );
        }
    }
}