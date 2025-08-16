<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProjectBilling;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;
use Illuminate\Support\Facades\DB;

class SyncBillingCashflow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:sync-cashflow {--fix : Actually fix the data (otherwise just report)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync cashflow entries with project billings to ensure data consistency';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Billing-Cashflow Sync Tool ===');
        $this->newLine();
        
        $fix = $this->option('fix');
        
        if (!$fix) {
            $this->warn('Running in REPORT mode. Use --fix to actually fix the data.');
        } else {
            $this->info('Running in FIX mode. Data will be updated.');
        }
        
        $this->newLine();
        
        // Get all paid billings
        $paidBillings = ProjectBilling::where('status', 'paid')
            ->with('project')
            ->get();
            
        $this->info("Found {$paidBillings->count()} paid billings to check.");
        $this->newLine();
        
        $issues = [];
        $fixed = 0;
        
        foreach ($paidBillings as $billing) {
            $this->line("Checking billing #{$billing->invoice_number} (ID: {$billing->id})...");
            
            // Find corresponding cashflow entry
            $cashflow = CashflowEntry::where('reference_type', 'billing')
                ->where('reference_id', $billing->id)
                ->where('status', '!=', 'cancelled')
                ->first();
                
            if (!$cashflow) {
                $this->error("  ❌ No cashflow entry found!");
                $issues[] = [
                    'billing_id' => $billing->id,
                    'invoice' => $billing->invoice_number,
                    'issue' => 'missing_cashflow',
                    'billing_amount' => $billing->total_amount
                ];
                
                if ($fix) {
                    $this->createCashflowEntry($billing);
                    $fixed++;
                    $this->info("  ✅ Created new cashflow entry");
                }
            } else {
                // Check if amounts match
                if ($cashflow->amount != $billing->total_amount) {
                    $this->warn("  ⚠️ Amount mismatch!");
                    $this->warn("     Billing: Rp " . number_format($billing->total_amount, 0, ',', '.'));
                    $this->warn("     Cashflow: Rp " . number_format($cashflow->amount, 0, ',', '.'));
                    
                    $issues[] = [
                        'billing_id' => $billing->id,
                        'invoice' => $billing->invoice_number,
                        'issue' => 'amount_mismatch',
                        'billing_amount' => $billing->total_amount,
                        'cashflow_amount' => $cashflow->amount,
                        'difference' => $billing->total_amount - $cashflow->amount
                    ];
                    
                    if ($fix) {
                        $oldAmount = $cashflow->amount;
                        $cashflow->update([
                            'amount' => $billing->total_amount,
                            'notes' => ($cashflow->notes ?? '') . 
                                      " | Synced from billing at " . now()->format('Y-m-d H:i:s') .
                                      " (was Rp " . number_format($oldAmount, 0, ',', '.') . ")"
                        ]);
                        $fixed++;
                        $this->info("  ✅ Updated cashflow amount");
                    }
                } else {
                    $this->info("  ✅ OK - Amount matches");
                }
            }
        }
        
        $this->newLine();
        $this->info('=== Summary ===');
        $this->info("Total billings checked: {$paidBillings->count()}");
        $this->info("Issues found: " . count($issues));
        
        if ($fix) {
            $this->info("Issues fixed: {$fixed}");
        }
        
        if (count($issues) > 0 && !$fix) {
            $this->newLine();
            $this->warn('Run with --fix option to fix these issues.');
        }
        
        // Show detailed issues
        if (count($issues) > 0) {
            $this->newLine();
            $this->table(
                ['Billing ID', 'Invoice', 'Issue', 'Billing Amount', 'Cashflow Amount', 'Difference'],
                collect($issues)->map(function ($issue) {
                    return [
                        $issue['billing_id'],
                        $issue['invoice'],
                        $issue['issue'],
                        'Rp ' . number_format($issue['billing_amount'], 0, ',', '.'),
                        isset($issue['cashflow_amount']) ? 'Rp ' . number_format($issue['cashflow_amount'], 0, ',', '.') : 'N/A',
                        isset($issue['difference']) ? 'Rp ' . number_format($issue['difference'], 0, ',', '.') : 'N/A'
                    ];
                })->toArray()
            );
        }
        
        $this->newLine();
        $this->info('=== Sync Complete ===');
        
        return Command::SUCCESS;
    }
    
    /**
     * Create cashflow entry for billing
     */
    private function createCashflowEntry(ProjectBilling $billing)
    {
        try {
            // Get the system category for project billing
            $category = CashflowCategory::getSystemCategory('INC_PROJECT_BILLING');
            
            if (!$category) {
                $this->error('System category INC_PROJECT_BILLING not found!');
                return;
            }
            
            // Create new cashflow entry
            CashflowEntry::create([
                'reference_type' => 'billing',
                'reference_id' => $billing->id,
                'project_id' => $billing->project_id,
                'category_id' => $category->id,
                'transaction_date' => $billing->billing_date ?? now()->toDateString(),
                'description' => "Pembayaran penagihan proyek: {$billing->project->name}" .
                               ($billing->isTerminPayment() ? " ({$billing->getTerminLabel()})" : ''),
                'amount' => $billing->total_amount,
                'type' => 'income',
                'payment_method' => 'bank_transfer',
                'notes' => "Auto-generated dari penagihan #{$billing->invoice_number} (Synced at " . now()->format('Y-m-d H:i:s') . ")",
                'created_by' => 1,
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => 1
            ]);
            
        } catch (\Exception $e) {
            $this->error("Failed to create cashflow entry: " . $e->getMessage());
        }
    }
}