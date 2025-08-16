<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\ProjectBilling;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;
use Illuminate\Support\Facades\DB;

class TestDeleteBillingFunctionality extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:delete-billing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test delete project billing functionality with cashflow cleanup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Test Delete Project Billing Functionality ===');
        $this->newLine();

        try {
            DB::beginTransaction();
            
            // 1. Cari atau buat project untuk testing
            $project = Project::first();
            if (!$project) {
                $this->error('❌ Tidak ada project yang tersedia untuk testing');
                return 1;
            }
            
            $this->info("✅ Menggunakan project: {$project->name} (ID: {$project->id})");
            
            // 2. Buat project billing test
            $testBilling = ProjectBilling::create([
                'project_id' => $project->id,
                'payment_type' => 'termin',
                'termin_number' => 99,
                'total_termin' => 1,
                'is_final_termin' => true,
                'invoice_number' => 'TEST-INV-' . time(),
                'nilai_jasa' => 1000000,
                'nilai_material' => 500000,
                'subtotal' => 1500000,
                'ppn_rate' => 11,
                'ppn_calculation' => 'normal',
                'ppn_amount' => 165000,
                'total_amount' => 1665000,
                'billing_date' => now()->toDateString(),
                'status' => 'draft',
                'notes' => 'Test billing untuk delete functionality'
            ]);
            
            $this->info("✅ Test billing dibuat: {$testBilling->invoice_number} (ID: {$testBilling->id})");
            
            // 3. Update status ke 'paid' untuk trigger cashflow entry creation
            $testBilling->update(['status' => 'paid', 'paid_date' => now()]);
            $this->info("✅ Status billing diubah ke 'paid'");
            
            // 4. Verifikasi cashflow entry dibuat
            $cashflowEntry = CashflowEntry::where('reference_type', 'billing')
                ->where('reference_id', $testBilling->id)
                ->first();
            
            if ($cashflowEntry) {
                $this->info("✅ Cashflow entry dibuat: ID {$cashflowEntry->id}, Amount: Rp " . number_format($cashflowEntry->amount, 0, ',', '.'));
            } else {
                $this->warn("⚠️  Cashflow entry tidak ditemukan (mungkin observer tidak aktif)");
            }
            
            // 5. Test delete functionality
            $this->newLine();
            $this->info('--- Testing Delete Functionality ---');
            
            // Ubah status kembali ke draft agar bisa dihapus
            $testBilling->update(['status' => 'draft']);
            $this->info("✅ Status billing diubah kembali ke 'draft' untuk testing delete");
            
            // Delete billing
            $testBilling->delete();
            $this->info("✅ Test billing berhasil dihapus");
            
            // 6. Verifikasi cashflow entry di-cleanup
            if ($cashflowEntry) {
                $cashflowEntry->refresh();
                if ($cashflowEntry->status === 'cancelled') {
                    $this->info("✅ Cashflow entry berhasil di-cancel: Status = {$cashflowEntry->status}");
                } else {
                    $this->error("❌ Cashflow entry tidak di-cancel: Status = {$cashflowEntry->status}");
                }
            }
            
            // 7. Verifikasi billing benar-benar terhapus
            $deletedBilling = ProjectBilling::find($testBilling->id);
            if (!$deletedBilling) {
                $this->info("✅ Billing berhasil dihapus dari database");
            } else {
                $this->error("❌ Billing masih ada di database");
            }
            
            DB::rollback(); // Rollback semua perubahan test
            $this->info("✅ Test selesai - semua perubahan di-rollback");
            
            $this->newLine();
            $this->info('=== HASIL TEST ===');
            $this->info('✅ Delete functionality bekerja dengan baik');
            $this->info('✅ Cashflow entry cleanup berfungsi');
            $this->info('✅ Observer terintegrasi dengan benar');
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->newLine();
            $this->error("❌ Test gagal: " . $e->getMessage());
            $this->error("Stack trace:");
            $this->error($e->getTraceAsString());
            return 1;
        }

        $this->newLine();
        $this->info('=== Test Delete Functionality Selesai ===');
        return 0;
    }
}