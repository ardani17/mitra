<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Models\ProjectLocation;
use App\Models\ProjectClient;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Exports\ProjectsExport;
use App\Exports\ProjectTemplateExport;
use App\Imports\ProjectsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\ActivityLogger;

class ProjectController extends Controller
{
    use AuthorizesRequests;
    public function __construct()
    {
        // Constructor kosong karena middleware sudah ditangani di routes
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);
        
        $query = Project::with(['timelines']);
        
        // Filter berdasarkan pencarian
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        // Filter berdasarkan status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan tipe
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        // Filter berdasarkan range budget
        if ($request->has('budget_min') && $request->budget_min) {
            $query->where('planned_budget', '>=', $request->budget_min);
        }
        if ($request->has('budget_max') && $request->budget_max) {
            $query->where('planned_budget', '<=', $request->budget_max);
        }
        
        // Filter berdasarkan tanggal mulai
        if ($request->has('start_date_from') && $request->start_date_from) {
            $query->where('start_date', '>=', $request->start_date_from);
        }
        if ($request->has('start_date_to') && $request->start_date_to) {
            $query->where('start_date', '<=', $request->start_date_to);
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSorts = ['created_at', 'name', 'planned_budget', 'start_date'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest();
        }
        
        $projects = $query->paginate(10)->appends($request->query());
        
        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Project::class);
        
        $types = [
            'konstruksi' => 'Konstruksi', 
            'maintenance' => 'Maintenance', 
            'psb' => 'PSB',
            'other' => 'Other'
        ];
        $statuses = [
            'planning' => 'Perencanaan',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];
        $priorities = [
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'urgent' => 'Mendesak'
        ];
        
        return view('projects.create', compact('types', 'statuses', 'priorities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectRequest $request)
    {
        $this->authorize('create', Project::class);
        
        $data = $request->validated();
        
        // Generate kode proyek otomatis
        $data['code'] = $this->generateProjectCode();
        
        // Auto-calculate planned_total_value
        $data['planned_total_value'] = ($data['planned_service_value'] ?? 0) + ($data['planned_material_value'] ?? 0);
        
        // Auto-calculate final_total_value if both values are provided
        if (isset($data['final_service_value']) || isset($data['final_material_value'])) {
            $data['final_total_value'] = ($data['final_service_value'] ?? 0) + ($data['final_material_value'] ?? 0);
        }
        
        // Set planned_budget to planned_total_value for backward compatibility
        $data['planned_budget'] = $data['planned_total_value'];
        
        $project = Project::create($data);
        
        // Simpan atau update lokasi jika ada
        if (!empty($data['location'])) {
            ProjectLocation::addOrUpdateLocation($data['location']);
        }
        
        // Simpan atau update client jika ada
        if (!empty($data['client'])) {
            ProjectClient::addOrUpdateClient($data['client']);
        }
        
        // Log activity using ActivityLogger
        ActivityLogger::logProjectCreated($project);
        
        return redirect()->route('projects.index')->with('success', 'Proyek berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $project = Project::with(['expenses', 'activities', 'timelines', 'billings', 'revenues', 'documents.uploader'])->findOrFail($id);
        $this->authorize('view', $project);
        
        // Get comprehensive activities for the project
        $allActivities = $project->getAllActivities();
        
        return view('projects.show', compact('project', 'allActivities'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('update', $project);
        
        $types = [
            'konstruksi' => 'Konstruksi', 
            'maintenance' => 'Maintenance', 
            'psb' => 'PSB',
            'other' => 'Other'
        ];
        $statuses = [
            'planning' => 'Perencanaan',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];
        $priorities = [
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'urgent' => 'Mendesak'
        ];
        
        return view('projects.edit', compact('project', 'types', 'statuses', 'priorities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectRequest $request, string $id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('update', $project);
        
        // Store original data for comparison
        $originalData = $project->getOriginal();
        
        $data = $request->validated();
        
        // Auto-calculate planned_total_value
        $data['planned_total_value'] = ($data['planned_service_value'] ?? 0) + ($data['planned_material_value'] ?? 0);
        
        // Auto-calculate final_total_value if both values are provided
        if (isset($data['final_service_value']) || isset($data['final_material_value'])) {
            $data['final_total_value'] = ($data['final_service_value'] ?? 0) + ($data['final_material_value'] ?? 0);
        }
        
        // Set planned_budget to planned_total_value for backward compatibility
        $data['planned_budget'] = $data['planned_total_value'];
        
        $project->update($data);
        
        // Simpan atau update lokasi jika ada
        if (!empty($data['location'])) {
            ProjectLocation::addOrUpdateLocation($data['location']);
        }
        
        // Simpan atau update client jika ada
        if (!empty($data['client'])) {
            ProjectClient::addOrUpdateClient($data['client']);
        }
        
        // Log activity using ActivityLogger
        ActivityLogger::logProjectUpdated($project, $originalData);
        
        return redirect()->route('projects.show', $project->id)->with('success', 'Proyek berhasil diperbarui.');
    }

    /**
     * Show delete confirmation page
     */
    public function confirmDelete(string $id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('delete', $project);
        
        // Check if project can be deleted
        $deleteCheck = $project->canBeDeleted();
        $deletionSummary = $project->getDeletionSummary();
        
        return view('projects.confirm-delete', compact('project', 'deleteCheck', 'deletionSummary'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('delete', $project);
        
        // Check if project can be deleted
        $deleteCheck = $project->canBeDeleted();
        
        if (!$deleteCheck['can_delete']) {
            return redirect()->back()->with('error', 
                'Proyek tidak dapat dihapus: ' . implode(', ', $deleteCheck['blockers']));
        }
        
        // Log activity sebelum delete
        $projectName = $project->name;
        $projectCode = $project->code;
        $deletionSummary = $project->getDeletionSummary();
        
        try {
            // Delete project (cascade delete akan handle semua relasi)
            $project->delete();
            
            // Log successful deletion
            \Log::info('Project deleted successfully', [
                'project_name' => $projectName,
                'project_code' => $projectCode,
                'deleted_by' => Auth::user()->name,
                'deletion_summary' => $deletionSummary
            ]);
            
            $message = "Proyek '{$projectName}' berhasil dihapus beserta semua data terkait:";
            $message .= "\n- {$deletionSummary['expenses_count']} pengeluaran";
            $message .= "\n- {$deletionSummary['expense_approvals_count']} persetujuan pengeluaran";
            $message .= "\n- {$deletionSummary['activities_count']} aktivitas";
            $message .= "\n- {$deletionSummary['timelines_count']} timeline";
            $message .= "\n- {$deletionSummary['billings_count']} tagihan";
            $message .= "\n- {$deletionSummary['revenues_count']} pendapatan";
            $message .= "\n- {$deletionSummary['documents_count']} dokumen";
            
            return redirect()->route('projects.index')->with('success', $message);
                
        } catch (\Exception $e) {
            \Log::error('Failed to delete project', [
                'project_id' => $id,
                'project_name' => $projectName,
                'error' => $e->getMessage(),
                'user' => Auth::user()->name,
                'deletion_summary' => $deletionSummary
            ]);
            
            return redirect()->back()->with('error', 
                'Terjadi kesalahan saat menghapus proyek: ' . $e->getMessage());
        }
    }
    
    /**
     * Update project status
     */
    public function updateStatus(Request $request, string $id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('updateStatus', $project);
        
        $request->validate([
            'status' => 'required|in:planning,in_progress,completed,cancelled'
        ]);
        
        $originalData = $project->getOriginal();
        $project->update(['status' => $request->status]);
        
        // Log activity using ActivityLogger
        ActivityLogger::logProjectUpdated($project, $originalData);
        
        return redirect()->back()->with('success', 'Status proyek berhasil diperbarui.');
    }
    
    /**
     * Generate unique project code
     */
    private function generateProjectCode()
    {
        $year = date('Y');
        $month = date('m');
        
        // Format: PRJ-YYYY-MM-XXX (contoh: PRJ-2025-01-001)
        $prefix = "PRJ-{$year}-{$month}-";
        
        // Cari kode terakhir untuk bulan ini
        $lastProject = Project::where('code', 'like', $prefix . '%')
                             ->orderBy('code', 'desc')
                             ->first();
        
        if ($lastProject) {
            // Ambil nomor urut terakhir dan tambah 1
            $lastNumber = (int) substr($lastProject->code, -3);
            $newNumber = $lastNumber + 1;
        } else {
            // Jika belum ada proyek di bulan ini, mulai dari 1
            $newNumber = 1;
        }
        
        // Format nomor urut dengan 3 digit (001, 002, dst)
        $formattedNumber = str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $formattedNumber;
    }
    
    /**
     * Export projects to Excel
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', Project::class);
        
        $query = Project::query();
        
        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        $projects = $query->get();
        
        $filename = 'projects_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new ProjectsExport($projects), $filename);
    }
    
    /**
     * Download template Excel for import
     */
    public function downloadTemplate()
    {
        $this->authorize('create', Project::class);
        
        $filename = 'template_import_proyek.xlsx';
        
        // Use comprehensive export as template
        return Excel::download(new \App\Exports\ComprehensiveProjectsExport(null, true), $filename);
    }
    
    /**
     * Show import form
     */
    public function importForm()
    {
        $this->authorize('create', Project::class);
        
        return view('projects.import');
    }
    
    /**
     * Preview import data before saving
     */
    public function importPreview(Request $request)
    {
        $this->authorize('create', Project::class);
        
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);
        
        try {
            // Store file temporarily
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('temp', $filename);
            
            // Process file for preview
            $import = new \App\Imports\ComprehensiveProjectsImport();
            $import->setPreviewMode(true); // Set preview mode
            Excel::import($import, storage_path('app/' . $path));
            
            $validData = $import->getValidData();
            $invalidData = $import->getInvalidData();
            $errors = $import->getErrors();
            
            return view('projects.import-preview', compact(
                'validData', 
                'invalidData', 
                'errors', 
                'filename'
            ));
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirm and execute import
     */
    public function importConfirm(Request $request)
    {
        $this->authorize('create', Project::class);
        
        $request->validate([
            'filename' => 'required|string',
            'import_valid_only' => 'boolean'
        ]);
        
        try {
            $filename = $request->filename;
            $path = storage_path('app/temp/' . $filename);
            
            if (!file_exists($path)) {
                return redirect()->route('projects.import.form')
                    ->with('error', 'File tidak ditemukan atau sudah kedaluwarsa. Silakan upload ulang file Excel Anda.');
            }
            
            // Process import with confirmation
            $import = new \App\Imports\ComprehensiveProjectsImport();
            $import->setConfirmMode(true);
            $import->setImportValidOnly($request->boolean('import_valid_only', true));
            
            Excel::import($import, $path);
            
            // Clean up temp file safely
            if (file_exists($path)) {
                unlink($path);
            }
            
            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();
            
            // Log import activity
            ActivityLogger::logDataImport('projects', $successCount, $errorCount);
            
            if ($errorCount > 0 && !$request->boolean('import_valid_only')) {
                return redirect()->route('projects.index')
                    ->with('warning', "Import selesai dengan {$successCount} data berhasil dan {$errorCount} data gagal.")
                    ->with('import_errors', $errors);
            }
            
            return redirect()->route('projects.index')
                ->with('success', "Import berhasil! {$successCount} proyek telah ditambahkan.");
                
        } catch (\Exception $e) {
            // Clean up temp file if exists
            if (isset($path) && file_exists($path)) {
                unlink($path);
            }
            
            return redirect()->route('projects.import.form')
                ->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }
    
    /**
     * Import projects from Excel (legacy method - redirect to preview)
     */
    public function import(Request $request)
    {
        return $this->importPreview($request);
    }
    
    /**
     * API endpoint untuk autocomplete lokasi
     */
    public function searchLocations(Request $request)
    {
        $search = $request->get('q', '');
        
        if (empty($search)) {
            // Jika tidak ada pencarian, return lokasi populer
            $locations = ProjectLocation::getPopularLocations(10);
        } else {
            // Jika ada pencarian, cari berdasarkan nama
            $locations = ProjectLocation::searchLocations($search, 10);
        }
        
        return response()->json($locations);
    }
    
    /**
     * API endpoint untuk mendapatkan semua lokasi populer
     */
    public function getPopularLocations()
    {
        $locations = ProjectLocation::getPopularLocations(20);
        return response()->json($locations);
    }
    
    /**
     * API endpoint untuk autocomplete client
     */
    public function searchClients(Request $request)
    {
        $search = $request->get('q', '');
        
        if (empty($search)) {
            // Jika tidak ada pencarian, return client populer
            $clients = ProjectClient::getPopularClients(10);
        } else {
            // Jika ada pencarian, cari berdasarkan nama
            $clients = ProjectClient::searchClients($search, 10);
        }
        
        return response()->json($clients);
    }
    
    /**
     * API endpoint untuk mendapatkan semua client populer
     */
    public function getPopularClients()
    {
        $clients = ProjectClient::getPopularClients(20);
        return response()->json($clients);
    }
}
