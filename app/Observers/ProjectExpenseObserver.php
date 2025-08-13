<?php

namespace App\Observers;

use App\Models\ProjectExpense;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        
        Log::info('ProjectExpense created', [
            'expense_id' => $projectExpense->id,
            'status' => $projectExpense->status,
            'amount' => $projectExpense->amount,
            'cashflow_integration' => $projectExpense->status === 'approved' ? 'triggered' : 'skipped'
        ]);
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
        // Log status change for debugging
        if ($projectExpense->isDirty('status')) {
            Log::info('ProjectExpense status changed', [
                'expense_id' => $projectExpense->id,
                'old_status' => $projectExpense->getOriginal('status'),
                'new_status' => $projectExpense->status,
                'amount' => $projectExpense->amount
            ]);
        }

        // Create cashflow entry when status changes to 'approved'
        if ($projectExpense->isDirty('status') && $projectExpense->status === 'approved') {
            $this->createCashflowEntry($projectExpense);
        }
        
        // Cancel cashflow entry if status changes from 'approved' to something else
        if ($projectExpense->isDirty('status') &&
            $projectExpense->getOriginal('status') === 'approved' &&
            $projectExpense->status !== 'approved') {
            $this->cancelCashflowEntry($projectExpense);
        }

        // Update existing cashflow entry if other fields change but status remains approved
        if (!$projectExpense->isDirty('status') &&
            $projectExpense->status === 'approved' &&
            ($projectExpense->isDirty('amount') || $projectExpense->isDirty('description') || $projectExpense->isDirty('expense_date'))) {
            $this->updateCashflowEntry($projectExpense);
        }
    }

    /**
     * Create cashflow entry for approved expense
     */
    private function createCashflowEntry(ProjectExpense $projectExpense): void
    {
        try {
            DB::beginTransaction();

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
                        'confirmed_by' => $this->getCurrentUserId(),
                        'amount' => $projectExpense->amount,
                        'description' => "Pengeluaran: {$projectExpense->description}",
                        'transaction_date' => $projectExpense->expense_date ? $projectExpense->expense_date->toDateString() : now()->toDateString(),
                    ]);
                    
                    Log::info('Cashflow entry reactivated', [
                        'expense_id' => $projectExpense->id,
                        'cashflow_entry_id' => $existingEntry->id
                    ]);
                }
                DB::commit();
                return;
            }

            // Get or create the system category for project expense
            $category = $this->getOrCreateExpenseCategory();
            
            if (!$category) {
                Log::error('Failed to get or create EXP_PROJECT category for cashflow integration', [
                    'expense_id' => $projectExpense->id
                ]);
                DB::rollBack();
                return;
            }

            // Create new cashflow entry
            $cashflowEntry = CashflowEntry::create([
                'reference_type' => 'expense',
                'reference_id' => $projectExpense->id,
                'project_id' => $projectExpense->project_id,
                'category_id' => $category->id,
                'transaction_date' => $projectExpense->expense_date ? $projectExpense->expense_date->toDateString() : now()->toDateString(),
                'description' => "Pengeluaran: {$projectExpense->description}",
                'amount' => $projectExpense->amount,
                'type' => 'expense',
                'payment_method' => $this->determinePaymentMethod($projectExpense),
                'notes' => $this->generateCashflowNotes($projectExpense),
                'created_by' => $projectExpense->user_id,
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => $this->getCurrentUserId()
            ]);

            Log::info('Cashflow entry created successfully', [
                'expense_id' => $projectExpense->id,
                'cashflow_entry_id' => $cashflowEntry->id,
                'amount' => $projectExpense->amount
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create cashflow entry', [
                'expense_id' => $projectExpense->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Update existing cashflow entry when expense details change
     */
    private function updateCashflowEntry(ProjectExpense $projectExpense): void
    {
        try {
            $cashflowEntry = CashflowEntry::where('reference_type', 'expense')
                ->where('reference_id', $projectExpense->id)
                ->where('status', '!=', 'cancelled')
                ->first();

            if ($cashflowEntry) {
                $cashflowEntry->update([
                    'amount' => $projectExpense->amount,
                    'description' => "Pengeluaran: {$projectExpense->description}",
                    'transaction_date' => $projectExpense->expense_date ? $projectExpense->expense_date->toDateString() : $cashflowEntry->transaction_date,
                    'notes' => $this->generateCashflowNotes($projectExpense) . " | Diperbarui pada " . now()->format('Y-m-d H:i:s')
                ]);

                Log::info('Cashflow entry updated', [
                    'expense_id' => $projectExpense->id,
                    'cashflow_entry_id' => $cashflowEntry->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update cashflow entry', [
                'expense_id' => $projectExpense->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel cashflow entry when expense is no longer approved or deleted
     */
    private function cancelCashflowEntry(ProjectExpense $projectExpense): void
    {
        try {
            $cashflowEntry = CashflowEntry::where('reference_type', 'expense')
                ->where('reference_id', $projectExpense->id)
                ->first();

            if ($cashflowEntry && $cashflowEntry->status !== 'cancelled') {
                $cashflowEntry->update([
                    'status' => 'cancelled',
                    'notes' => ($cashflowEntry->notes ?? '') . " | Dibatalkan karena pengeluaran tidak lagi disetujui atau dihapus pada " . now()->format('Y-m-d H:i:s')
                ]);

                Log::info('Cashflow entry cancelled', [
                    'expense_id' => $projectExpense->id,
                    'cashflow_entry_id' => $cashflowEntry->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to cancel cashflow entry', [
                'expense_id' => $projectExpense->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get or create the expense category
     */
    private function getOrCreateExpenseCategory(): ?CashflowCategory
    {
        // Try to get existing category
        $category = CashflowCategory::getSystemCategory('EXP_PROJECT');
        
        if (!$category) {
            // Create the category if it doesn't exist
            try {
                $category = CashflowCategory::create([
                    'name' => 'Pengeluaran Proyek',
                    'type' => 'expense',
                    'code' => 'EXP_PROJECT',
                    'description' => 'Pengeluaran untuk keperluan proyek konstruksi',
                    'is_active' => true,
                    'is_system' => true
                ]);
                
                Log::info('Created missing EXP_PROJECT category', [
                    'category_id' => $category->id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create EXP_PROJECT category', [
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        }
        
        return $category;
    }

    /**
     * Get current user ID with fallback
     */
    private function getCurrentUserId(): int
    {
        return auth()->id() ?? 1; // Fallback to user ID 1
    }

    /**
     * Determine payment method based on expense data
     */
    private function determinePaymentMethod(ProjectExpense $projectExpense): string
    {
        // You can add logic here to determine payment method based on expense data
        // For now, default to cash
        return 'cash';
    }

    /**
     * Generate comprehensive notes for cashflow entry
     */
    private function generateCashflowNotes(ProjectExpense $projectExpense): string
    {
        $notes = "Auto-generated dari pengeluaran proyek";
        
        if ($projectExpense->receipt_number) {
            $notes .= " (No. Kuitansi: {$projectExpense->receipt_number})";
        }
        
        if ($projectExpense->vendor) {
            $notes .= " | Vendor: {$projectExpense->vendor}";
        }
        
        if ($projectExpense->category) {
            $notes .= " | Kategori: {$projectExpense->category}";
        }
        
        if ($projectExpense->notes) {
            $notes .= " | Catatan: " . substr($projectExpense->notes, 0, 100);
        }
        
        return $notes;
    }
}
