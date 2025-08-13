<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ProjectPaymentScheduleController extends Controller
{
    /**
     * API method to search and filter payment schedules
     */
    public function search(Request $request)
    {
        Gate::authorize('viewAny', ProjectPaymentSchedule::class);
        
        $query = ProjectPaymentSchedule::query();
        
        // Filter by project
        if ($request->has('project_id') && $request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            switch ($request->status) {
                case 'pending':
                    $query->where('status', 'pending');
                    break;
                case 'billed':
                    $query->where('status', 'billed');
                    break;
                case 'paid':
                    $query->where('status', 'paid');
                    break;
                case 'overdue':
                    $query->where('status', 'pending')
                          ->whereDate('due_date', '<', now());
                    break;
            }
        }
        
        // Filter by due date range
        if ($request->has('due_date_start') && $request->due_date_start) {
            $query->whereDate('due_date', '>=', $request->due_date_start);
        }
        
        if ($request->has('due_date_end') && $request->due_date_end) {
            $query->whereDate('due_date', '<=', $request->due_date_end);
        }
        
        // Search by term
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('termin_name', 'like', "%{$search}%")
                  ->orWhereHas('project', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }
        
        // Order by due date
        $query->orderBy('due_date', $request->input('order', 'asc'));
        
        // Paginate results
        $perPage = $request->input('per_page', 10);
        $schedules = $query->with('project')->paginate($perPage);
        
        return response()->json($schedules);
    }
    
    /**
     * Get statistics for payment schedules
     */
    public function getStats(Request $request)
    {
        Gate::authorize('viewAny', ProjectPaymentSchedule::class);
        
        $query = ProjectPaymentSchedule::query();
        
        // Filter by project if specified
        if ($request->has('project_id') && $request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        
        // Filter by period if specified
        if ($request->has('period') && $request->period) {
            $now = Carbon::now();
            
            switch ($request->period) {
                case 'week':
                    $query->whereBetween('due_date', [
                        $now->startOfWeek()->format('Y-m-d'),
                        $now->endOfWeek()->format('Y-m-d')
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('due_date', $now->month)
                          ->whereYear('due_date', $now->year);
                    break;
                case 'quarter':
                    $query->whereBetween('due_date', [
                        $now->startOfQuarter()->format('Y-m-d'),
                        $now->endOfQuarter()->format('Y-m-d')
                    ]);
                    break;
                case 'year':
                    $query->whereYear('due_date', $now->year);
                    break;
            }
        }
        
        // Calculate statistics
        $stats = [
            'total_count' => $query->count(),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'billed_count' => (clone $query)->where('status', 'billed')->count(),
            'paid_count' => (clone $query)->where('status', 'paid')->count(),
            'overdue_count' => (clone $query)->where('status', 'pending')
                                            ->whereDate('due_date', '<', now())
                                            ->count(),
            'total_amount' => (clone $query)->sum('amount'),
            'pending_amount' => (clone $query)->where('status', 'pending')->sum('amount'),
            'billed_amount' => (clone $query)->where('status', 'billed')->sum('amount'),
            'paid_amount' => (clone $query)->where('status', 'paid')->sum('amount'),
            'overdue_amount' => (clone $query)->where('status', 'pending')
                                             ->whereDate('due_date', '<', now())
                                             ->sum('amount'),
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Get all payment schedules for a specific project
     */
    public function getProjectSchedules(Request $request, $projectId)
    {
        Gate::authorize('viewAny', ProjectPaymentSchedule::class);
        
        $project = Project::findOrFail($projectId);
        
        $query = ProjectPaymentSchedule::where('project_id', $projectId);
        
        // Filter by status if specified
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $schedules = $query->orderBy('due_date')->get();
        
        return response()->json($schedules);
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ProjectPaymentSchedule::class);

        $query = ProjectPaymentSchedule::with(['project', 'billing']);

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by due date range
        if ($request->filled('due_date_from')) {
            $query->where('due_date', '>=', $request->due_date_from);
        }
        if ($request->filled('due_date_to')) {
            $query->where('due_date', '<=', $request->due_date_to);
        }

        // Search by project name or termin name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('termin_name', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($projectQuery) use ($search) {
                      $projectQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        $schedules = $query->orderBy('due_date', 'asc')
                          ->orderBy('termin_number', 'asc')
                          ->paginate(20);

        $projects = Project::select('id', 'name', 'code')->orderBy('name')->get();

        $stats = [
            'total_schedules' => ProjectPaymentSchedule::count(),
            'pending_schedules' => ProjectPaymentSchedule::where('status', 'pending')->count(),
            'overdue_schedules' => ProjectPaymentSchedule::overdue()->count(),
            'total_amount' => ProjectPaymentSchedule::sum('amount'),
            'pending_amount' => ProjectPaymentSchedule::where('status', 'pending')->sum('amount'),
        ];

        return view('project-payment-schedules.index', compact('schedules', 'projects', 'stats'));
    }

    /**
     * Show the form for creating a new payment schedule
     */
    public function create(Request $request)
    {
        Gate::authorize('create', ProjectPaymentSchedule::class);

        $project = null;
        if ($request->filled('project_id')) {
            $project = Project::findOrFail($request->project_id);
        }

        $projects = Project::select('id', 'name', 'code', 'service_value', 'material_value')
                          ->orderBy('name')
                          ->get();

        return view('project-payment-schedules.create', compact('projects', 'project'));
    }

    /**
     * Store a newly created payment schedule
     */
    public function store(Request $request)
    {
        Gate::authorize('create', ProjectPaymentSchedule::class);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'schedules' => 'required|array|min:2',
            'schedules.*.termin_name' => 'required|string|max:255',
            'schedules.*.percentage' => 'required|numeric|min:0.01|max:100',
            'schedules.*.due_date' => 'required|date|after:today',
            'schedules.*.description' => 'nullable|string|max:500'
        ]);

        // Validate total percentage = 100%
        $totalPercentage = collect($validated['schedules'])->sum('percentage');
        if (abs($totalPercentage - 100) > 0.01) {
            return back()->withErrors([
                'schedules' => "Total persentase harus 100%. Saat ini: {$totalPercentage}%"
            ])->withInput();
        }

        // Check for duplicate due dates
        $dueDates = collect($validated['schedules'])->pluck('due_date')->toArray();
        if (count($dueDates) !== count(array_unique($dueDates))) {
            return back()->withErrors([
                'schedules' => 'Tanggal jatuh tempo tidak boleh sama'
            ])->withInput();
        }

        $project = Project::findOrFail($validated['project_id']);

        try {
            DB::beginTransaction();

            // Delete existing pending schedules
            $project->paymentSchedules()->where('status', 'pending')->delete();

            // Create new schedules
            $schedules = $project->createTerminSchedule($validated['schedules']);

            DB::commit();

            return redirect()->route('project-payment-schedules.index')
                           ->with('success', 'Jadwal pembayaran berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified payment schedule
     */
    public function show(ProjectPaymentSchedule $projectPaymentSchedule)
    {
        Gate::authorize('view', $projectPaymentSchedule);

        $projectPaymentSchedule->load(['project', 'billing']);

        return view('project-payment-schedules.show', compact('projectPaymentSchedule'));
    }

    /**
     * Show the form for editing the specified payment schedule
     */
    public function edit(ProjectPaymentSchedule $projectPaymentSchedule)
    {
        Gate::authorize('update', $projectPaymentSchedule);

        if ($projectPaymentSchedule->status !== 'pending') {
            return redirect()->route('project-payment-schedules.show', $projectPaymentSchedule)
                           ->with('error', 'Hanya schedule dengan status pending yang dapat diedit');
        }

        $projects = Project::select('id', 'name', 'code')->orderBy('name')->get();

        return view('project-payment-schedules.edit', compact('projectPaymentSchedule', 'projects'));
    }

    /**
     * Update the specified payment schedule
     */
    public function update(Request $request, ProjectPaymentSchedule $projectPaymentSchedule)
    {
        Gate::authorize('update', $projectPaymentSchedule);

        if ($projectPaymentSchedule->status !== 'pending') {
            return redirect()->route('project-payment-schedules.show', $projectPaymentSchedule)
                           ->with('error', 'Hanya schedule dengan status pending yang dapat diupdate');
        }

        $validated = $request->validate([
            'termin_name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0.01|max:100',
            'due_date' => 'required|date|after:today',
            'description' => 'nullable|string|max:500'
        ]);

        // Validate total percentage for the project
        $otherSchedulesPercentage = $projectPaymentSchedule->project
            ->paymentSchedules()
            ->where('id', '!=', $projectPaymentSchedule->id)
            ->sum('percentage');

        $totalPercentage = $otherSchedulesPercentage + $validated['percentage'];
        if (abs($totalPercentage - 100) > 0.01) {
            return back()->withErrors([
                'percentage' => "Total persentase untuk proyek ini akan menjadi {$totalPercentage}%. Harus 100%."
            ])->withInput();
        }

        try {
            $projectPaymentSchedule->update($validated);

            return redirect()->route('project-payment-schedules.show', $projectPaymentSchedule)
                           ->with('success', 'Schedule berhasil diperbarui');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified payment schedule
     */
    public function destroy(ProjectPaymentSchedule $projectPaymentSchedule)
    {
        Gate::authorize('delete', $projectPaymentSchedule);

        if ($projectPaymentSchedule->status !== 'pending') {
            return redirect()->route('project-payment-schedules.index')
                           ->with('error', 'Hanya schedule dengan status pending yang dapat dihapus');
        }

        try {
            $projectPaymentSchedule->delete();

            return redirect()->route('project-payment-schedules.index')
                           ->with('success', 'Schedule berhasil dihapus');

        } catch (\Exception $e) {
            return redirect()->route('project-payment-schedules.index')
                           ->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk create schedules for multiple projects
     */
    public function bulkCreateSchedule(Request $request)
    {
        Gate::authorize('create', ProjectPaymentSchedule::class);

        $validated = $request->validate([
            'project_ids' => 'required|array|min:1',
            'project_ids.*' => 'exists:projects,id',
            'schedule_template' => 'required|array|min:2',
            'schedule_template.*.termin_name' => 'required|string|max:255',
            'schedule_template.*.percentage' => 'required|numeric|min:0.01|max:100',
            'schedule_template.*.days_offset' => 'required|integer|min:1|max:365',
            'schedule_template.*.description' => 'nullable|string|max:500'
        ]);

        // Validate total percentage = 100%
        $totalPercentage = collect($validated['schedule_template'])->sum('percentage');
        if (abs($totalPercentage - 100) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => "Total persentase harus 100%. Saat ini: {$totalPercentage}%"
            ], 422);
        }

        try {
            DB::beginTransaction();

            $createdCount = 0;
            $projects = Project::whereIn('id', $validated['project_ids'])->get();

            foreach ($projects as $project) {
                // Delete existing pending schedules
                $project->paymentSchedules()->where('status', 'pending')->delete();

                // Create schedules based on template
                $schedules = [];
                foreach ($validated['schedule_template'] as $index => $template) {
                    $schedules[] = [
                        'termin_name' => $template['termin_name'],
                        'percentage' => $template['percentage'],
                        'due_date' => now()->addDays($template['days_offset'])->toDateString(),
                        'description' => $template['description'] ?? null
                    ];
                }

                $project->createTerminSchedule($schedules);
                $createdCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Jadwal pembayaran berhasil dibuat untuk {$createdCount} proyek",
                'created_count' => $createdCount
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Adjust existing schedule (modify due dates, percentages)
     */
    public function adjustSchedule(Request $request, Project $project)
    {
        Gate::authorize('update', ProjectPaymentSchedule::class);

        $validated = $request->validate([
            'adjustments' => 'required|array|min:1',
            'adjustments.*.schedule_id' => 'required|exists:project_payment_schedules,id',
            'adjustments.*.percentage' => 'nullable|numeric|min:0.01|max:100',
            'adjustments.*.due_date' => 'nullable|date|after:today',
            'adjustments.*.description' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['adjustments'] as $adjustment) {
                $schedule = ProjectPaymentSchedule::findOrFail($adjustment['schedule_id']);
                
                // Only allow adjustments to pending schedules
                if ($schedule->status !== 'pending') {
                    continue;
                }

                $updateData = array_filter([
                    'percentage' => $adjustment['percentage'] ?? null,
                    'due_date' => $adjustment['due_date'] ?? null,
                    'description' => $adjustment['description'] ?? null
                ]);

                if (!empty($updateData)) {
                    $schedule->update($updateData);
                }
            }

            // Validate total percentage after adjustments
            $totalPercentage = $project->paymentSchedules()->sum('percentage');
            if (abs($totalPercentage - 100) > 0.01) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => "Total persentase setelah penyesuaian: {$totalPercentage}%. Harus 100%."
                ], 422);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal pembayaran berhasil disesuaikan',
                'schedules' => $project->paymentSchedules()->orderBy('termin_number')->get()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export schedules to Excel/PDF
     */
    public function export(Request $request)
    {
        Gate::authorize('viewAny', ProjectPaymentSchedule::class);

        $format = $request->get('format', 'excel');
        $projectId = $request->get('project_id');

        $query = ProjectPaymentSchedule::with(['project', 'billing']);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $schedules = $query->orderBy('due_date')->get();

        if ($format === 'pdf') {
            // TODO: Implement PDF export
            return response()->json(['message' => 'PDF export belum tersedia'], 501);
        }

        // Excel export
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PaymentSchedulesExport($schedules),
            'payment-schedules-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}