<?php

namespace App\Http\Controllers;

use App\Models\CashflowEntry;
use App\Models\CashflowCategory;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashflowController extends Controller
{
    // Middleware will be applied in routes

    /**
     * Display a listing of cashflow entries
     */
    public function index(Request $request)
    {
        $query = CashflowEntry::with(['project', 'category', 'creator'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $entries = $query->paginate(20)->withQueryString();

        // Get filter options
        $categories = CashflowCategory::active()->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        // Get summary for current filters
        $summaryQuery = CashflowEntry::confirmed();
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $summaryQuery->dateRange($request->start_date, $request->end_date);
        }

        $summary = [
            'total_income' => $summaryQuery->clone()->income()->sum('amount'),
            'total_expense' => $summaryQuery->clone()->expense()->sum('amount'),
        ];
        $summary['balance'] = $summary['total_income'] - $summary['total_expense'];

        return view('cashflow.index', compact('entries', 'categories', 'projects', 'summary'));
    }

    /**
     * Show income entries only
     */
    public function income(Request $request)
    {
        $request->merge(['type' => 'income']);
        return $this->index($request);
    }

    /**
     * Show expense entries only
     */
    public function expense(Request $request)
    {
        $request->merge(['type' => 'expense']);
        return $this->index($request);
    }

    /**
     * Show the form for creating a new cashflow entry
     */
    public function create()
    {
        $categories = CashflowCategory::active()->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        
        return view('cashflow.create', compact('categories', 'projects'));
    }

    /**
     * Store a newly created cashflow entry
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'category_id' => 'required|exists:cashflow_categories,id',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense',
            'payment_method' => 'nullable|string|max:100',
            'account_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['reference_type'] = 'manual';
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'confirmed';
        $validated['confirmed_at'] = now();
        $validated['confirmed_by'] = auth()->id();

        $entry = CashflowEntry::create($validated);

        return redirect()->route('finance.cashflow.index')
            ->with('success', 'Entry cashflow berhasil ditambahkan.');
    }

    /**
     * Display the specified cashflow entry
     */
    public function show(CashflowEntry $cashflow)
    {
        $cashflow->load(['project', 'category', 'creator', 'confirmer']);
        
        return view('cashflow.show', compact('cashflow'));
    }

    /**
     * Show the form for editing the specified cashflow entry
     */
    public function edit(CashflowEntry $cashflow)
    {
        if (!$cashflow->canBeEdited()) {
            return redirect()->route('finance.cashflow.index')
                ->with('error', 'Entry ini tidak dapat diedit.');
        }

        $categories = CashflowCategory::active()->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        
        return view('cashflow.edit', compact('cashflow', 'categories', 'projects'));
    }

    /**
     * Update the specified cashflow entry
     */
    public function update(Request $request, CashflowEntry $cashflow)
    {
        if (!$cashflow->canBeEdited()) {
            return redirect()->route('finance.cashflow.index')
                ->with('error', 'Entry ini tidak dapat diedit.');
        }

        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'category_id' => 'required|exists:cashflow_categories,id',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense',
            'payment_method' => 'nullable|string|max:100',
            'account_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
        ]);

        $cashflow->update($validated);

        return redirect()->route('finance.cashflow.index')
            ->with('success', 'Entry cashflow berhasil diperbarui.');
    }

    /**
     * Remove the specified cashflow entry
     */
    public function destroy(CashflowEntry $cashflow)
    {
        if (!$cashflow->canBeDeleted()) {
            return redirect()->route('finance.cashflow.index')
                ->with('error', 'Entry ini tidak dapat dihapus.');
        }

        $cashflow->delete();

        return redirect()->route('finance.cashflow.index')
            ->with('success', 'Entry cashflow berhasil dihapus.');
    }

    /**
     * Handle bulk actions
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:confirm,cancel,delete',
            'entries' => 'required|array',
            'entries.*' => 'exists:cashflow_entries,id'
        ]);

        $entries = CashflowEntry::whereIn('id', $validated['entries'])->get();
        $successCount = 0;

        DB::transaction(function () use ($entries, $validated, &$successCount) {
            foreach ($entries as $entry) {
                switch ($validated['action']) {
                    case 'confirm':
                        if ($entry->status === 'pending') {
                            $entry->confirm();
                            $successCount++;
                        }
                        break;
                    case 'cancel':
                        if ($entry->status !== 'cancelled') {
                            $entry->cancel();
                            $successCount++;
                        }
                        break;
                    case 'delete':
                        if ($entry->canBeDeleted()) {
                            $entry->delete();
                            $successCount++;
                        }
                        break;
                }
            }
        });

        $actionText = match($validated['action']) {
            'confirm' => 'dikonfirmasi',
            'cancel' => 'dibatalkan',
            'delete' => 'dihapus',
        };

        return redirect()->route('finance.cashflow.index')
            ->with('success', "{$successCount} entry berhasil {$actionText}.");
    }

    /**
     * Export cashflow data
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'excel'); // excel, csv, pdf
        
        $query = CashflowEntry::with(['project', 'category', 'creator'])
            ->orderBy('transaction_date', 'desc');

        // Apply same filters as index
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $entries = $query->get();

        switch ($format) {
            case 'csv':
                return $this->exportCsv($entries, $request);
            case 'pdf':
                return $this->exportPdf($entries, $request);
            case 'excel':
            default:
                return $this->exportExcel($entries, $request);
        }
    }

    /**
     * Export to Excel format
     */
    private function exportExcel($entries, $request)
    {
        $data = $this->prepareExportData($entries);
        
        $filename = 'cashflow-export-' . now()->format('Y-m-d-H-i-s') . '.xlsx';
        
        // Create simple Excel-like CSV with proper headers
        $output = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($output, [
            'Tanggal Transaksi',
            'Deskripsi',
            'Kategori',
            'Proyek',
            'Tipe',
            'Jumlah',
            'Status',
            'Metode Pembayaran',
            'Kode Akun',
            'Dibuat Oleh',
            'Tanggal Dibuat',
            'Catatan'
        ]);
        
        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return response($csv)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export to CSV format
     */
    private function exportCsv($entries, $request)
    {
        $data = $this->prepareExportData($entries);
        
        $filename = 'cashflow-export-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $output = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($output, [
            'Tanggal Transaksi',
            'Deskripsi',
            'Kategori',
            'Proyek',
            'Tipe',
            'Jumlah',
            'Status',
            'Metode Pembayaran',
            'Kode Akun',
            'Dibuat Oleh',
            'Tanggal Dibuat',
            'Catatan'
        ]);
        
        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export to PDF format
     */
    private function exportPdf($entries, $request)
    {
        $data = $this->prepareExportData($entries);
        
        // Get summary
        $summary = [
            'total_income' => $entries->where('type', 'income')->where('status', 'confirmed')->sum('amount'),
            'total_expense' => $entries->where('type', 'expense')->where('status', 'confirmed')->sum('amount'),
        ];
        $summary['balance'] = $summary['total_income'] - $summary['total_expense'];
        
        $html = view('cashflow.export-pdf', compact('entries', 'summary', 'data'))->render();
        
        // For now, return HTML (can be enhanced with PDF library later)
        $filename = 'cashflow-export-' . now()->format('Y-m-d-H-i-s') . '.html';
        
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Prepare data for export
     */
    private function prepareExportData($entries)
    {
        return $entries->map(function ($entry) {
            return [
                $entry->transaction_date->format('Y-m-d'),
                $entry->description,
                $entry->category->name,
                $entry->project?->name ?? '-',
                $entry->formatted_type,
                $entry->amount,
                $entry->formatted_status,
                $entry->payment_method ?? '-',
                $entry->account_code ?? '-',
                $entry->creator->name,
                $entry->created_at->format('Y-m-d H:i:s'),
                $entry->notes ?? '-',
            ];
        })->toArray();
    }

    /**
     * Import cashflow data
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls',
            'has_header' => 'boolean'
        ]);

        $file = $request->file('file');
        $hasHeader = $request->boolean('has_header', true);
        
        try {
            $data = $this->parseImportFile($file, $hasHeader);
            $results = $this->processImportData($data);
            
            return redirect()->route('finance.cashflow.index')
                ->with('success', "Import berhasil: {$results['success']} data berhasil diimport, {$results['failed']} data gagal.");
                
        } catch (\Exception $e) {
            return redirect()->route('finance.cashflow.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * Parse import file
     */
    private function parseImportFile($file, $hasHeader)
    {
        $extension = $file->getClientOriginalExtension();
        $data = [];
        
        if ($extension === 'csv') {
            $handle = fopen($file->getPathname(), 'r');
            
            if ($hasHeader) {
                fgetcsv($handle); // Skip header row
            }
            
            while (($row = fgetcsv($handle)) !== false) {
                $data[] = $row;
            }
            
            fclose($handle);
        }
        
        return $data;
    }

    /**
     * Process import data
     */
    private function processImportData($data)
    {
        $success = 0;
        $failed = 0;
        
        DB::transaction(function () use ($data, &$success, &$failed) {
            foreach ($data as $row) {
                try {
                    // Expected format: [date, description, category, project, type, amount, payment_method, account_code, notes]
                    if (count($row) < 6) {
                        $failed++;
                        continue;
                    }
                    
                    $category = CashflowCategory::where('name', $row[2])->first();
                    $project = !empty($row[3]) ? Project::where('name', $row[3])->first() : null;
                    
                    if (!$category) {
                        $failed++;
                        continue;
                    }
                    
                    CashflowEntry::create([
                        'transaction_date' => Carbon::parse($row[0]),
                        'description' => $row[1],
                        'category_id' => $category->id,
                        'project_id' => $project?->id,
                        'type' => strtolower($row[4]) === 'pemasukan' ? 'income' : 'expense',
                        'amount' => (float) $row[5],
                        'payment_method' => $row[6] ?? null,
                        'account_code' => $row[7] ?? null,
                        'notes' => $row[8] ?? null,
                        'reference_type' => 'manual',
                        'status' => 'confirmed',
                        'created_by' => auth()->id(),
                        'confirmed_by' => auth()->id(),
                        'confirmed_at' => now(),
                    ]);
                    
                    $success++;
                } catch (\Exception $e) {
                    $failed++;
                }
            }
        });
        
        return compact('success', 'failed');
    }

    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Tanggal Transaksi (YYYY-MM-DD)',
            'Deskripsi',
            'Kategori',
            'Proyek (Opsional)',
            'Tipe (Pemasukan/Pengeluaran)',
            'Jumlah',
            'Metode Pembayaran (Opsional)',
            'Kode Akun (Opsional)',
            'Catatan (Opsional)'
        ];
        
        $sampleData = [
            ['2024-01-15', 'Pembayaran dari klien ABC', 'Pendapatan Proyek', 'Proyek Tower ABC', 'Pemasukan', '50000000', 'Transfer Bank', '4-1001', 'Pembayaran termin 1'],
            ['2024-01-16', 'Pembelian material besi', 'Material & Peralatan', 'Proyek Tower ABC', 'Pengeluaran', '15000000', 'Transfer Bank', '5-2001', 'Untuk konstruksi lantai 1-5']
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
            ->header('Content-Disposition', 'attachment; filename="template-import-cashflow.csv"');
    }
}
