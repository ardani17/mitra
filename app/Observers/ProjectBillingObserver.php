<?php

namespace App\Observers;

use App\Models\ProjectBilling;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;

class ProjectBillingObserver
{
    /**
     * Handle the ProjectBilling "created" event.
     */
    public function created(ProjectBilling $projectBilling): void
    {
        $this->updateProjectBillingStatus($projectBilling);
        
        // FIXED: Handle cashflow creation for billings created with 'paid' status
        if ($projectBilling->status === 'paid') {
            \Log::info("Billing {$projectBilling->id} created with paid status, creating cashflow entry");
            $this->createCashflowEntry($projectBilling);
        }
    }

    /**
     * Handle the ProjectBilling "updated" event.
     */
    public function updated(ProjectBilling $projectBilling): void
    {
        $this->updateProjectBillingStatus($projectBilling);
        $this->handleCashflowIntegration($projectBilling);
    }

    /**
     * Handle the ProjectBilling "deleted" event.
     */
    public function deleted(ProjectBilling $projectBilling): void
    {
        $this->updateProjectBillingStatus($projectBilling);
        $this->cleanupCashflowEntry($projectBilling);
    }

    /**
     * Handle the ProjectBilling "restored" event.
     */
    public function restored(ProjectBilling $projectBilling): void
    {
        $this->updateProjectBillingStatus($projectBilling);
    }

    /**
     * Handle the ProjectBilling "force deleted" event.
     */
    public function forceDeleted(ProjectBilling $projectBilling): void
    {
        $this->updateProjectBillingStatus($projectBilling);
        $this->cleanupCashflowEntry($projectBilling);
    }

    /**
     * Update project billing status ketika ada perubahan pada project billing
     */
    private function updateProjectBillingStatus(ProjectBilling $projectBilling): void
    {
        if ($projectBilling->project) {
            $projectBilling->project->updateBillingStatus();
            
            // Log activity untuk tracking
            $projectBilling->project->activities()->create([
                'user_id' => auth()->id() ?? 1,
                'activity_type' => 'billing_updated',
                'description' => "Status tagihan proyek diperbarui. Total tagihan: " . 
                               number_format($projectBilling->project->total_billed_amount, 0, ',', '.') . 
                               " (" . round($projectBilling->project->billing_percentage, 1) . "%)",
                'activity_date' => now(),
                'metadata' => json_encode([
                    'billing_id' => $projectBilling->id,
                    'billing_amount' => $projectBilling->total_amount,
                    'billing_status' => $projectBilling->project->billing_status,
                    'billing_percentage' => $projectBilling->project->billing_percentage,
                    'invoice_number' => $projectBilling->invoice_number,
                    'sp_number' => $projectBilling->sp_number
                ])
            ]);
        }
    }

    /**
     * Handle cashflow integration when billing status changes
     * FIXED: Using wasChanged() instead of isDirty() for proper detection after save
     */
    private function handleCashflowIntegration(ProjectBilling $projectBilling): void
    {
        // Only create cashflow entry when status changes to 'paid'
        // FIXED: wasChanged() works after save(), isDirty() doesn't
        if ($projectBilling->wasChanged('status') && $projectBilling->status === 'paid') {
            \Log::info("Billing {$projectBilling->id} status changed to paid, creating cashflow entry");
            $this->createCashflowEntry($projectBilling);
        }
        
        // If status changes from 'paid' to something else, cancel the cashflow entry
        if ($projectBilling->wasChanged('status') && $projectBilling->getOriginal('status') === 'paid' && $projectBilling->status !== 'paid') {
            \Log::info("Billing {$projectBilling->id} status changed from paid, cancelling cashflow entry");
            $this->cancelCashflowEntry($projectBilling);
        }
        
        // NEW: Handle amount changes for paid billings
        if ($projectBilling->status === 'paid' && !$projectBilling->wasChanged('status')) {
            // Check if any amount-related field changed
            if ($projectBilling->wasChanged('total_amount') ||
                $projectBilling->wasChanged('nilai_jasa') ||
                $projectBilling->wasChanged('nilai_material') ||
                $projectBilling->wasChanged('ppn_amount') ||
                $projectBilling->wasChanged('subtotal')) {
                
                \Log::info("Billing {$projectBilling->id} amount changed while paid, updating cashflow entry");
                $this->updateCashflowAmount($projectBilling);
            }
        }
    }

    /**
     * Create cashflow entry for paid billing
     * FIXED: Added comprehensive error handling and logging
     */
    private function createCashflowEntry(ProjectBilling $projectBilling): void
    {
        try {
            \Log::info("Starting cashflow entry creation for billing {$projectBilling->id}");

            // Load project relationship jika belum ada
            if (!$projectBilling->relationLoaded('project')) {
                $projectBilling->load('project');
            }

            // Check if cashflow entry already exists
            $existingEntry = CashflowEntry::where('reference_type', 'billing')
                ->where('reference_id', $projectBilling->id)
                ->first();

            if ($existingEntry) {
                \Log::info("Cashflow entry already exists for billing {$projectBilling->id}");
                // Update existing entry if it was cancelled
                if ($existingEntry->status === 'cancelled') {
                    $existingEntry->update([
                        'status' => 'confirmed',
                        'confirmed_at' => now(),
                        'confirmed_by' => auth()->id()
                    ]);
                    \Log::info("Reactivated cancelled cashflow entry {$existingEntry->id}");
                }
                return;
            }

            // Get the system category for project billing
            $category = CashflowCategory::getSystemCategory('INC_PROJECT_BILLING');
            
            if (!$category) {
                \Log::error('System category INC_PROJECT_BILLING not found for cashflow integration');
                return;
            }

            \Log::info("Found cashflow category {$category->id} for billing {$projectBilling->id}");

            // Ensure we have project data
            if (!$projectBilling->project) {
                \Log::error("Project not found for billing {$projectBilling->id}");
                return;
            }

            // Create new cashflow entry
            $cashflowEntry = CashflowEntry::create([
                'reference_type' => 'billing',
                'reference_id' => $projectBilling->id,
                'project_id' => $projectBilling->project_id,
                'category_id' => $category->id,
                'transaction_date' => now()->toDateString(),
                'description' => "Pembayaran penagihan proyek: {$projectBilling->project->name}" .
                               ($projectBilling->isTerminPayment() ? " ({$projectBilling->getTerminLabel()})" : ''),
                'amount' => $projectBilling->total_amount,
                'type' => 'income',
                'payment_method' => 'bank_transfer',
                'notes' => "Auto-generated dari penagihan #{$projectBilling->invoice_number}",
                'created_by' => auth()->id() ?? 1,
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => auth()->id() ?? 1
            ]);

            \Log::info("Successfully created cashflow entry {$cashflowEntry->id} for billing {$projectBilling->id}");

        } catch (\Exception $e) {
            \Log::error("Failed to create cashflow entry for billing {$projectBilling->id}: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }

    /**
     * Cancel cashflow entry when billing is no longer paid
     * FIXED: Added error handling and logging
     */
    private function cancelCashflowEntry(ProjectBilling $projectBilling): void
    {
        try {
            $cashflowEntry = CashflowEntry::where('reference_type', 'billing')
                ->where('reference_id', $projectBilling->id)
                ->first();

            if ($cashflowEntry && $cashflowEntry->status !== 'cancelled') {
                $cashflowEntry->update([
                    'status' => 'cancelled',
                    'notes' => ($cashflowEntry->notes ?? '') . " | Dibatalkan karena status penagihan berubah pada " . now()->format('Y-m-d H:i:s')
                ]);
                \Log::info("Cancelled cashflow entry {$cashflowEntry->id} for billing {$projectBilling->id}");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to cancel cashflow entry for billing {$projectBilling->id}: " . $e->getMessage());
        }
    }
    
    /**
     * Update cashflow amount when billing amount changes
     * NEW: Added to handle amount changes for paid billings
     */
    private function updateCashflowAmount(ProjectBilling $projectBilling): void
    {
        try {
            $cashflowEntry = CashflowEntry::where('reference_type', 'billing')
                ->where('reference_id', $projectBilling->id)
                ->where('status', '!=', 'cancelled')
                ->first();
            
            if ($cashflowEntry) {
                $oldAmount = $cashflowEntry->amount;
                $newAmount = $projectBilling->total_amount;
                
                // Only update if amount actually changed
                if ($oldAmount != $newAmount) {
                    $cashflowEntry->update([
                        'amount' => $newAmount,
                        'description' => "Pembayaran penagihan proyek: {$projectBilling->project->name}" .
                                       ($projectBilling->isTerminPayment() ? " ({$projectBilling->getTerminLabel()})" : ''),
                        'notes' => ($cashflowEntry->notes ?? '') .
                                  " | Amount updated from Rp " . number_format($oldAmount, 0, ',', '.') .
                                  " to Rp " . number_format($newAmount, 0, ',', '.') .
                                  " at " . now()->format('Y-m-d H:i:s')
                    ]);
                    
                    \Log::info("Updated cashflow amount for billing {$projectBilling->id}: from {$oldAmount} to {$newAmount}");
                    
                    // Log activity for tracking
                    if ($projectBilling->project) {
                        $projectBilling->project->activities()->create([
                            'user_id' => auth()->id() ?? 1,
                            'activity_type' => 'cashflow_updated',
                            'description' => "Cashflow amount updated from Rp " . number_format($oldAmount, 0, ',', '.') .
                                           " to Rp " . number_format($newAmount, 0, ',', '.'),
                            'activity_date' => now(),
                            'metadata' => json_encode([
                                'billing_id' => $projectBilling->id,
                                'cashflow_id' => $cashflowEntry->id,
                                'old_amount' => $oldAmount,
                                'new_amount' => $newAmount,
                                'invoice_number' => $projectBilling->invoice_number
                            ])
                        ]);
                    }
                }
            } else {
                // If no cashflow entry exists for a paid billing, create one
                \Log::warning("No cashflow entry found for paid billing {$projectBilling->id}, creating new entry");
                $this->createCashflowEntry($projectBilling);
            }
        } catch (\Exception $e) {
            \Log::error("Failed to update cashflow amount for billing {$projectBilling->id}: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }

    /**
     * Cleanup cashflow entry when billing is deleted
     */
    private function cleanupCashflowEntry(ProjectBilling $projectBilling): void
    {
        try {
            \Log::info("Cleaning up cashflow entry for deleted billing {$projectBilling->id}");
            
            $cashflowEntry = CashflowEntry::where('reference_type', 'billing')
                ->where('reference_id', $projectBilling->id)
                ->first();

            if ($cashflowEntry) {
                // Mark as cancelled instead of deleting to maintain audit trail
                $cashflowEntry->update([
                    'status' => 'cancelled',
                    'notes' => ($cashflowEntry->notes ?? '') . " | Dibatalkan karena tagihan dihapus pada " . now()->format('Y-m-d H:i:s')
                ]);
                
                \Log::info("Successfully cancelled cashflow entry {$cashflowEntry->id} for deleted billing {$projectBilling->id}");
            } else {
                \Log::info("No cashflow entry found for deleted billing {$projectBilling->id}");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to cleanup cashflow entry for deleted billing {$projectBilling->id}: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }
}
