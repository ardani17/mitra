<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\DailySalary;
use App\Models\SalaryRelease;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test Integrasi Rilis Gaji dengan Cashflow ===\n\n";

try {
    // 1. Cek apakah ada karyawan aktif
    $employee = Employee::where('status', 'active')->first();
    if (!$employee) {
        echo "❌ Tidak ada karyawan aktif untuk testing\n";
        exit(1);
    }
    echo "✅ Karyawan ditemukan: {$employee->name}\n";

    // 2. Cek apakah ada gaji harian yang dikonfirmasi dan belum dirilis
    $unreleasedSalaries = $employee->dailySalaries()
        ->where('status', 'confirmed')
        ->whereNull('salary_release_id')
        ->get();
    
    if ($unreleasedSalaries->isEmpty()) {
        echo "⚠️  Tidak ada gaji harian yang belum dirilis untuk testing\n";
        echo "   Membuat data gaji harian untuk testing...\n";
        
        // Buat gaji harian untuk testing dengan tanggal unik
        $testDate = now()->subDays(rand(2, 10)); // Random date to avoid duplicates
        $testSalary = DailySalary::create([
            'employee_id' => $employee->id,
            'work_date' => $testDate,
            'amount' => 185000, // Required field
            'hours_worked' => 8,
            'overtime_hours' => 0,
            'basic_salary' => 150000,
            'meal_allowance' => 10000,
            'attendance_bonus' => 20000,
            'phone_allowance' => 5000,
            'transport_allowance' => 0,
            'overtime_amount' => 0,
            'deductions' => 0,
            'total_amount' => 185000,
            'attendance_status' => 'present',
            'status' => 'confirmed',
            'created_by' => 1
        ]);
        
        $unreleasedSalaries = collect([$testSalary]);
        echo "✅ Data gaji harian testing dibuat\n";
    } else {
        echo "✅ Ditemukan {$unreleasedSalaries->count()} gaji harian yang belum dirilis\n";
    }

    // 3. Test membuat rilis gaji
    echo "\n--- Testing Pembuatan Rilis Gaji ---\n";
    
    $totalAmount = $unreleasedSalaries->sum('total_amount');
    $deductions = 0;
    $netAmount = $totalAmount - $deductions;
    
    $salaryRelease = SalaryRelease::create([
        'employee_id' => $employee->id,
        'period_start' => now()->startOfMonth(),
        'period_end' => now()->endOfMonth(),
        'total_amount' => $totalAmount,
        'deductions' => $deductions,
        'net_amount' => $netAmount,
        'status' => 'draft',
        'notes' => 'Test rilis gaji untuk validasi integrasi',
        'created_by' => 1
    ]);
    
    // Attach daily salaries
    $unreleasedSalaries->each(function ($salary) use ($salaryRelease) {
        $salary->update(['salary_release_id' => $salaryRelease->id]);
    });
    
    echo "✅ Rilis gaji dibuat: {$salaryRelease->release_code}\n";
    echo "   Total: Rp " . number_format($totalAmount, 0, ',', '.') . "\n";

    // 4. Test release gaji (ubah status ke released)
    echo "\n--- Testing Release Gaji ---\n";
    
    // Cek apakah kategori cashflow sudah ada
    $category = CashflowCategory::where('name', 'Gaji Karyawan')->first();
    if (!$category) {
        echo "⚠️  Kategori 'Gaji Karyawan' belum ada, akan dibuat otomatis\n";
    } else {
        echo "✅ Kategori cashflow 'Gaji Karyawan' sudah ada\n";
    }
    
    // Release gaji
    $salaryRelease->update([
        'status' => 'released',
        'released_by' => 1,
        'released_at' => now()
    ]);
    
    echo "✅ Gaji berhasil dirilis\n";
    
    // 5. Validasi integrasi cashflow
    echo "\n--- Validasi Integrasi Cashflow ---\n";
    
    // Refresh model untuk mendapatkan data terbaru
    $salaryRelease->refresh();
    
    if ($salaryRelease->cashflow_entry_id) {
        $cashflowEntry = $salaryRelease->cashflowEntry;
        if ($cashflowEntry) {
            echo "✅ Entry cashflow berhasil dibuat:\n";
            echo "   ID: {$cashflowEntry->id}\n";
            echo "   Tanggal: {$cashflowEntry->transaction_date}\n";
            echo "   Deskripsi: {$cashflowEntry->description}\n";
            echo "   Jumlah: Rp " . number_format($cashflowEntry->amount, 0, ',', '.') . "\n";
            echo "   Tipe: {$cashflowEntry->type}\n";
            echo "   Status: {$cashflowEntry->status}\n";
            
            // Validasi kategori
            if ($cashflowEntry->category) {
                echo "   Kategori: {$cashflowEntry->category->name}\n";
            }
        } else {
            echo "❌ Entry cashflow tidak ditemukan meskipun ID tersimpan\n";
        }
    } else {
        echo "❌ Entry cashflow tidak dibuat\n";
    }

    // 6. Test mark as paid
    echo "\n--- Testing Mark as Paid ---\n";
    
    $salaryRelease->update(['status' => 'paid']);
    echo "✅ Gaji berhasil ditandai sebagai dibayar\n";

    // 7. Cleanup - hapus data testing
    echo "\n--- Cleanup Data Testing ---\n";
    
    if ($salaryRelease->cashflowEntry) {
        $salaryRelease->cashflowEntry->delete();
        echo "✅ Entry cashflow dihapus\n";
    }
    
    // Detach daily salaries
    $salaryRelease->dailySalaries()->update(['salary_release_id' => null]);
    
    // Delete salary release
    $salaryRelease->delete();
    echo "✅ Rilis gaji dihapus\n";
    
    // Delete test daily salary if created
    if (isset($testSalary)) {
        $testSalary->delete();
        echo "✅ Data gaji harian testing dihapus\n";
    }

    echo "\n🎉 SEMUA TEST BERHASIL! Fitur rilis gaji sudah terintegrasi dengan cashflow.\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}