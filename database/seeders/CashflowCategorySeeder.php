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
            // Income Categories
            [
                'name' => 'Penagihan Proyek',
                'type' => 'income',
                'code' => 'INC_PROJECT_BILLING',
                'description' => 'Pemasukan dari penagihan proyek konstruksi telekomunikasi',
                'is_active' => true,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Penagihan Batch',
                'type' => 'income',
                'code' => 'INC_BATCH_BILLING',
                'description' => 'Pemasukan dari penagihan batch',
                'is_active' => true,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pendapatan Lain-lain',
                'type' => 'income',
                'code' => 'INC_OTHER',
                'description' => 'Pendapatan dari sumber lain di luar proyek',
                'is_active' => true,
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bunga Bank',
                'type' => 'income',
                'code' => 'INC_BANK_INTEREST',
                'description' => 'Pendapatan bunga dari rekening bank',
                'is_active' => true,
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Expense Categories
            [
                'name' => 'Pengeluaran Proyek',
                'type' => 'expense',
                'code' => 'EXP_PROJECT',
                'description' => 'Pengeluaran untuk keperluan proyek konstruksi',
                'is_active' => true,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Biaya Operasional',
                'type' => 'expense',
                'code' => 'EXP_OPERATIONAL',
                'description' => 'Biaya operasional kantor dan administrasi',
                'is_active' => true,
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gaji dan Tunjangan',
                'type' => 'expense',
                'code' => 'EXP_SALARY',
                'description' => 'Pengeluaran untuk gaji karyawan dan tunjangan',
                'is_active' => true,
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Transportasi',
                'type' => 'expense',
                'code' => 'EXP_TRANSPORT',
                'description' => 'Biaya transportasi dan perjalanan dinas',
                'is_active' => true,
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Peralatan dan Supplies',
                'type' => 'expense',
                'code' => 'EXP_EQUIPMENT',
                'description' => 'Pembelian peralatan dan supplies kantor',
                'is_active' => true,
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pajak dan Retribusi',
                'type' => 'expense',
                'code' => 'EXP_TAX',
                'description' => 'Pembayaran pajak dan retribusi',
                'is_active' => true,
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pengeluaran Lain-lain',
                'type' => 'expense',
                'code' => 'EXP_OTHER',
                'description' => 'Pengeluaran lain-lain yang tidak masuk kategori di atas',
                'is_active' => true,
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('cashflow_categories')->insert($categories);
    }
}
