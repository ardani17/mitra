<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBilling;
use App\Models\ProjectPaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class ProjectBillingController extends Controller
{
    public function __construct()
    {
        // Authentication handled by route middleware
    }

    /**
     * Display a listing of project billings
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ProjectBilling::class);

        $query = ProjectBilling::with(['project', 'paymentSchedule'])
            ->orderBy('created_at', 'desc');

        // Filter by payment type
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Search by project name or invoice number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('project', function ($projectQuery) use ($search) {
                    $projectQuery->where('name', 'ILIKE', "%{$search}%")
                               ->orWhere('code', 'ILIKE', "%{$search}%");
                })->orWhere('invoice_number', 'ILIKE', "%{$search}%");
            });
        }

        $billings = $query->paginate(15);
        $projects = Project::orderBy('name')->get();

        // Statistics
        $stats = [
            'total_billings' => ProjectBilling::count(),
            'full_payments' => ProjectBilling::where('payment_type', 'full')->count(),
            'termin_payments' => ProjectBilling::where('payment_type', 'termin')->count(),
            'total_amount' => ProjectBilling::sum('total_amount'),
            'paid_amount' => ProjectBilling::where('status', 'paid')->sum('total_amount'),
        ];

        return view('project-billings.index', compact('billings', 'projects', 'stats'));
    }

    /**
     * Show the form for creating a new project billing
     */
    public function create(Request $request)
    {
        Gate::authorize('create', ProjectBilling::class);

        $projects = Project::with('paymentSchedules')
            ->orderBy('name')
            ->get();

        $selectedProject = null;
        if ($request->filled('project_id')) {
            $selectedProject = Project::with(['paymentSchedules' => function ($query) {
                $query->where('status', 'pending')->orderBy('termin_number');
            }])->find($request->project_id);
        }

        return view('project-billings.create', compact('projects', 'selectedProject'));
    }

    /**
     * Store a newly created project billing
     */
    public function store(Request $request)
    {
        // Debug: Log all request data
        \Log::info('Project Billing Store Request:', $request->all());
        
        try {
            Gate::authorize('create', ProjectBilling::class);
        } catch (\Exception $e) {
            \Log::error('Authorization failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Tidak memiliki akses untuk membuat penagihan']);
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'payment_type' => 'required|in:termin',
            'termin_number' => 'required|integer|min:1',
            'total_termin' => 'required|integer|min:1',
            'is_final_termin' => 'nullable|boolean',
            'nilai_jasa' => 'required|numeric|min:1',
            'nilai_material' => 'required|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'ppn_rate' => 'required|numeric|min:0|max:100',
            'ppn_calculation' => 'required|in:normal,round_up,round_down',
            'ppn_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'invoice_number' => 'required|string|max:255|unique:project_billings,invoice_number',
            'billing_date' => 'required|date',
            'status' => 'required|in:draft,sent',
            'description' => 'nullable|string|max:1000'
        ]);

        \Log::info('Validation passed:', $validated);

        try {
            DB::beginTransaction();

            // Create billing data
            $billingData = [
                'project_id' => $validated['project_id'],
                'payment_type' => 'termin', // Always termin for project billing
                'termin_number' => $validated['termin_number'],
                'total_termin' => $validated['total_termin'],
                'is_final_termin' => $validated['is_final_termin'] ?? false,
                'invoice_number' => $validated['invoice_number'],
                'nilai_jasa' => $validated['nilai_jasa'],
                'nilai_material' => $validated['nilai_material'],
                'subtotal' => $validated['subtotal'] ?? ($validated['nilai_jasa'] + $validated['nilai_material']),
                'ppn_rate' => $validated['ppn_rate'],
                'ppn_calculation' => $validated['ppn_calculation'],
                'ppn_amount' => $validated['ppn_amount'],
                'total_amount' => $validated['total_amount'],
                'billing_date' => $validated['billing_date'],
                'status' => $validated['status'],
                'notes' => $validated['description'] ?? null
            ];

            \Log::info('Creating billing with data:', $billingData);

            $billing = ProjectBilling::create($billingData);

            \Log::info('Billing created successfully:', ['id' => $billing->id]);

            DB::commit();

            return redirect()->route('project-billings.index')
                           ->with('success', 'Penagihan berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Project Billing Store Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withInput()
                        ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified project billing
     */
    public function show(ProjectBilling $projectBilling)
    {
        Gate::authorize('view', $projectBilling);

        $projectBilling->load(['project', 'paymentSchedule']);

        return view('project-billings.show', compact('projectBilling'));
    }

    /**
     * Show the form for editing the specified project billing
     */
    public function edit(ProjectBilling $projectBilling)
    {
        Gate::authorize('update', $projectBilling);

        if ($projectBilling->status === 'paid') {
            return back()->withErrors(['error' => 'Penagihan yang sudah lunas tidak dapat diedit']);
        }

        $projects = Project::orderBy('name')->get();

        return view('project-billings.edit', compact('projectBilling', 'projects'));
    }

    /**
     * Update the specified project billing
     */
    public function update(Request $request, ProjectBilling $projectBilling)
    {
        Gate::authorize('update', $projectBilling);

        // Allow status change to 'paid' but prevent other edits if already paid
        if ($projectBilling->status === 'paid' && $request->input('status') !== 'paid') {
            return back()->withErrors(['error' => 'Penagihan yang sudah lunas tidak dapat diedit']);
        }

        $validated = $request->validate([
            'invoice_number' => 'required|string|max:255|unique:project_billings,invoice_number,' . $projectBilling->id,
            'nilai_jasa' => 'required|numeric|min:1',
            'nilai_material' => 'required|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'ppn_rate' => 'required|numeric|min:0|max:100',
            'ppn_calculation' => 'required|in:normal,round_up,round_down',
            'ppn_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'billing_date' => 'required|date',
            'status' => 'required|in:draft,sent,paid,overdue',
            'paid_date' => 'nullable|date|required_if:status,paid',
            'description' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Prepare update data
            $updateData = [
                'invoice_number' => $validated['invoice_number'],
                'nilai_jasa' => $validated['nilai_jasa'],
                'nilai_material' => $validated['nilai_material'],
                'subtotal' => $validated['subtotal'] ?? ($validated['nilai_jasa'] + $validated['nilai_material']),
                'ppn_rate' => $validated['ppn_rate'],
                'ppn_calculation' => $validated['ppn_calculation'],
                'ppn_amount' => $validated['ppn_amount'],
                'total_amount' => $validated['total_amount'],
                'billing_date' => $validated['billing_date'],
                'status' => $validated['status'],
                'notes' => $validated['description'] ?? null
            ];

            if ($validated['status'] === 'paid' && isset($validated['paid_date'])) {
                $updateData['paid_date'] = $validated['paid_date'];
            }

            $projectBilling->update($updateData);

            // Update payment schedule status if this is a termin payment
            if ($projectBilling->isTerminPayment() && $projectBilling->paymentSchedule) {
                $scheduleStatus = match($validated['status']) {
                    'paid' => 'paid',
                    'sent' => 'billed',
                    default => 'billed'
                };
                
                $projectBilling->paymentSchedule->update(['status' => $scheduleStatus]);
            }

            DB::commit();

            return redirect()->route('project-billings.show', $projectBilling)
                           ->with('success', 'Penagihan berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                        ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified project billing
     */
    public function destroy(ProjectBilling $projectBilling)
    {
        Gate::authorize('delete', $projectBilling);

        if ($projectBilling->status === 'paid') {
            return back()->withErrors(['error' => 'Penagihan yang sudah lunas tidak dapat dihapus']);
        }

        try {
            DB::beginTransaction();

            // Reset payment schedule status if this is a termin payment
            if ($projectBilling->isTerminPayment() && $projectBilling->paymentSchedule) {
                $projectBilling->paymentSchedule->update([
                    'status' => 'pending',
                    'billing_id' => null
                ]);
            }

            $projectBilling->delete();

            DB::commit();

            return redirect()->route('project-billings.index')
                           ->with('success', 'Penagihan berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show termin schedule management for a project
     */
    public function manageSchedule(Project $project)
    {
        Gate::authorize('create', ProjectBilling::class);

        $project->load(['paymentSchedules' => function ($query) {
            $query->orderBy('termin_number');
        }]);

        return view('project-billings.manage-schedule', compact('project'));
    }

    /**
     * Store or update termin schedule for a project
     */
    public function storeSchedule(Request $request, Project $project)
    {
        Gate::authorize('create', ProjectBilling::class);

        $validated = $request->validate([
            'schedules' => 'required|array|min:2',
            'schedules.*.name' => 'required|string|max:255',
            'schedules.*.percentage' => 'required|numeric|min:0.01|max:100',
            'schedules.*.due_date' => 'required|date|after:today',
            'schedules.*.description' => 'nullable|string|max:500'
        ]);

        try {
            $project->createTerminSchedule($validated['schedules']);

            return redirect()->route('project-billings.manage-schedule', $project)
                           ->with('success', 'Jadwal termin berhasil dibuat');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get project payment schedules (AJAX)
     */
    public function getProjectSchedules(Project $project)
    {
        $schedules = $project->paymentSchedules()
            ->where('status', 'pending')
            ->orderBy('termin_number')
            ->get();

        return response()->json($schedules);
    }

    /**
     * Create termin payment from schedule
     */
    public function createTerminPayment(Request $request, ProjectPaymentSchedule $schedule)
    {
        Gate::authorize('create', ProjectBilling::class);

        if ($schedule->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Schedule sudah diproses sebelumnya'
            ], 422);
        }

        $validated = $request->validate([
            'billing_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:billing_date',
            'ppn_rate' => 'required|numeric|min:0|max:100',
            'ppn_calculation' => 'required|in:normal,round_up,round_down',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $billing = $schedule->createBilling($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penagihan termin berhasil dibuat',
                'billing_id' => $billing->id,
                'redirect_url' => route('project-billings.show', $billing)
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
     * Generate payment schedule for project
     */
    public function generatePaymentSchedule(Request $request, Project $project)
    {
        Gate::authorize('create', ProjectBilling::class);

        $validated = $request->validate([
            'schedule_type' => 'required|in:custom,percentage,milestone',
            'schedules' => 'required|array|min:2',
            'schedules.*.name' => 'required|string|max:255',
            'schedules.*.percentage' => 'required|numeric|min:0.01|max:100',
            'schedules.*.due_date' => 'required|date|after:today',
            'schedules.*.description' => 'nullable|string|max:500'
        ]);

        // Validate total percentage = 100%
        $totalPercentage = collect($validated['schedules'])->sum('percentage');
        if (abs($totalPercentage - 100) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => "Total persentase harus 100%. Saat ini: {$totalPercentage}%"
            ], 422);
        }

        // Check for duplicate termin numbers or overlapping dates
        $dueDates = collect($validated['schedules'])->pluck('due_date')->toArray();
        if (count($dueDates) !== count(array_unique($dueDates))) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal jatuh tempo tidak boleh sama'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Delete existing schedules if any
            $project->paymentSchedules()->where('status', 'pending')->delete();

            // Create new schedules
            $schedules = $project->createTerminSchedule($validated['schedules']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal pembayaran berhasil dibuat',
                'schedules' => $schedules->load('project'),
                'redirect_url' => route('project-billings.manage-schedule', $project)
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
     * Update termin status (mark as paid, overdue, etc.)
     */
    public function updateTerminStatus(Request $request, ProjectBilling $projectBilling)
    {
        Gate::authorize('update', $projectBilling);

        if (!$projectBilling->isTerminPayment()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya termin payment yang dapat diupdate statusnya'
            ], 422);
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,sent,paid,overdue',
            'paid_date' => 'nullable|date|required_if:status,paid',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $projectBilling->update($validated);

            // Update payment schedule status
            if ($projectBilling->paymentSchedule) {
                $scheduleStatus = match($validated['status']) {
                    'paid' => 'paid',
                    'sent' => 'billed',
                    'overdue' => 'overdue',
                    default => 'billed'
                };
                
                $projectBilling->paymentSchedule->update(['status' => $scheduleStatus]);
            }

            // Check if this is the final termin and update project billing status
            if ($validated['status'] === 'paid' && $projectBilling->is_final_termin) {
                $project = $projectBilling->project;
                $allTerminsPaid = $project->projectBillings()
                    ->where('payment_type', 'termin')
                    ->where('status', '!=', 'paid')
                    ->count() === 0;

                if ($allTerminsPaid) {
                    $project->update(['billing_status' => 'fully_billed']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status termin berhasil diperbarui',
                'billing' => $projectBilling->fresh(['project', 'paymentSchedule'])
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
     * Bulk update multiple termin payments
     */
    public function bulkUpdateTermin(Request $request)
    {
        Gate::authorize('update', ProjectBilling::class);

        $validated = $request->validate([
            'billing_ids' => 'required|array|min:1',
            'billing_ids.*' => 'exists:project_billings,id',
            'action' => 'required|in:mark_sent,mark_paid,mark_overdue',
            'paid_date' => 'nullable|date|required_if:action,mark_paid',
            'notes' => 'nullable|string|max:1000'
        ]);

        $status = match($validated['action']) {
            'mark_sent' => 'sent',
            'mark_paid' => 'paid',
            'mark_overdue' => 'overdue',
            default => 'sent'
        };

        try {
            DB::beginTransaction();

            $billings = ProjectBilling::whereIn('id', $validated['billing_ids'])
                ->where('payment_type', 'termin')
                ->get();

            foreach ($billings as $billing) {
                $updateData = [
                    'status' => $status,
                    'notes' => $validated['notes'] ?? $billing->notes
                ];

                if ($status === 'paid') {
                    $updateData['paid_date'] = $validated['paid_date'];
                }

                $billing->update($updateData);

                // Update payment schedule status
                if ($billing->paymentSchedule) {
                    $scheduleStatus = match($status) {
                        'paid' => 'paid',
                        'sent' => 'billed',
                        'overdue' => 'overdue',
                        default => 'billed'
                    };
                    
                    $billing->paymentSchedule->update(['status' => $scheduleStatus]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($billings) . ' termin payment berhasil diperbarui',
                'updated_count' => count($billings)
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
     * API: Search project billings
     */
    public function search(Request $request)
    {
        Gate::authorize('viewAny', ProjectBilling::class);
        
        $query = ProjectBilling::with(['project', 'paymentSchedule'])
            ->orderBy('created_at', 'desc');
            
        // Filter by payment type
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('billing_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('billing_date', '<=', $request->date_to);
        }
        
        // Search by project name or invoice number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('project', function ($projectQuery) use ($search) {
                    $projectQuery->where('name', 'ILIKE', "%{$search}%")
                               ->orWhere('code', 'ILIKE', "%{$search}%");
                })->orWhere('invoice_number', 'ILIKE', "%{$search}%");
            });
        }
        
        // Pagination
        $limit = $request->get('limit', 15);
        $billings = $query->paginate($limit);
        
        return response()->json([
            'success' => true,
            'data' => $billings,
            'pagination' => [
                'total' => $billings->total(),
                'per_page' => $billings->perPage(),
                'current_page' => $billings->currentPage(),
                'last_page' => $billings->lastPage()
            ]
        ]);
    }
    
    /**
     * API: Get billing statistics
     */
    public function getStats(Request $request)
    {
        Gate::authorize('viewAny', ProjectBilling::class);
        
        // Filter by date range
        $query = ProjectBilling::query();
        
        if ($request->filled('period')) {
            $period = $request->period;
            $now = Carbon::now();
            
            $query->where(function($q) use ($period, $now) {
                switch ($period) {
                    case 'this_month':
                        $q->whereMonth('billing_date', $now->month)
                          ->whereYear('billing_date', $now->year);
                        break;
                    case 'last_month':
                        $lastMonth = $now->copy()->subMonth();
                        $q->whereMonth('billing_date', $lastMonth->month)
                          ->whereYear('billing_date', $lastMonth->year);
                        break;
                    case 'this_year':
                        $q->whereYear('billing_date', $now->year);
                        break;
                    case 'last_year':
                        $q->whereYear('billing_date', $now->year - 1);
                        break;
                }
            });
        }
        
        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        // Calculate statistics
        $stats = [
            'total_billings' => $query->count(),
            'full_payments' => (clone $query)->where('payment_type', 'full')->count(),
            'termin_payments' => (clone $query)->where('payment_type', 'termin')->count(),
            'total_amount' => (clone $query)->sum('total_amount'),
            'paid_amount' => (clone $query)->where('status', 'paid')->sum('total_amount'),
            'pending_amount' => (clone $query)->whereIn('status', ['draft', 'sent', 'overdue'])->sum('total_amount'),
            'status_counts' => [
                'draft' => (clone $query)->where('status', 'draft')->count(),
                'sent' => (clone $query)->where('status', 'sent')->count(),
                'paid' => (clone $query)->where('status', 'paid')->count(),
                'overdue' => (clone $query)->where('status', 'overdue')->count(),
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * API: Get project billings
     */
    public function getProjectBillings(Project $project)
    {
        Gate::authorize('view', $project);
        
        $billings = $project->projectBillings()
            ->with('paymentSchedule')
            ->orderBy('billing_date', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $billings
        ]);
    }
}