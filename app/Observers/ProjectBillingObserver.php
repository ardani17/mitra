<?php

namespace App\Observers;

use App\Models\ProjectBilling;

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
}
