<?php

namespace App\Http\Controllers;

use App\Models\CashflowCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashflowCategoryController extends Controller
{
    /**
     * Display a listing of the categories
     */
    public function index(Request $request)
    {
        $query = CashflowCategory::ordered();

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->withCount('cashflowEntries')->paginate(20)->withQueryString();

        // Get statistics
        $statistics = CashflowCategory::getStatistics();

        // Get unique groups for filter
        $groups = CashflowCategory::select('group')
            ->distinct()
            ->whereNotNull('group')
            ->pluck('group')
            ->mapWithKeys(function ($group) {
                return [$group => CashflowCategory::$groupLabels[$group] ?? ucfirst(str_replace('_', ' ', $group))];
            })
            ->sort();

        return view('cashflow-categories.index', compact('categories', 'statistics', 'groups'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $groups = CashflowCategory::$groupLabels;
        return view('cashflow-categories.create', compact('groups'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'group' => 'required|string|max:50',
            'code' => 'required|string|max:50|unique:cashflow_categories,code',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_system'] = false;
        $validated['sort_order'] = $validated['sort_order'] ?? 999;

        $category = CashflowCategory::create($validated);

        return redirect()->route('finance.cashflow-categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified category
     */
    public function show(CashflowCategory $cashflowCategory)
    {
        $cashflowCategory->loadCount('cashflowEntries');
        
        // Get recent entries for this category
        $recentEntries = $cashflowCategory->cashflowEntries()
            ->with(['project', 'creator'])
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();

        // Get monthly statistics
        $monthlyStats = $cashflowCategory->cashflowEntries()
            ->selectRaw('YEAR(transaction_date) as year, MONTH(transaction_date) as month, SUM(amount) as total')
            ->where('status', 'confirmed')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('cashflow-categories.show', compact('cashflowCategory', 'recentEntries', 'monthlyStats'));
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(CashflowCategory $cashflowCategory)
    {
        if ($cashflowCategory->is_system) {
            return redirect()->route('finance.cashflow-categories.index')
                ->with('error', 'Kategori sistem tidak dapat diedit.');
        }

        $groups = CashflowCategory::$groupLabels;
        return view('cashflow-categories.edit', compact('cashflowCategory', 'groups'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, CashflowCategory $cashflowCategory)
    {
        if ($cashflowCategory->is_system) {
            return redirect()->route('finance.cashflow-categories.index')
                ->with('error', 'Kategori sistem tidak dapat diedit.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'group' => 'required|string|max:50',
            'code' => 'required|string|max:50|unique:cashflow_categories,code,' . $cashflowCategory->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $cashflowCategory->update($validated);

        return redirect()->route('finance.cashflow-categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified category
     */
    public function destroy(CashflowCategory $cashflowCategory)
    {
        if (!$cashflowCategory->canBeDeleted()) {
            return redirect()->route('finance.cashflow-categories.index')
                ->with('error', 'Kategori ini tidak dapat dihapus karena merupakan kategori sistem atau masih memiliki transaksi.');
        }

        $cashflowCategory->delete();

        return redirect()->route('finance.cashflow-categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    /**
     * Toggle category active status
     */
    public function toggle(CashflowCategory $cashflowCategory)
    {
        if ($cashflowCategory->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori sistem tidak dapat dinonaktifkan.'
            ], 403);
        }

        $cashflowCategory->update([
            'is_active' => !$cashflowCategory->is_active
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $cashflowCategory->is_active,
            'message' => $cashflowCategory->is_active 
                ? 'Kategori berhasil diaktifkan.' 
                : 'Kategori berhasil dinonaktifkan.'
        ]);
    }

    /**
     * Bulk update categories
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:cashflow_categories,id'
        ]);

        $categories = CashflowCategory::whereIn('id', $validated['category_ids'])
            ->where('is_system', false)
            ->get();

        $count = 0;

        DB::transaction(function () use ($categories, $validated, &$count) {
            foreach ($categories as $category) {
                switch ($validated['action']) {
                    case 'activate':
                        $category->update(['is_active' => true]);
                        $count++;
                        break;
                    case 'deactivate':
                        $category->update(['is_active' => false]);
                        $count++;
                        break;
                    case 'delete':
                        if ($category->canBeDeleted()) {
                            $category->delete();
                            $count++;
                        }
                        break;
                }
            }
        });

        $actionText = match($validated['action']) {
            'activate' => 'diaktifkan',
            'deactivate' => 'dinonaktifkan',
            'delete' => 'dihapus',
        };

        return redirect()->route('finance.cashflow-categories.index')
            ->with('success', "{$count} kategori berhasil {$actionText}.");
    }

    /**
     * Export categories to CSV
     */
    public function export()
    {
        $categories = CashflowCategory::ordered()->get();
        
        $filename = 'cashflow-categories-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $output = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($output, [
            'Kode',
            'Nama',
            'Tipe',
            'Group',
            'Deskripsi',
            'Status',
            'Sistem',
            'Urutan',
            'Jumlah Transaksi'
        ]);
        
        // Add data rows
        foreach ($categories as $category) {
            fputcsv($output, [
                $category->code,
                $category->name,
                $category->formatted_type,
                $category->formatted_group,
                $category->description,
                $category->is_active ? 'Aktif' : 'Nonaktif',
                $category->is_system ? 'Ya' : 'Tidak',
                $category->sort_order,
                $category->cashflowEntries()->count()
            ]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Import categories from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        
        // Skip header row
        fgetcsv($handle);
        
        $imported = 0;
        $failed = 0;
        
        DB::transaction(function () use ($handle, &$imported, &$failed) {
            while (($row = fgetcsv($handle)) !== false) {
                try {
                    // Expected format: Code, Name, Type, Group, Description, Active, Sort Order
                    if (count($row) < 7) {
                        $failed++;
                        continue;
                    }
                    
                    // Check if code already exists
                    if (CashflowCategory::where('code', $row[0])->exists()) {
                        $failed++;
                        continue;
                    }
                    
                    CashflowCategory::create([
                        'code' => $row[0],
                        'name' => $row[1],
                        'type' => strtolower($row[2]) === 'pemasukan' ? 'income' : 'expense',
                        'group' => $row[3],
                        'description' => $row[4],
                        'is_active' => strtolower($row[5]) === 'aktif' || $row[5] === '1',
                        'sort_order' => (int) $row[6],
                        'is_system' => false,
                    ]);
                    
                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                }
            }
        });
        
        fclose($handle);
        
        return redirect()->route('finance.cashflow-categories.index')
            ->with('success', "Import selesai: {$imported} kategori berhasil diimport, {$failed} gagal.");
    }

    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Kode (Unik)',
            'Nama Kategori',
            'Tipe (Pemasukan/Pengeluaran)',
            'Group',
            'Deskripsi',
            'Status (Aktif/Nonaktif)',
            'Urutan'
        ];
        
        $sampleData = [
            ['INC_CUSTOM_001', 'Pendapatan Jasa Konsultasi', 'Pemasukan', 'pendapatan_lain', 'Pendapatan dari jasa konsultasi', 'Aktif', '200'],
            ['EXP_CUSTOM_001', 'Biaya Pelatihan', 'Pengeluaran', 'operasional', 'Biaya pelatihan karyawan', 'Aktif', '300']
        ];
        
        $output = fopen('php://temp', 'r+');
        
        fputcsv($output, $headers);
        foreach ($sampleData as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="template-import-kategori-cashflow.csv"');
    }
}