<?php

namespace App\Observers;

use App\Models\SalaryRelease;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;

class SalaryReleaseObserver
{
    /**
     * Handle the SalaryRelease "created" event.
     */
    public function created(SalaryRelease $salaryRelease): void
    {
        // No action needed on creation, only when status changes to released
    }

    /**
     * Handle the SalaryRelease "updated" event.
     */
    public function updated(SalaryRelease $salaryRelease): void
    {
        // Check if status changed to 'released' and no cashflow entry exists yet
        if ($salaryRelease->wasChanged('status') && 
            $salaryRelease->status === 'released' && 
            !$salaryRelease->cashflow_entry_id) {
            
            $this->createCashflowEntry($salaryRelease);
        }

        // If status changed from 'released' or 'paid' to 'draft', remove cashflow entry
        // But keep cashflow entry when changing from 'released' to 'paid'
        if ($salaryRelease->wasChanged('status') &&
            in_array($salaryRelease->getOriginal('status'), ['released', 'paid']) &&
            $salaryRelease->status === 'draft' &&
            $salaryRelease->cashflow_entry_id) {
            
            $this->removeCashflowEntry($salaryRelease);
        }
        
        // Update cashflow entry description when status changes to 'paid'
        if ($salaryRelease->wasChanged('status') &&
            $salaryRelease->status === 'paid' &&
            $salaryRelease->cashflow_entry_id) {
            
            $this->updateCashflowEntryForPaid($salaryRelease);
        }
    }

    /**
     * Handle the SalaryRelease "deleted" event.
     */
    public function deleted(SalaryRelease $salaryRelease): void
    {
        // Remove associated cashflow entry if exists
        if ($salaryRelease->cashflow_entry_id) {
            $this->removeCashflowEntry($salaryRelease);
        }
    }

    /**
     * Handle the SalaryRelease "restored" event.
     */
    public function restored(SalaryRelease $salaryRelease): void
    {
        // If restored and was released, recreate cashflow entry
        if ($salaryRelease->status === 'released') {
            $this->createCashflowEntry($salaryRelease);
        }
    }

    /**
     * Handle the SalaryRelease "force deleted" event.
     */
    public function forceDeleted(SalaryRelease $salaryRelease): void
    {
        // Remove associated cashflow entry if exists
        if ($salaryRelease->cashflow_entry_id) {
            $this->removeCashflowEntry($salaryRelease);
        }
    }

    /**
     * Create cashflow entry for salary release
     */
    private function createCashflowEntry(SalaryRelease $salaryRelease): void
    {
        try {
            // Find or create salary expense category
            $category = CashflowCategory::firstOrCreate(
                ['name' => 'Gaji Karyawan'],
                [
                    'code' => 'SALARY_EXPENSE',
                    'type' => 'expense',
                    'description' => 'Pengeluaran untuk gaji karyawan',
                    'is_active' => true
                ]
            );

            // Create cashflow entry
            $cashflowEntry = CashflowEntry::create([
                'transaction_date' => $salaryRelease->released_at ?? now(),
                'description' => "Pembayaran gaji {$salaryRelease->employee->name} periode {$salaryRelease->period_label}",
                'amount' => $salaryRelease->net_amount,
                'type' => 'expense',
                'category_id' => $category->id,
                'reference_type' => 'expense',  // Changed from 'salary_release' to 'expense' to match constraint
                'reference_id' => $salaryRelease->id,
                'notes' => "Rilis gaji dengan kode: {$salaryRelease->release_code}",
                'created_by' => $salaryRelease->released_by ?? auth()->id(),
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => $salaryRelease->released_by ?? auth()->id()
            ]);

            // Update salary release with cashflow entry reference
            $salaryRelease->update(['cashflow_entry_id' => $cashflowEntry->id]);

        } catch (\Exception $e) {
            \Log::error('Failed to create cashflow entry for salary release: ' . $e->getMessage(), [
                'salary_release_id' => $salaryRelease->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove cashflow entry for salary release
     */
    private function removeCashflowEntry(SalaryRelease $salaryRelease): void
    {
        try {
            if ($salaryRelease->cashflowEntry) {
                $salaryRelease->cashflowEntry->delete();
                $salaryRelease->update(['cashflow_entry_id' => null]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to remove cashflow entry for salary release: ' . $e->getMessage(), [
                'salary_release_id' => $salaryRelease->id,
                'cashflow_entry_id' => $salaryRelease->cashflow_entry_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update cashflow entry description when salary is marked as paid
     */
    private function updateCashflowEntryForPaid(SalaryRelease $salaryRelease): void
    {
        try {
            if ($salaryRelease->cashflowEntry) {
                $salaryRelease->cashflowEntry->update([
                    'description' => "Pembayaran gaji {$salaryRelease->employee->name} periode {$salaryRelease->period_label} (DIBAYAR)",
                    'notes' => "Rilis gaji dengan kode: {$salaryRelease->release_code} - Status: DIBAYAR pada " . now()->format('d/m/Y H:i'),
                    'transaction_date' => $salaryRelease->paid_at ?? now()
                ]);
                
                \Log::info('Cashflow entry updated for paid salary release', [
                    'salary_release_id' => $salaryRelease->id,
                    'cashflow_entry_id' => $salaryRelease->cashflow_entry_id,
                    'status' => 'paid'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to update cashflow entry for paid salary release: ' . $e->getMessage(), [
                'salary_release_id' => $salaryRelease->id,
                'cashflow_entry_id' => $salaryRelease->cashflow_entry_id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
