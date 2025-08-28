<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProjectBilling;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixBillingCashflow extends Command
{
    protected $signature = 'billing:fix-cashflow 
                            {--billing-id= : Specific billing ID to fix}
                            {--dry-run : Show what would be fixed without making changes}
                            {--all : Fix all paid billings without cashflow entries}';
    
    protected $description = 'Fix paid billings that are missing cashflow entries';

    public function handle()
    {
        $this->info("=== Fixing Missing Cashflow Entries for Billings ===\n");
        
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No changes will be made\n");
        }
        
        // Get billings to fix
        $billings = $this->getBillingsToFix();
        
        if ($billings->isEmpty()) {
            $this->info("No billings found that need fixing.");
            return 0;
        }
        
        $this->info("Found {$billings->count()} billing(s) to process:\n");
        
        // Display table of billings
        $tableData = $billings->map(function($billing) {
            return [
                $billing->id,
                $billing->project->name ?? 'N/A',
                number_format($billing->total_amount),
                $billing->invoice_number ?? '-',
                $billing->status,
                $billing->billing_date ? $billing->billing_date->format('Y-m-d') : $billing->created_at->format('Y-m-d'),
                $billing->termin_number ? "Termin {$billing->termin_number}/{$billing->total_termin}" : '-'
            ];
        })->toArray();
        
        $this->table(
            ['ID', 'Project', 'Amount (Rp)', 'Invoice', 'Status', 'Date', 'Termin'],
            $tableData
        );
        
        if (!$dryRun && !$this->confirm("\nDo you want to create cashflow entries for these billings?")) {
            $this->info("Operation cancelled.");
            return 0;
        }
        
        // Process each billing
        $successful = 0;
        $failed = 0;
        
        foreach ($billings as $billing) {
            $this->info("\nProcessing Billing ID: {$billing->id} (Invoice: {$billing->invoice_number})");
            
            if ($dryRun) {
                $this->info("  [DRY RUN] Would create cashflow entry for billing {$billing->id}");
                $successful++;
            } else {
                if ($this->createCashflowEntry($billing)) {
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
    
    private function getBillingsToFix()
    {
        $query = ProjectBilling::with('project')
            ->where('status', 'paid');
        
        // If specific billing ID provided
        if ($billingId = $this->option('billing-id')) {
            $query->where('id', $billingId);
        }
        // If --all flag not provided, ask for date range
        elseif (!$this->option('all')) {
            $days = $this->ask('Check billings from last how many days?', 30);
            $query->where('created_at', '>=', now()->subDays($days));
        }
        
        // Get billings without cashflow entries
        $billings = $query->get()->filter(function($billing) {
            $existingEntry = CashflowEntry::where('reference_type', 'billing')
                ->where('reference_id', $billing->id)
                ->whereIn('status', ['confirmed', 'pending'])
                ->exists();
            
            if (!$existingEntry) {
                $this->line("  - Billing {$billing->id} (Invoice: {$billing->invoice_number}): No cashflow entry found");
                return true;
            }
            return false;
        });
        
        return $billings;
    }
    
    private function createCashflowEntry(ProjectBilling $billing)
    {
        try {
            DB::beginTransaction();
            
            // Get or create the income category for project billing
            $category = $this->getOrCreateBillingCategory();
            
            if (!$category) {
                throw new \Exception("Failed to get or create billing income category");
            }
            
            // Ensure we have project data
            if (!$billing->project) {
                throw new \Exception("Project not found for billing {$billing->id}");
            }
            
            // Create cashflow entry
            $cashflowData = [
                'reference_type' => 'billing',
                'reference_id' => $billing->id,
                'project_id' => $billing->project_id,
                'category_id' => $category->id,
                'transaction_date' => $billing->paid_date ? $billing->paid_date->toDateString() : 
                                     ($billing->billing_date ? $billing->billing_date->toDateString() : 
                                      $billing->created_at->toDateString()),
                'description' => "Pembayaran penagihan proyek: {$billing->project->name}" .
                                ($billing->isTerminPayment() ? " ({$billing->getTerminLabel()})" : ''),
                'amount' => $billing->total_amount,
                'type' => 'income',
                'payment_method' => 'bank_transfer',
                'notes' => $this->generateNotes($billing),
                'created_by' => auth()->id() ?? 1,
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => auth()->id() ?? 1
            ];
            
            $cashflowEntry = CashflowEntry::create($cashflowData);
            
            Log::info('Manual cashflow entry created for billing via fix command', [
                'billing_id' => $billing->id,
                'cashflow_entry_id' => $cashflowEntry->id,
                'amount' => $billing->total_amount,
                'invoice_number' => $billing->invoice_number,
                'command_user' => auth()->id() ?? 'console'
            ]);
            
            // Update project activity log
            if ($billing->project) {
                $billing->project->activities()->create([
                    'user_id' => auth()->id() ?? 1,
                    'activity_type' => 'cashflow_fixed',
                    'description' => "Cashflow entry created for paid billing #{$billing->invoice_number} (FIX via command)",
                    'activity_date' => now(),
                    'metadata' => json_encode([
                        'billing_id' => $billing->id,
                        'cashflow_id' => $cashflowEntry->id,
                        'amount' => $billing->total_amount,
                        'fixed_by' => 'command'
                    ])
                ]);
            }
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create cashflow entry for billing via fix command', [
                'billing_id' => $billing->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error("    Error: " . $e->getMessage());
            return false;
        }
    }
    
    private function getOrCreateBillingCategory()
    {
        // Try to get existing category
        $category = CashflowCategory::where('code', 'INC_PROJECT_BILLING')
            ->orWhere('code', 'INC_PROJECT')
            ->first();
        
        if (!$category) {
            // Create the category if it doesn't exist
            $category = CashflowCategory::create([
                'name' => 'Pendapatan Proyek',
                'type' => 'income',
                'code' => 'INC_PROJECT_BILLING',
                'description' => 'Pendapatan dari penagihan proyek',
                'is_active' => true,
                'is_system' => true
            ]);
            
            $this->info("  Created missing INC_PROJECT_BILLING category");
        }
        
        return $category;
    }
    
    private function generateNotes(ProjectBilling $billing)
    {
        $notes = "Auto-generated dari penagihan proyek (FIX via command)";
        
        if ($billing->invoice_number) {
            $notes .= " | Invoice: {$billing->invoice_number}";
        }
        
        if ($billing->sp_number) {
            $notes .= " | SP: {$billing->sp_number}";
        }
        
        if ($billing->termin_number) {
            $notes .= " | {$billing->getTerminLabel()}";
        }
        
        if ($billing->paid_date) {
            $notes .= " | Paid date: " . $billing->paid_date->format('Y-m-d');
        }
        
        $notes .= " | Fixed at: " . now()->format('Y-m-d H:i:s');
        
        return $notes;
    }
}