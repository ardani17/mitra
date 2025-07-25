<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ComprehensiveProjectsExport;
use App\Exports\ComprehensiveExpensesExport;
use App\Exports\ComprehensiveBillingsExport;
use App\Exports\ComprehensiveTimelinesExport;
use App\Imports\ComprehensiveProjectsImport;
use App\Imports\ComprehensiveExpensesImport;
use App\Imports\ComprehensiveBillingsImport;
use App\Imports\ComprehensiveTimelinesImport;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectBilling;
use App\Models\ProjectTimeline;
use App\Models\ImportLog;

class ExcelController extends Controller
{
    /**
     * Show the Excel export/import interface
     */
    public function index()
    {
        $projects = Project::select('id', 'code', 'name')->get();
        
        return view('excel.index', compact('projects'));
    }

    /**
     * Export data berdasarkan tipe
     */
    public function export(Request $request, $type)
    {
        $request->validate([
            'format' => 'nullable|in:data,template',
            'project_id' => 'nullable|exists:projects,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $isTemplate = $request->get('format') === 'template';
        $filename = $this->generateFilename($type, $isTemplate);

        try {
            switch ($type) {
                case 'projects':
                    return $this->exportProjects($request, $isTemplate, $filename);
                
                case 'expenses':
                    return $this->exportExpenses($request, $isTemplate, $filename);
                
                case 'billings':
                    return $this->exportBillings($request, $isTemplate, $filename);
                
                case 'timelines':
                    return $this->exportTimelines($request, $isTemplate, $filename);
                
                default:
                    return back()->with('error', 'Tipe export tidak valid');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal melakukan export: ' . $e->getMessage());
        }
    }

    /**
     * Import data berdasarkan tipe
     */
    public function import(Request $request, $type)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            
            // Log import attempt
            $importLog = ImportLog::create([
                'filename' => $originalName,
                'type' => $type,
                'status' => 'processing',
                'user_id' => auth()->id(),
            ]);

            $import = $this->getImportClass($type);
            Excel::import($import, $file);

            // Update log with results
            $importLog->update([
                'status' => $import->hasErrors() ? 'completed_with_errors' : 'completed',
                'success_count' => $import->getSuccessCount(),
                'error_count' => $import->getErrorCount(),
                'errors' => $import->hasErrors() ? json_encode($import->getErrors()) : null,
            ]);

            if ($import->hasErrors()) {
                return back()->with([
                    'warning' => "Import selesai dengan {$import->getErrorCount()} error dan {$import->getSuccessCount()} data berhasil diimport.",
                    'import_errors' => $import->getErrors()
                ]);
            }

            return back()->with('success', "Import berhasil! {$import->getSuccessCount()} data telah diimport.");

        } catch (\Exception $e) {
            if (isset($importLog)) {
                $importLog->update([
                    'status' => 'failed',
                    'errors' => json_encode(['System Error: ' . $e->getMessage()])
                ]);
            }

            return back()->with('error', 'Gagal melakukan import: ' . $e->getMessage());
        }
    }

    /**
     * Download template untuk import
     */
    public function downloadTemplate($type)
    {
        $filename = $this->generateFilename($type, true);

        try {
            switch ($type) {
                case 'projects':
                    return Excel::download(new ComprehensiveProjectsExport(null, true), $filename);
                
                case 'expenses':
                    return Excel::download(new ComprehensiveExpensesExport(null, true), $filename);
                
                case 'billings':
                    return Excel::download(new ComprehensiveBillingsExport(null, true), $filename);
                
                case 'timelines':
                    return Excel::download(new ComprehensiveTimelinesExport(null, true), $filename);
                
                default:
                    return back()->with('error', 'Tipe template tidak valid');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mendownload template: ' . $e->getMessage());
        }
    }

    /**
     * Export projects
     */
    private function exportProjects(Request $request, $isTemplate, $filename)
    {
        if ($isTemplate) {
            return Excel::download(new ComprehensiveProjectsExport(null, true), $filename);
        }

        $query = Project::with(['billings']);

        // Apply filters
        if ($request->filled('project_id')) {
            $query->where('id', $request->project_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $projects = $query->get();

        return Excel::download(new ComprehensiveProjectsExport($projects), $filename);
    }

    /**
     * Export expenses
     */
    private function exportExpenses(Request $request, $isTemplate, $filename)
    {
        if ($isTemplate) {
            return Excel::download(new ComprehensiveExpensesExport(null, true), $filename);
        }

        $query = ProjectExpense::with(['project', 'approvals.user']);

        // Apply filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        $expenses = $query->get();

        return Excel::download(new ComprehensiveExpensesExport($expenses), $filename);
    }

    /**
     * Export billings
     */
    private function exportBillings(Request $request, $isTemplate, $filename)
    {
        if ($isTemplate) {
            return Excel::download(new ComprehensiveBillingsExport(null, true), $filename);
        }

        $query = ProjectBilling::with(['project', 'billingBatch']);

        // Apply filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('billing_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('billing_date', '<=', $request->date_to);
        }

        $billings = $query->get();

        return Excel::download(new ComprehensiveBillingsExport($billings), $filename);
    }

    /**
     * Export timelines
     */
    private function exportTimelines(Request $request, $isTemplate, $filename)
    {
        if ($isTemplate) {
            return Excel::download(new ComprehensiveTimelinesExport(null, true), $filename);
        }

        $query = ProjectTimeline::with(['project']);

        // Apply filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('planned_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('planned_date', '<=', $request->date_to);
        }

        $timelines = $query->get();

        return Excel::download(new ComprehensiveTimelinesExport($timelines), $filename);
    }

    /**
     * Get import class berdasarkan tipe
     */
    private function getImportClass($type)
    {
        switch ($type) {
            case 'projects':
                return new ComprehensiveProjectsImport();
            
            case 'expenses':
                return new ComprehensiveExpensesImport();
            
            case 'billings':
                return new ComprehensiveBillingsImport();
            
            case 'timelines':
                return new ComprehensiveTimelinesImport();
            
            default:
                throw new \Exception('Tipe import tidak valid');
        }
    }

    /**
     * Generate filename untuk export
     */
    private function generateFilename($type, $isTemplate = false)
    {
        $typeNames = [
            'projects' => 'Proyek',
            'expenses' => 'Pengeluaran',
            'billings' => 'Tagihan',
            'timelines' => 'Timeline'
        ];

        $typeName = $typeNames[$type] ?? $type;
        $prefix = $isTemplate ? 'Template_' : 'Data_';
        $date = now()->format('Y-m-d_H-i-s');

        return "{$prefix}{$typeName}_{$date}.xlsx";
    }

    /**
     * Show import logs
     */
    public function importLogs()
    {
        $logs = ImportLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('excel.import-logs', compact('logs'));
    }

    /**
     * Show import log detail
     */
    public function importLogDetail($id)
    {
        $log = ImportLog::with('user')->findOrFail($id);
        $errors = $log->errors ? json_decode($log->errors, true) : [];

        return view('excel.import-log-detail', compact('log', 'errors'));
    }
}
