<?php

namespace App\Observers;

use App\Models\ProjectExpense;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;

class ProjectExpenseObserver
{
    /**
     * Handle the ProjectExpense "created" event.
     */
    public function created(ProjectExpense $projectExpense): void
    {
        // Handle cashflow integration when expense is created with 'approved' status
        if ($projectExpense->status === 'approved') {
            $this->createCashflowEntry($projectExpense);
        }
    }

    /**
     * Handle the ProjectExpense "updated" event.
     */
    public function updated(ProjectExpense $projectExpense): void
    {
        $this->handleCashflowIntegration($projectExpense);
    }

    /**
     * Handle the ProjectExpense "deleted" event.
     */
    public function deleted(ProjectExpense $projectExpense): void
    {
        $this->cancelCashflowEntry($projectExpense);
    }

    /**
     * Handle the ProjectExpense "restored" event.
     */
    public function restored(ProjectExpense $projectExpense): void
    {
        $this->handleCashflowIntegration($projectExpense);
    }

    /**
     * Handle the ProjectExpense "force deleted" event.
     */
    public function forceDeleted(ProjectExpense $projectExpense): void
    {
        $this->cancelCashflowEntry($projectExpense);
    }

    /**
     * Handle cashflow integration when expense status changes
     */
    private function handleCashflowIntegration(ProjectExpense $projectExpense): void
    {
        // Only create cashflow entry when status changes to 'approved'
        if ($projectExpense->isDirty('status') && $projectExpense->status === 'approved') {
            $this->createCashflowEntry($projectExpense);
        }
        
        // If status changes from 'approved' to something else, cancel the cashflow entry
        if ($projectExpense->isDirty('status') && $projectExpense->getOriginal('status') === 'approved' && $projectExpense->status !== 'approved') {
            $this->cancelCashflowEntry($projectExpense);
        }
    }

    /**
     * Create cashflow entry for approved expense
     */
    private function createCashflowEntry(ProjectExpense $projectExpense): void
    {
        // Check if cashflow entry already exists
        $existingEntry = CashflowEntry::where('reference_type', 'expense')
            ->where('reference_id', $projectExpense->id)
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

        // Get the system category for project expense
        $category = CashflowCategory::getSystemCategory('EXP_PROJECT');
        
        if (!$category) {
            \Log::warning('System category EXP_PROJECT not found for cashflow integration');
            return;
        }

        // Create new cashflow entry
        CashflowEntry::create([
            'reference_type' => 'expense',
            'reference_id' => $projectExpense->id,
            'project_id' => $projectExpense->project_id,
            'category_id' => $category->id,
            'transaction_date' => $projectExpense->expense_date ? $projectExpense->expense_date->toDateString() : now()->toDateString(),
            'description' => "Pengeluaran: {$projectExpense->description}",
            'amount' => $projectExpense->amount,
            'type' => 'expense',
            'payment_method' => 'cash', // Default, bisa disesuaikan
            'notes' => "Auto-generated dari pengeluaran proyek" .
                      ($projectExpense->receipt_number ? " (No. Kuitansi: {$projectExpense->receipt_number})" : ''),
            'created_by' => $projectExpense->user_id,
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => auth()->id() ?? 1
        ]);
    }

    /**
     * Cancel cashflow entry when expense is no longer approved or deleted
     */
    private function cancelCashflowEntry(ProjectExpense $projectExpense): void
    {
        $cashflowEntry = CashflowEntry::where('reference_type', 'expense')
            ->where('reference_id', $projectExpense->id)
            ->first();

        if ($cashflowEntry && $cashflowEntry->status !== 'cancelled') {
            $cashflowEntry->update([
                'status' => 'cancelled',
                'notes' => ($cashflowEntry->notes ?? '') . " | Dibatalkan karena pengeluaran tidak lagi disetujui atau dihapus pada " . now()->format('Y-m-d H:i:s')
            ]);
        }
    }
}
