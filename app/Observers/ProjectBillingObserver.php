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
     */
    private function handleCashflowIntegration(ProjectBilling $projectBilling): void
    {
        // Only create cashflow entry when status changes to 'paid'
        if ($projectBilling->isDirty('status') && $projectBilling->status === 'paid') {
            $this->createCashflowEntry($projectBilling);
        }
        
        // If status changes from 'paid' to something else, cancel the cashflow entry
        if ($projectBilling->isDirty('status') && $projectBilling->getOriginal('status') === 'paid' && $projectBilling->status !== 'paid') {
            $this->cancelCashflowEntry($projectBilling);
        }
    }

    /**
     * Create cashflow entry for paid billing
     */
    private function createCashflowEntry(ProjectBilling $projectBilling): void
    {
        // Check if cashflow entry already exists
        $existingEntry = CashflowEntry::where('reference_type', 'billing')
            ->where('reference_id', $projectBilling->id)
            ->first();

        if ($existingEntry) {
            // Update existing entry if it was cancelled
            if ($existingEntry->status === 'cancelled') {
                $existingEntry->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                    'confirmed_by' => auth()->id()
                ]);
            }
            return;
        }

        // Get the system category for project billing
        $category = CashflowCategory::getSystemCategory('INC_PROJECT_BILLING');
        
        if (!$category) {
            \Log::warning('System category INC_PROJECT_BILLING not found for cashflow integration');
            return;
        }

        // Create new cashflow entry
        CashflowEntry::create([
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
    }

    /**
     * Cancel cashflow entry when billing is no longer paid
     */
    private function cancelCashflowEntry(ProjectBilling $projectBilling): void
    {
        $cashflowEntry = CashflowEntry::where('reference_type', 'billing')
            ->where('reference_id', $projectBilling->id)
            ->first();

        if ($cashflowEntry && $cashflowEntry->status !== 'cancelled') {
            $cashflowEntry->update([
                'status' => 'cancelled',
                'notes' => ($cashflowEntry->notes ?? '') . " | Dibatalkan karena status penagihan berubah pada " . now()->format('Y-m-d H:i:s')
            ]);
        }
    }
}
