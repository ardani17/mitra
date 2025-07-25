<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Exports\ProjectsExport;
use App\Exports\ProjectTemplateExport;
use App\Imports\ProjectsImport;
use Maatwebsite\Excel\Facades\Excel;

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
        
        // Log activity
        $project->activities()->create([
            'user_id' => Auth::id(),
            'activity_type' => 'project_created',
            'description' => 'Proyek dibuat: ' . $project->name
        ]);
        
        return redirect()->route('projects.index')->with('success', 'Proyek berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $project = Project::with(['expenses', 'activities', 'timelines', 'billings', 'revenues', 'documents.uploader'])->findOrFail($id);
        $this->authorize('view', $project);
        
        return view('projects.show', compact('project'));
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
        
        $data = $request->validated();
        
        // Auto-calculate planned_total_value
        $data['planned_total_value'] = ($data['planned_service_value'] ?? 0) + ($data['planned_material_value'] ?? 0);
        
        // Auto-calculate final_total_value if both values are provided
        if (isset($data['final_service_value']) || isset($data['final_material_value'])) {
            $data['final_total_value'] = ($data['final_service_value'] ?? 0) + ($data['final_material_value'] ?? 0);
        }
        
        // Set planned_budget to planned_total_value for backward compatibility
        $data['planned_budget'] = $data['planned_total_value'];
        
        $oldStatus = $project->status;
        $project->update($data);
        
        // Log activity jika status berubah
        if ($oldStatus !== $request->status) {
            $project->activities()->create([
                'user_id' => Auth::id(),
                'activity_type' => 'status_changed',
                'description' => "Status proyek berubah dari {$oldStatus} ke {$request->status}",
                'changes' => [
                    'old_status' => $oldStatus,
                    'new_status' => $request->status
                ]
            ]);
        }
        
        return redirect()->route('projects.show', $project->id)->with('success', 'Proyek berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('delete', $project);
        
        // Log activity sebelum delete
        $projectName = $project->name;
        $projectCode = $project->code;
        
        // Count related data for confirmation
        $relatedData = [
            'expenses' => $project->expenses()->count(),
            'timelines' => $project->timelines()->count(),
            'billings' => $project->billings()->count(),
            'revenues' => $project->revenues()->count(),
            'documents' => $project->documents()->count(),
            'activities' => $project->activities()->count(),
        ];
        
        try {
            // Delete project (cascade delete akan handle relasi)
            $project->delete();
            
            // Log successful deletion
            \Log::info('Project deleted successfully', [
                'project_name' => $projectName,
                'project_code' => $projectCode,
                'deleted_by' => Auth::user()->name,
                'related_data_deleted' => $relatedData
            ]);
            
            return redirect()->route('projects.index')->with('success', 
                "Proyek '{$projectName}' berhasil dihapus beserta semua data terkait.");
                
        } catch (\Exception $e) {
            \Log::error('Failed to delete project', [
                'project_id' => $id,
                'project_name' => $projectName,
                'error' => $e->getMessage(),
                'user' => Auth::user()->name
            ]);
            
            return redirect()->back()->with('error', 
                'Terjadi kesalahan saat menghapus proyek. Silakan coba lagi.');
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
        
        $oldStatus = $project->status;
        $project->update(['status' => $request->status]);
        
        // Log activity
        $project->activities()->create([
            'user_id' => Auth::id(),
            'activity_type' => 'status_updated',
            'description' => "Status proyek diperbarui dari {$oldStatus} ke {$request->status}",
            'changes' => [
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ]
        ]);
        
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
        
        $query = Project::with(['user']);
        
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
        
        return Excel::download(new ProjectTemplateExport, $filename);
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
     * Import projects from Excel
     */
    public function import(Request $request)
    {
        $this->authorize('create', Project::class);
        
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);
        
        try {
            $import = new ProjectsImport();
            Excel::import($import, $request->file('file'));
            
            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();
            
            if ($import->hasErrors()) {
                return redirect()->back()
                    ->with('warning', "Import selesai dengan {$successCount} data berhasil dan {$errorCount} data gagal.")
                    ->with('import_errors', $errors);
            }
            
            return redirect()->route('projects.index')
                ->with('success', "Import berhasil! {$successCount} proyek telah ditambahkan.");
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }
}
