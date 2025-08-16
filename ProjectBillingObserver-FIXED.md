# ProjectBillingObserver - Fixed Version

Berikut adalah kode observer yang sudah diperbaiki untuk mengatasi masalah cashflow integration:

```php
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
     * FIXED: Menggunakan wasChanged() instead of isDirty()
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
}
```

## Perubahan Utama:

1. **Line 82-83**: Ganti `isDirty()` dengan `wasChanged()`
2. **Line 87-88**: Ganti `isDirty()` dengan `wasChanged()`  
3. **Line 95-96**: Tambahkan logging untuk debugging
4. **Line 102-105**: Load project relationship jika belum ada
5. **Line 107-115**: Tambahkan logging untuk existing entry
6. **Line 125**: Tambahkan logging untuk kategori
7. **Line 128-132**: Validasi project data
8. **Line 150**: Tambahkan logging untuk success
9. **Line 152-155**: Comprehensive error handling
10. **Line 163-175**: Error handling untuk cancel entry

## Cara Menggunakan:

1. Backup file observer yang lama
2. Replace isi file `app/Observers/ProjectBillingObserver.php` dengan kode di atas
3. Clear cache: `php artisan optimize:clear`
4. Test dengan membuat billing baru dan ubah status ke 'paid'
5. Cek log di `storage/logs/laravel.log` untuk debugging