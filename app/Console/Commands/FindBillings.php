<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProjectBilling;
use App\Models\CashflowEntry;

class FindBillings extends Command
{
    protected $signature = 'billing:find 
                            {--amount= : Find by exact amount}
                            {--min-amount= : Find by minimum amount}
                            {--max-amount= : Find by maximum amount}
                            {--status= : Filter by status (draft/sent/paid/overdue)}
                            {--project= : Search by project name}
                            {--invoice= : Search by invoice number}
                            {--date-from= : From date (YYYY-MM-DD)}
                            {--date-to= : To date (YYYY-MM-DD)}
                            {--missing-cashflow : Show only billings without cashflow}
                            {--termin= : Filter by termin number}
                            {--limit=20 : Number of results to show}';
    
    protected $description = 'Find billings based on various criteria';

    public function handle()
    {
        $this->info("=== Searching Billings ===\n");
        
        $query = ProjectBilling::with(['project', 'paymentSchedule']);
        $hasFilters = false;
        
        // Apply filters
        if ($amount = $this->option('amount')) {
            $query->where('total_amount', $amount);
            $this->info("Filter: Amount = Rp " . number_format($amount));
            $hasFilters = true;
        }
        
        if ($minAmount = $this->option('min-amount')) {
            $query->where('total_amount', '>=', $minAmount);
            $this->info("Filter: Amount >= Rp " . number_format($minAmount));
            $hasFilters = true;
        }
        
        if ($maxAmount = $this->option('max-amount')) {
            $query->where('total_amount', '<=', $maxAmount);
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
        
        if ($invoice = $this->option('invoice')) {
            $query->where('invoice_number', 'like', "%{$invoice}%");
            $this->info("Filter: Invoice contains '{$invoice}'");
            $hasFilters = true;
        }
        
        if ($dateFrom = $this->option('date-from')) {
            $query->where('billing_date', '>=', $dateFrom);
            $this->info("Filter: Date from {$dateFrom}");
            $hasFilters = true;
        }
        
        if ($dateTo = $this->option('date-to')) {
            $query->where('billing_date', '<=', $dateTo);
            $this->info("Filter: Date to {$dateTo}");
            $hasFilters = true;
        }
        
        if ($termin = $this->option('termin')) {
            $query->where('termin_number', $termin);
            $this->info("Filter: Termin number = {$termin}");
            $hasFilters = true;
        }
        
        // Special filter for missing cashflow
        if ($this->option('missing-cashflow')) {
            $billings = $query->get()->filter(function($billing) {
                return !CashflowEntry::where('reference_type', 'billing')
                    ->where('reference_id', $billing->id)
                    ->whereIn('status', ['confirmed', 'pending'])
                    ->exists();
            });
            $this->warn("Filter: Only showing billings WITHOUT cashflow entries");
        } else {
            $billings = $query->limit($this->option('limit'))->orderBy('created_at', 'desc')->get();
        }
        
        if (!$hasFilters) {
            $this->warn("No filters applied. Showing recent billings:");
            $billings = ProjectBilling::with(['project'])
                ->orderBy('created_at', 'desc')
                ->limit($this->option('limit'))
                ->get();
        }
        
        if ($billings->isEmpty()) {
            $this->info("\nNo billings found matching your criteria.");
            return 0;
        }
        
        $this->info("\nFound {$billings->count()} billing(s):\n");
        
        // Prepare table data
        $tableData = [];
        foreach ($billings as $billing) {
            // Check cashflow status
            $hasCashflow = CashflowEntry::where('reference_type', 'billing')
                ->where('reference_id', $billing->id)
                ->whereIn('status', ['confirmed', 'pending'])
                ->exists();
            
            $cashflowStatus = $hasCashflow ? '✓' : '✗';
            
            $tableData[] = [
                $billing->id,
                $billing->project->name ?? 'N/A',
                'Rp ' . number_format($billing->total_amount),
                $billing->invoice_number ?? '-',
                $billing->status,
                $cashflowStatus,
                $billing->billing_date ? $billing->billing_date->format('Y-m-d') : $billing->created_at->format('Y-m-d'),
                $billing->termin_number ? "T{$billing->termin_number}/{$billing->total_termin}" : '-'
            ];
        }
        
        $this->table(
            ['ID', 'Project', 'Amount', 'Invoice', 'Status', 'CF', 'Date', 'Termin'],
            $tableData
        );
        
        $this->info("\nLegend: CF = Cashflow Entry (✓ = exists, ✗ = missing)\n");
        
        // Show paid billings without cashflow
        $paidWithoutCashflow = $billings->filter(function($billing) {
            return $billing->status === 'paid' && 
                   !CashflowEntry::where('reference_type', 'billing')
                    ->where('reference_id', $billing->id)
                    ->whereIn('status', ['confirmed', 'pending'])
                    ->exists();
        });
        
        if ($paidWithoutCashflow->count() > 0) {
            $this->error("=== ⚠️  PAID BILLINGS WITHOUT CASHFLOW ===");
            foreach ($paidWithoutCashflow as $billing) {
                $this->warn("ID: {$billing->id} | Invoice: {$billing->invoice_number} | Amount: Rp " . number_format($billing->total_amount));
                $this->info("  Fix with: php artisan billing:fix-cashflow --billing-id={$billing->id}");
            }
        }
        
        // Provide helpful commands
        $this->info("\n=== Useful Commands ===");
        $this->comment("To fix missing cashflow for a specific billing:");
        $this->comment("  php artisan billing:fix-cashflow --billing-id=[ID]");
        $this->comment("");
        $this->comment("To manage billing status:");
        $this->comment("  php artisan billing:manage [ID]");
        $this->comment("");
        $this->comment("To find all paid billings without cashflow:");
        $this->comment("  php artisan billing:find --status=paid --missing-cashflow");
        
        return 0;
    }
}