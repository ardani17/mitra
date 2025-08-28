<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $categories = [
            // INCOME CATEGORIES - Project
            ['name' => 'Pendapatan Proyek', 'type' => 'income', 'code' => 'INC_PROJECT', 'description' => 'Pendapatan dari proyek', 'is_active' => true, 'is_system' => true, 'group' => 'project', 'sort_order' => 1],
            ['name' => 'Pembayaran Invoice', 'type' => 'income', 'code' => 'INC_PROJECT_BILLING', 'description' => 'Pembayaran invoice proyek', 'is_active' => true, 'is_system' => true, 'group' => 'project', 'sort_order' => 2],
            
            // INCOME - Operational
            ['name' => 'Penjualan Produk', 'type' => 'income', 'code' => 'INC_SALES', 'description' => 'Pendapatan dari penjualan produk', 'is_active' => true, 'is_system' => false, 'group' => 'operational', 'sort_order' => 10],
            ['name' => 'Jasa Konsultasi', 'type' => 'income', 'code' => 'INC_CONSULTING', 'description' => 'Pendapatan dari jasa konsultasi', 'is_active' => true, 'is_system' => false, 'group' => 'operational', 'sort_order' => 11],
            
            // INCOME - Debt & Receivables
            ['name' => 'Penerimaan Piutang', 'type' => 'income', 'code' => 'INC_RECEIVABLE', 'description' => 'Penerimaan pembayaran piutang', 'is_active' => true, 'is_system' => false, 'group' => 'debt_receivable', 'sort_order' => 20],
            ['name' => 'Pinjaman Diterima', 'type' => 'income', 'code' => 'INC_LOAN_RECEIVED', 'description' => 'Penerimaan pinjaman/hutang', 'is_active' => true, 'is_system' => false, 'group' => 'debt_receivable', 'sort_order' => 21],
            
            // INCOME - Investment
            ['name' => 'Modal Disetor', 'type' => 'income', 'code' => 'INC_CAPITAL', 'description' => 'Setoran modal dari pemilik', 'is_active' => true, 'is_system' => false, 'group' => 'investment', 'sort_order' => 30],
            ['name' => 'Investasi Masuk', 'type' => 'income', 'code' => 'INC_INVESTMENT', 'description' => 'Penerimaan investasi', 'is_active' => true, 'is_system' => false, 'group' => 'investment', 'sort_order' => 31],
            ['name' => 'Dividen/Bunga', 'type' => 'income', 'code' => 'INC_DIVIDEND', 'description' => 'Pendapatan dividen atau bunga', 'is_active' => true, 'is_system' => false, 'group' => 'investment', 'sort_order' => 32],
            
            // INCOME - Other
            ['name' => 'Pendapatan Lain-lain', 'type' => 'income', 'code' => 'INC_OTHER', 'description' => 'Pendapatan lainnya', 'is_active' => true, 'is_system' => false, 'group' => 'other', 'sort_order' => 40],
            ['name' => 'Pengembalian Dana', 'type' => 'income', 'code' => 'INC_REFUND', 'description' => 'Pengembalian dana/refund', 'is_active' => true, 'is_system' => false, 'group' => 'other', 'sort_order' => 41],
            
            // EXPENSE CATEGORIES - Project
            ['name' => 'Pengeluaran Proyek', 'type' => 'expense', 'code' => 'EXP_PROJECT', 'description' => 'Pengeluaran untuk proyek', 'is_active' => true, 'is_system' => true, 'group' => 'project', 'sort_order' => 50],
            ['name' => 'Material Proyek', 'type' => 'expense', 'code' => 'EXP_PROJECT_MATERIAL', 'description' => 'Pembelian material proyek', 'is_active' => true, 'is_system' => false, 'group' => 'project', 'sort_order' => 51],
            ['name' => 'Upah Proyek', 'type' => 'expense', 'code' => 'EXP_PROJECT_LABOR', 'description' => 'Upah tenaga kerja proyek', 'is_active' => true, 'is_system' => false, 'group' => 'project', 'sort_order' => 52],
            ['name' => 'Transportasi Proyek', 'type' => 'expense', 'code' => 'EXP_PROJECT_TRANSPORT', 'description' => 'Biaya transportasi proyek', 'is_active' => true, 'is_system' => false, 'group' => 'project', 'sort_order' => 53],
            
            // EXPENSE - Operational
            ['name' => 'Gaji Karyawan', 'type' => 'expense', 'code' => 'EXP_SALARY', 'description' => 'Pembayaran gaji karyawan', 'is_active' => true, 'is_system' => false, 'group' => 'operational', 'sort_order' => 60],
            ['name' => 'Sewa Kantor', 'type' => 'expense', 'code' => 'EXP_RENT', 'description' => 'Biaya sewa kantor/gedung', 'is_active' => true, 'is_system' => false, 'group' => 'operational', 'sort_order' => 61],
            ['name' => 'Listrik & Air', 'type' => 'expense', 'code' => 'EXP_UTILITIES', 'description' => 'Biaya listrik, air, dan utilitas', 'is_active' => true, 'is_system' => false, 'group' => 'operational', 'sort_order' => 62],
            ['name' => 'Internet & Telepon', 'type' => 'expense', 'code' => 'EXP_COMMUNICATION', 'description' => 'Biaya internet dan telepon', 'is_active' => true, 'is_system' => false, 'group' => 'operational', 'sort_order' => 63],
            ['name' => 'ATK & Supplies', 'type' => 'expense', 'code' => 'EXP_SUPPLIES', 'description' => 'Alat tulis kantor dan supplies', 'is_active' => true, 'is_system' => false, 'group' => 'operational', 'sort_order' => 64],
            ['name' => 'Maintenance', 'type' => 'expense', 'code' => 'EXP_MAINTENANCE', 'description' => 'Biaya maintenance dan perbaikan', 'is_active' => true, 'is_system' => false, 'group' => 'operational', 'sort_order' => 65],
            ['name' => 'Marketing', 'type' => 'expense', 'code' => 'EXP_MARKETING', 'description' => 'Biaya marketing dan promosi', 'is_active' => true, 'is_system' => false, 'group' => 'operational', 'sort_order' => 66],
            ['name' => 'Transportasi Operasional', 'type' => 'expense', 'code' => 'EXP_TRANSPORT', 'description' => 'Biaya transportasi operasional', 'is_active' => true, 'is_system' => false, 'group' => 'operational', 'sort_order' => 67],
            ['name' => 'Konsumsi & Entertainment', 'type' => 'expense', 'code' => 'EXP_ENTERTAINMENT', 'description' => 'Biaya konsumsi dan entertainment', 'is_active' => true, 'is_system' => false, 'group' => 'operational', 'sort_order' => 68],
            
            // EXPENSE - Debt & Receivables
            ['name' => 'Pembayaran Hutang', 'type' => 'expense', 'code' => 'EXP_DEBT_PAYMENT', 'description' => 'Pembayaran hutang/pinjaman', 'is_active' => true, 'is_system' => false, 'group' => 'debt_receivable', 'sort_order' => 70],
            ['name' => 'Bunga Pinjaman', 'type' => 'expense', 'code' => 'EXP_INTEREST', 'description' => 'Pembayaran bunga pinjaman', 'is_active' => true, 'is_system' => false, 'group' => 'debt_receivable', 'sort_order' => 71],
            ['name' => 'Pemberian Pinjaman', 'type' => 'expense', 'code' => 'EXP_LOAN_GIVEN', 'description' => 'Pemberian pinjaman kepada pihak lain', 'is_active' => true, 'is_system' => false, 'group' => 'debt_receivable', 'sort_order' => 72],
            
            // EXPENSE - Tax
            ['name' => 'Pajak PPh', 'type' => 'expense', 'code' => 'EXP_TAX_PPH', 'description' => 'Pembayaran pajak PPh', 'is_active' => true, 'is_system' => false, 'group' => 'tax', 'sort_order' => 80],
            ['name' => 'Pajak PPN', 'type' => 'expense', 'code' => 'EXP_TAX_PPN', 'description' => 'Pembayaran pajak PPN', 'is_active' => true, 'is_system' => false, 'group' => 'tax', 'sort_order' => 81],
            ['name' => 'Pajak Lainnya', 'type' => 'expense', 'code' => 'EXP_TAX_OTHER', 'description' => 'Pembayaran pajak lainnya', 'is_active' => true, 'is_system' => false, 'group' => 'tax', 'sort_order' => 82],
            
            // EXPENSE - Investment
            ['name' => 'Pembelian Aset', 'type' => 'expense', 'code' => 'EXP_ASSET', 'description' => 'Pembelian aset tetap', 'is_active' => true, 'is_system' => false, 'group' => 'investment', 'sort_order' => 90],
            ['name' => 'Investasi Keluar', 'type' => 'expense', 'code' => 'EXP_INVESTMENT', 'description' => 'Pengeluaran untuk investasi', 'is_active' => true, 'is_system' => false, 'group' => 'investment', 'sort_order' => 91],
            ['name' => 'Penarikan Modal', 'type' => 'expense', 'code' => 'EXP_CAPITAL_WITHDRAWAL', 'description' => 'Penarikan modal oleh pemilik', 'is_active' => true, 'is_system' => false, 'group' => 'investment', 'sort_order' => 92],
            
            // EXPENSE - Other
            ['name' => 'Biaya Bank', 'type' => 'expense', 'code' => 'EXP_BANK_FEE', 'description' => 'Biaya administrasi bank', 'is_active' => true, 'is_system' => false, 'group' => 'other', 'sort_order' => 100],
            ['name' => 'Biaya Legal', 'type' => 'expense', 'code' => 'EXP_LEGAL', 'description' => 'Biaya legal dan notaris', 'is_active' => true, 'is_system' => false, 'group' => 'other', 'sort_order' => 101],
            ['name' => 'Biaya Asuransi', 'type' => 'expense', 'code' => 'EXP_INSURANCE', 'description' => 'Pembayaran premi asuransi', 'is_active' => true, 'is_system' => false, 'group' => 'other', 'sort_order' => 102],
            ['name' => 'Pengeluaran Lain-lain', 'type' => 'expense', 'code' => 'EXP_OTHER', 'description' => 'Pengeluaran lainnya', 'is_active' => true, 'is_system' => false, 'group' => 'other', 'sort_order' => 103],
        ];

        // Add timestamps to all categories
        foreach ($categories as &$category) {
            $category['created_at'] = now();
            $category['updated_at'] = now();
        }

        // Insert categories that don't exist
        foreach ($categories as $category) {
            if (!DB::table('cashflow_categories')->where('code', $category['code'])->exists()) {
                DB::table('cashflow_categories')->insert($category);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't delete categories on rollback as they might have transactions
        // This is a safety measure to prevent data loss
    }
};