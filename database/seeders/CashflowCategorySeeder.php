<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashflowCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // ========== INCOME CATEGORIES ==========
            
            // Proyek Group
            [
                'name' => 'Penagihan Proyek',
                'type' => 'income',
                'group' => 'proyek',
                'code' => 'INC_PROJECT_BILLING',
                'description' => 'Pemasukan dari penagihan proyek konstruksi telekomunikasi',
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Penagihan Batch',
                'type' => 'income',
                'group' => 'proyek',
                'code' => 'INC_BATCH_BILLING',
                'description' => 'Pemasukan dari penagihan batch',
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Hutang & Modal Group
            [
                'name' => 'Penerimaan Pinjaman/Hutang',
                'type' => 'income',
                'group' => 'hutang_modal',
                'code' => 'INC_LOAN_RECEIPT',
                'description' => 'Penerimaan modal dari pinjaman bank atau pihak ketiga',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Modal Investor',
                'type' => 'income',
                'group' => 'hutang_modal',
                'code' => 'INC_INVESTOR_CAPITAL',
                'description' => 'Penerimaan modal dari investor atau pemegang saham',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 21,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Modal Awal/Tambahan',
                'type' => 'income',
                'group' => 'hutang_modal',
                'code' => 'INC_INITIAL_CAPITAL',
                'description' => 'Modal awal atau tambahan modal dari pemilik',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 22,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Piutang & Tagihan Group
            [
                'name' => 'Pembayaran Piutang',
                'type' => 'income',
                'group' => 'piutang_tagihan',
                'code' => 'INC_RECEIVABLE_PAYMENT',
                'description' => 'Penerimaan pembayaran dari piutang usaha',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pengembalian Pinjaman',
                'type' => 'income',
                'group' => 'piutang_tagihan',
                'code' => 'INC_LOAN_RETURN',
                'description' => 'Penerimaan dari pengembalian pinjaman yang diberikan',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 31,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Pendapatan Lainnya Group
            [
                'name' => 'Penjualan Aset',
                'type' => 'income',
                'group' => 'pendapatan_lain',
                'code' => 'INC_ASSET_SALE',
                'description' => 'Pendapatan dari penjualan aset tetap atau inventaris',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sewa/Rental',
                'type' => 'income',
                'group' => 'pendapatan_lain',
                'code' => 'INC_RENTAL',
                'description' => 'Pendapatan dari penyewaan aset atau properti',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 41,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Komisi/Fee',
                'type' => 'income',
                'group' => 'pendapatan_lain',
                'code' => 'INC_COMMISSION',
                'description' => 'Pendapatan dari komisi atau fee jasa',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 42,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dividen',
                'type' => 'income',
                'group' => 'pendapatan_lain',
                'code' => 'INC_DIVIDEND',
                'description' => 'Penerimaan dividen dari investasi',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 43,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bunga Bank',
                'type' => 'income',
                'group' => 'pendapatan_lain',
                'code' => 'INC_BANK_INTEREST',
                'description' => 'Pendapatan bunga dari rekening bank',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 44,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bunga Deposito',
                'type' => 'income',
                'group' => 'pendapatan_lain',
                'code' => 'INC_DEPOSIT_INTEREST',
                'description' => 'Pendapatan bunga dari deposito',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cashback/Diskon',
                'type' => 'income',
                'group' => 'pendapatan_lain',
                'code' => 'INC_CASHBACK',
                'description' => 'Penerimaan cashback atau diskon pembelian',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 46,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Klaim Asuransi',
                'type' => 'income',
                'group' => 'pendapatan_lain',
                'code' => 'INC_INSURANCE_CLAIM',
                'description' => 'Penerimaan klaim asuransi',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 47,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hibah/Bantuan',
                'type' => 'income',
                'group' => 'pendapatan_lain',
                'code' => 'INC_GRANT',
                'description' => 'Penerimaan hibah atau bantuan',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 48,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pendapatan Lain-lain',
                'type' => 'income',
                'group' => 'pendapatan_lain',
                'code' => 'INC_OTHER',
                'description' => 'Pendapatan dari sumber lain di luar kategori di atas',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 49,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ========== EXPENSE CATEGORIES ==========
            
            // Proyek Group
            [
                'name' => 'Pengeluaran Proyek',
                'type' => 'expense',
                'group' => 'proyek',
                'code' => 'EXP_PROJECT',
                'description' => 'Pengeluaran untuk keperluan proyek konstruksi',
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Material & Peralatan Proyek',
                'type' => 'expense',
                'group' => 'proyek',
                'code' => 'EXP_PROJECT_MATERIAL',
                'description' => 'Pembelian material dan peralatan untuk proyek',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 101,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Hutang & Pinjaman Group
            [
                'name' => 'Pembayaran Hutang Pokok',
                'type' => 'expense',
                'group' => 'hutang_pinjaman',
                'code' => 'EXP_DEBT_PRINCIPAL',
                'description' => 'Pembayaran cicilan hutang pokok',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 110,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bunga Pinjaman',
                'type' => 'expense',
                'group' => 'hutang_pinjaman',
                'code' => 'EXP_LOAN_INTEREST',
                'description' => 'Pembayaran bunga pinjaman',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 111,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Denda/Penalty',
                'type' => 'expense',
                'group' => 'hutang_pinjaman',
                'code' => 'EXP_PENALTY',
                'description' => 'Pembayaran denda keterlambatan atau penalty',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 112,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pemberian Pinjaman',
                'type' => 'expense',
                'group' => 'hutang_pinjaman',
                'code' => 'EXP_LOAN_GIVEN',
                'description' => 'Pemberian pinjaman kepada pihak lain',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 113,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Operasional Group
            [
                'name' => 'Gaji dan Tunjangan',
                'type' => 'expense',
                'group' => 'operasional',
                'code' => 'EXP_SALARY',
                'description' => 'Pengeluaran untuk gaji karyawan dan tunjangan',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 120,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sewa Kantor/Gudang',
                'type' => 'expense',
                'group' => 'operasional',
                'code' => 'EXP_RENT',
                'description' => 'Pembayaran sewa kantor atau gudang',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 121,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Listrik, Air, Internet',
                'type' => 'expense',
                'group' => 'operasional',
                'code' => 'EXP_UTILITIES',
                'description' => 'Pembayaran utilitas (listrik, air, internet, telepon)',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 122,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Transportasi',
                'type' => 'expense',
                'group' => 'operasional',
                'code' => 'EXP_TRANSPORT',
                'description' => 'Biaya transportasi dan perjalanan dinas',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 123,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Peralatan dan Supplies Kantor',
                'type' => 'expense',
                'group' => 'operasional',
                'code' => 'EXP_OFFICE_SUPPLIES',
                'description' => 'Pembelian peralatan dan supplies kantor',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 124,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Maintenance/Perbaikan',
                'type' => 'expense',
                'group' => 'operasional',
                'code' => 'EXP_MAINTENANCE',
                'description' => 'Biaya perawatan dan perbaikan',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 125,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Asuransi',
                'type' => 'expense',
                'group' => 'operasional',
                'code' => 'EXP_INSURANCE',
                'description' => 'Pembayaran premi asuransi',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 126,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Biaya Operasional Lainnya',
                'type' => 'expense',
                'group' => 'operasional',
                'code' => 'EXP_OPERATIONAL',
                'description' => 'Biaya operasional kantor dan administrasi lainnya',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 129,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Aset & Investasi Group
            [
                'name' => 'Pembelian Aset',
                'type' => 'expense',
                'group' => 'aset_investasi',
                'code' => 'EXP_ASSET_PURCHASE',
                'description' => 'Pembelian aset tetap atau inventaris',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 130,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Investasi',
                'type' => 'expense',
                'group' => 'aset_investasi',
                'code' => 'EXP_INVESTMENT',
                'description' => 'Pengeluaran untuk investasi',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 131,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Pengeluaran Lainnya Group
            [
                'name' => 'Pajak dan Retribusi',
                'type' => 'expense',
                'group' => 'pengeluaran_lain',
                'code' => 'EXP_TAX',
                'description' => 'Pembayaran pajak dan retribusi',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 140,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Marketing/Promosi',
                'type' => 'expense',
                'group' => 'pengeluaran_lain',
                'code' => 'EXP_MARKETING',
                'description' => 'Biaya marketing dan promosi',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 141,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Administrasi Bank',
                'type' => 'expense',
                'group' => 'pengeluaran_lain',
                'code' => 'EXP_BANK_ADMIN',
                'description' => 'Biaya administrasi bank',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 142,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Legal/Notaris',
                'type' => 'expense',
                'group' => 'pengeluaran_lain',
                'code' => 'EXP_LEGAL',
                'description' => 'Biaya legal dan notaris',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 143,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Konsultan',
                'type' => 'expense',
                'group' => 'pengeluaran_lain',
                'code' => 'EXP_CONSULTANT',
                'description' => 'Biaya konsultan',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 144,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Entertainment',
                'type' => 'expense',
                'group' => 'pengeluaran_lain',
                'code' => 'EXP_ENTERTAINMENT',
                'description' => 'Biaya entertainment klien',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 145,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CSR/Donasi',
                'type' => 'expense',
                'group' => 'pengeluaran_lain',
                'code' => 'EXP_DONATION',
                'description' => 'Pengeluaran CSR atau donasi',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 146,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pengeluaran Lain-lain',
                'type' => 'expense',
                'group' => 'pengeluaran_lain',
                'code' => 'EXP_OTHER',
                'description' => 'Pengeluaran lain-lain yang tidak masuk kategori di atas',
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 149,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // WARNING: NEVER USE truncate() IN PRODUCTION!
        // This was the line that deleted all your data:
        // DB::table('cashflow_categories')->truncate();
        
        // SAFE APPROACH: Only insert categories that don't exist yet
        foreach ($categories as $category) {
            // Check if category with this code already exists
            $exists = DB::table('cashflow_categories')
                ->where('code', $category['code'])
                ->exists();
            
            if (!$exists) {
                // Only insert if it doesn't exist
                DB::table('cashflow_categories')->insert($category);
            } else {
                // Optionally update the existing category (except for the code)
                DB::table('cashflow_categories')
                    ->where('code', $category['code'])
                    ->update([
                        'name' => $category['name'],
                        'type' => $category['type'],
                        'group' => $category['group'],
                        'description' => $category['description'],
                        'sort_order' => $category['sort_order'],
                        'updated_at' => now(),
                    ]);
            }
        }
        
        // Update existing categories that don't have group field filled
        DB::table('cashflow_categories')
            ->whereNull('group')
            ->orWhere('group', '')
            ->update([
                'group' => DB::raw("CASE 
                    WHEN type = 'income' THEN 'pendapatan_lain'
                    WHEN type = 'expense' THEN 'pengeluaran_lain'
                    ELSE 'lainnya'
                END"),
                'updated_at' => now(),
            ]);
    }
}
