<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ExpenseRequest;
use App\Models\ProjectExpense;
use App\Models\Project;
use App\Models\ExpenseApproval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Exports\ExpensesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\ActivityLogger;

class ExpenseController extends Controller
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
        $this->authorize('viewAny', ProjectExpense::class);
        
        $query = ProjectExpense::with(['project', 'approvals.approver']);
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('project', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter berdasarkan project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan kategori
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        // Filter berdasarkan range amount
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }
        
        // Filter berdasarkan tanggal
        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);
        
        $expenses = $query->paginate(15)->withQueryString();
        $projects = Project::all();
        
        return view('expenses.index', compact('expenses', 'projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', ProjectExpense::class);
        
        $project = null;
        if ($request->has('project')) {
            $project = Project::findOrFail($request->project);
        }
        
        $projects = Project::all();
        $categories = [
            'material' => 'Material',
            'labor' => 'Tenaga Kerja',
            'equipment' => 'Peralatan',
            'transportation' => 'Transportasi',
            'other' => 'Lainnya'
        ];
        
        return view('expenses.create', compact('project', 'projects', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ExpenseRequest $request)
    {
        $this->authorize('create', ProjectExpense::class);
        
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';
        
        $expense = ProjectExpense::create($data);
        
        // Buat approval records untuk workflow
        $this->createApprovalWorkflow($expense);
        
        // Log activity using ActivityLogger
        ActivityLogger::logExpenseCreated($expense);
        
        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dibuat dan menunggu persetujuan.');
    }
    
    /**
     * Create approval workflow for expense
     */
    private function createApprovalWorkflow(ProjectExpense $expense)
    {
        // Cari user dengan role finance_manager
        $financeManager = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'finance_manager');
        })->first();
        
        // Buat approval record untuk Finance Manager (selalu diperlukan)
        ExpenseApproval::create([
            'expense_id' => $expense->id,
            'approver_id' => $financeManager ? $financeManager->id : 1, // Fallback ke user ID 1
            'level' => 'finance_manager',
            'status' => 'pending'
        ]);
        
        // Jika amount > 10 juta, perlu approval Direktur
        // Jika <= 10 juta, perlu approval Project Manager
        if ($expense->amount > 10000000) {
            $director = \App\Models\User::whereHas('roles', function($q) {
                $q->where('name', 'direktur');
            })->first();
            
            ExpenseApproval::create([
                'expense_id' => $expense->id,
                'approver_id' => $director ? $director->id : 1, // Fallback ke user ID 1
                'level' => 'direktur',
                'status' => 'pending'
            ]);
        } else {
            $projectManager = \App\Models\User::whereHas('roles', function($q) {
                $q->where('name', 'project_manager');
            })->first();
            
            ExpenseApproval::create([
                'expense_id' => $expense->id,
                'approver_id' => $projectManager ? $projectManager->id : 1, // Fallback ke user ID 1
                'level' => 'project_manager',
                'status' => 'pending'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $expense = ProjectExpense::with(['project', 'approvals.approver'])->findOrFail($id);
        $this->authorize('view', $expense);
        
        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $expense = ProjectExpense::findOrFail($id);
        $this->authorize('update', $expense);
        
        $projects = Project::all();
        $categories = [
            'material' => 'Material',
            'labor' => 'Tenaga Kerja',
            'equipment' => 'Peralatan',
            'transportation' => 'Transportasi',
            'other' => 'Lainnya'
        ];
        
        return view('expenses.edit', compact('expense', 'projects', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ExpenseRequest $request, string $id)
    {
        $expense = ProjectExpense::findOrFail($id);
        $this->authorize('update', $expense);
        
        $expense->update($request->validated());
        
        // Log activity
        $expense->project->activities()->create([
            'user_id' => Auth::id(),
            'activity_type' => 'expense_updated',
            'description' => 'Pengeluaran diperbarui: ' . $expense->description . ' - Rp ' . number_format($expense->amount)
        ]);
        
        return redirect()->route('expenses.show', $expense->id)->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $expense = ProjectExpense::findOrFail($id);
        $this->authorize('delete', $expense);
        
        $projectId = $expense->project_id;
        $description = $expense->description;
        $amount = $expense->amount;
        
        $expense->delete();
        
        // Log activity
        Project::find($projectId)->activities()->create([
            'user_id' => Auth::id(),
            'activity_type' => 'expense_deleted',
            'description' => 'Pengeluaran dihapus: ' . $description . ' - Rp ' . number_format($amount)
        ]);
        
        return redirect()->route('projects.show', $projectId)->with('success', 'Pengeluaran berhasil dihapus.');
    }
    
    /**
     * Submit expense for approval
     */
    public function submitForApproval(string $id)
    {
        $expense = ProjectExpense::findOrFail($id);
        
        // Hanya bisa submit jika status masih draft
        if ($expense->status !== 'draft') {
            return redirect()->back()->with('error', 'Expense has already been submitted for approval.');
        }
        
        $expense->update(['status' => 'submitted']);
        
        // Buat approval records untuk setiap level
        $approvalLevels = ['finance_manager', 'project_manager', 'direktur'];
        
        foreach ($approvalLevels as $level) {
            ExpenseApproval::create([
                'expense_id' => $expense->id,
                'approver_id' => null, // Akan diisi saat approval
                'level' => $level,
                'status' => 'pending'
            ]);
        }
        
        return redirect()->back()->with('success', 'Expense submitted for approval successfully.');
    }
    
    /**
     * Approve or reject expense
     */
    public function approve(Request $request, ProjectExpense $expense)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:500'
        ]);
        
        $user = Auth::user();
        $userRole = $user->roles->first()->name ?? null;
        
        // Check if user has permission to approve
        if (!in_array($userRole, ['finance_manager', 'direktur', 'project_manager'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk melakukan approval.');
        }
        
        // Find the approval record for this user role
        $approval = ExpenseApproval::where('expense_id', $expense->id)
            ->where('level', $userRole)
            ->first();
        
        if (!$approval) {
            return redirect()->back()->with('error', 'Approval record tidak ditemukan.');
        }
        
        // Update approval record
        $approval->update([
            'approver_id' => $user->id,
            'status' => $request->status,
            'notes' => $request->notes,
            'approved_at' => now()
        ]);
        
        // Update expense status based on approval workflow
        if ($request->status === 'rejected') {
            $expense->update(['status' => 'rejected']);
            
            // Log rejection
            \Log::info('Expense rejected via ExpenseController', [
                'expense_id' => $expense->id,
                'approver_id' => $user->id,
                'approver_role' => $userRole,
                'notes' => $request->notes
            ]);
        } else {
            // Check if all required approvals are complete
            $requiredApprovals = ['finance_manager'];
            if ($expense->amount > 10000000) { // 10 juta
                $requiredApprovals[] = 'direktur';
            } else {
                $requiredApprovals[] = 'project_manager';
            }

            $completedApprovals = $expense->approvals()
                ->whereIn('level', $requiredApprovals)
                ->where('status', 'approved')
                ->pluck('level')
                ->toArray();

            if (count($completedApprovals) >= count($requiredApprovals)) {
                // Use update method to trigger observer
                $expense->update(['status' => 'approved']);
                
                // Log successful approval
                \Log::info('Expense fully approved via ExpenseController', [
                    'expense_id' => $expense->id,
                    'amount' => $expense->amount,
                    'required_approvals' => $requiredApprovals,
                    'completed_approvals' => $completedApprovals,
                    'final_approver_id' => $user->id,
                    'final_approver_role' => $userRole
                ]);
            } else {
                // Log partial approval
                \Log::info('Expense partially approved via ExpenseController', [
                    'expense_id' => $expense->id,
                    'amount' => $expense->amount,
                    'required_approvals' => $requiredApprovals,
                    'completed_approvals' => $completedApprovals,
                    'approver_id' => $user->id,
                    'approver_role' => $userRole,
                    'remaining_approvals' => array_diff($requiredApprovals, $completedApprovals)
                ]);
            }
        }
        
        // Log activity using ActivityLogger
        ActivityLogger::logExpenseApproval($expense, $approval);
        
        $message = $request->status === 'approved' ? 'Pengeluaran berhasil disetujui.' : 'Pengeluaran ditolak.';
        return redirect()->back()->with('success', $message);
    }
    
    /**
     * Export expenses to Excel
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', ProjectExpense::class);
        
        $query = ProjectExpense::with(['project', 'approvals.approver']);
        
        // Apply same filters as index method
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('project', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }
        
        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }
        
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);
        
        $expenses = $query->get();
        
        $filename = 'expenses_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new ExpensesExport($expenses), $filename);
    }
}
