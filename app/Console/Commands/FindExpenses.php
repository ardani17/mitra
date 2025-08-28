<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProjectExpense;
use App\Models\CashflowEntry;

class FindExpenses extends Command
{
    protected $signature = 'expense:find 
                            {--amount= : Find by exact amount}
                            {--min-amount= : Find by minimum amount}
                            {--max-amount= : Find by maximum amount}
                            {--status= : Filter by status (pending/approved/rejected)}
                            {--project= : Search by project name}
                            {--description= : Search in description}
                            {--vendor= : Search by vendor name}
                            {--date-from= : From date (YYYY-MM-DD)}
                            {--date-to= : To date (YYYY-MM-DD)}
                            {--missing-cashflow : Show only expenses without cashflow}
                            {--user= : Filter by user name or ID}
                            {--limit=20 : Number of results to show}';
    
    protected $description = 'Find expenses based on various criteria';

    public function handle()
    {
        $this->info("=== Searching Expenses ===\n");
        
        $query = ProjectExpense::with(['project', 'user', 'approvals']);
        $hasFilters = false;
        
        // Apply filters
        if ($amount = $this->option('amount')) {
            $query->where('amount', $amount);
            $this->info("Filter: Amount = Rp " . number_format($amount));
            $hasFilters = true;
        }
        
        if ($minAmount = $this->option('min-amount')) {
            $query->where('amount', '>=', $minAmount);
            $this->info("Filter: Amount >= Rp " . number_format($minAmount));
            $hasFilters = true;
        }
        
        if ($maxAmount = $this->option('max-amount')) {
            $query->where('amount', '<=', $maxAmount);
            $this->info("Filter: Amount <= Rp " . number_format($maxAmount));
            $hasFilters = true;
        }
        
        if ($status = $this->option('status')) {
            $query->where('status', $status);
            $this->info("Filter: Status = {$status}");
            $hasFilters = true;
        }
        
        if ($project = $this->option('project')) {
            $query->whereHas('project', function($q) use ($project) {
                $q->where('name', 'like', "%{$project}%")
                  ->orWhere('code', 'like', "%{$project}%");
            });
            $this->info("Filter: Project contains '{$project}'");
            $hasFilters = true;
        }
        
        if ($description = $this->option('description')) {
            $query->where('description', 'like', "%{$description}%");
            $this->info("Filter: Description contains '{$description}'");
            $hasFilters = true;
        }
        
        if ($vendor = $this->option('vendor')) {
            $query->where('vendor', 'like', "%{$vendor}%");
            $this->info("Filter: Vendor contains '{$vendor}'");
            $hasFilters = true;
        }
        
        if ($dateFrom = $this->option('date-from')) {
            $query->where('expense_date', '>=', $dateFrom);
            $this->info("Filter: Date from {$dateFrom}");
            $hasFilters = true;
        }
        
        if ($dateTo = $this->option('date-to')) {
            $query->where('expense_date', '<=', $dateTo);
            $this->info("Filter: Date to {$dateTo}");
            $hasFilters = true;
        }
        
        if ($user = $this->option('user')) {
            if (is_numeric($user)) {
                $query->where('user_id', $user);
            } else {
                $query->whereHas('user', function($q) use ($user) {
                    $q->where('name', 'like', "%{$user}%");
                });
            }
            $this->info("Filter: User = '{$user}'");
            $hasFilters = true;
        }
        
        // Special filter for missing cashflow
        if ($this->option('missing-cashflow')) {
            $expenses = $query->get()->filter(function($expense) {
                return !CashflowEntry::where('reference_type', 'expense')
                    ->where('reference_id', $expense->id)
                    ->whereIn('status', ['confirmed', 'pending'])
                    ->exists();
            });
            $this->warn("Filter: Only showing expenses WITHOUT cashflow entries");
        } else {
            $expenses = $query->limit($this->option('limit'))->orderBy('created_at', 'desc')->get();
        }
        
        if (!$hasFilters) {
            $this->warn("No filters applied. Showing recent expenses:");
            $expenses = ProjectExpense::with(['project', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit($this->option('limit'))
                ->get();
        }
        
        if ($expenses->isEmpty()) {
            $this->info("\nNo expenses found matching your criteria.");
            return 0;
        }
        
        $this->info("\nFound {$expenses->count()} expense(s):\n");
        
        // Prepare table data
        $tableData = [];
        foreach ($expenses as $expense) {
            // Check cashflow status
            $hasCashflow = CashflowEntry::where('reference_type', 'expense')
                ->where('reference_id', $expense->id)
                ->whereIn('status', ['confirmed', 'pending'])
                ->exists();
            
            $cashflowStatus = $hasCashflow ? '✓' : '✗';
            
            $tableData[] = [
                $expense->id,
                $expense->project->name ?? 'N/A',
                'Rp ' . number_format($expense->amount),
                substr($expense->description, 0, 30) . (strlen($expense->description) > 30 ? '...' : ''),
                $expense->status,
                $cashflowStatus,
                $expense->expense_date ? $expense->expense_date->format('Y-m-d') : $expense->created_at->format('Y-m-d'),
                $expense->user->name ?? 'N/A'
            ];
        }
        
        $this->table(
            ['ID', 'Project', 'Amount', 'Description', 'Status', 'CF', 'Date', 'Created By'],
            $tableData
        );
        
        $this->info("\nLegend: CF = Cashflow Entry (✓ = exists, ✗ = missing)\n");
        
        // Show summary for 20jt expense
        if ($this->option('amount') == 20000000 || 
            ($this->option('min-amount') == 20000000 && $this->option('max-amount') == 20000000)) {
            $this->warn("=== Expense 20 Juta Summary ===");
            foreach ($expenses as $expense) {
                if ($expense->amount == 20000000) {
                    $this->info("ID: {$expense->id}");
                    $this->info("Description: {$expense->description}");
                    $this->info("Status: {$expense->status}");
                    
                    $hasCashflow = CashflowEntry::where('reference_type', 'expense')
                        ->where('reference_id', $expense->id)
                        ->exists();
                    
                    if (!$hasCashflow) {
                        $this->error("⚠️  NO CASHFLOW ENTRY - Use this command to fix:");
                        $this->warn("php artisan fix:missing-cashflow --expense-id={$expense->id}");
                    }
                    $this->info("");
                }
            }
        }
        
        // Provide helpful commands
        $this->info("=== Useful Commands ===");
        $this->comment("To fix missing cashflow for a specific expense:");
        $this->comment("  php artisan fix:missing-cashflow --expense-id=[ID]");
        $this->comment("");
        $this->comment("To manage expense status (delete/reject/reset):");
        $this->comment("  php artisan expense:manage [ID]");
        $this->comment("");
        $this->comment("To see more details about a specific expense:");
        $this->comment("  php artisan expense:manage [ID] (then choose 'cancel' to just view)");
        
        return 0;
    }
}