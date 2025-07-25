<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExpenseApproval;
use App\Models\ProjectExpense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExpenseApprovalController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of pending approvals for the current user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->roles->first()->name ?? null;
        
        // Hanya role tertentu yang bisa melihat approval
        if (!in_array($userRole, ['finance_manager', 'direktur', 'project_manager'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }
        
        $query = ExpenseApproval::with(['expense.project', 'expense.user'])
            ->where('level', $userRole)
            ->where('status', 'pending');
        
        // Filter berdasarkan search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('expense', function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('project', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter berdasarkan project
        if ($request->filled('project_id')) {
            $query->whereHas('expense', function($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }
        
        // Filter berdasarkan range amount
        if ($request->filled('amount_min')) {
            $query->whereHas('expense', function($q) use ($request) {
                $q->where('amount', '>=', $request->amount_min);
            });
        }
        if ($request->filled('amount_max')) {
            $query->whereHas('expense', function($q) use ($request) {
                $q->where('amount', '<=', $request->amount_max);
            });
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);
        
        $approvals = $query->paginate(15)->withQueryString();
        
        // Get projects for filter
        $projects = \App\Models\Project::all();
        
        return view('expenses.approvals', compact('approvals', 'projects', 'userRole'));
    }

    /**
     * Show the approval form for a specific expense
     */
    public function show(ExpenseApproval $approval)
    {
        $user = Auth::user();
        $userRole = $user->roles->first()->name ?? null;
        
        // Check if user has permission to approve this expense
        if ($approval->level !== $userRole) {
            abort(403, 'Anda tidak memiliki izin untuk menyetujui pengeluaran ini.');
        }
        
        if ($approval->status !== 'pending') {
            return redirect()->route('expense-approvals.index')
                ->with('error', 'Approval ini sudah diproses sebelumnya.');
        }
        
        $expense = $approval->expense->load(['project', 'user', 'approvals.approver']);
        
        return view('expenses.approval-detail', compact('approval', 'expense'));
    }

    /**
     * Process the approval (approve or reject)
     */
    public function process(Request $request, ExpenseApproval $approval)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:500'
        ]);
        
        $user = Auth::user();
        $userRole = $user->roles->first()->name ?? null;
        
        // Check if user has permission to approve this expense
        if ($approval->level !== $userRole) {
            abort(403, 'Anda tidak memiliki izin untuk menyetujui pengeluaran ini.');
        }
        
        if ($approval->status !== 'pending') {
            return redirect()->route('expense-approvals.index')
                ->with('error', 'Approval ini sudah diproses sebelumnya.');
        }
        
        // Update approval record
        $approval->update([
            'approver_id' => $user->id,
            'status' => $request->status,
            'notes' => $request->notes,
            'approved_at' => now()
        ]);
        
        $expense = $approval->expense;
        
        // Update expense status based on approval workflow
        if ($request->status === 'rejected') {
            $expense->update(['status' => 'rejected']);
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
                $expense->update(['status' => 'approved']);
            }
        }
        
        // Log activity
        $expense->project->activities()->create([
            'user_id' => $user->id,
            'activity_type' => 'expense_' . $request->status,
            'description' => 'Pengeluaran ' . ($request->status === 'approved' ? 'disetujui' : 'ditolak') . ' oleh ' . $user->name . ': ' . $expense->description
        ]);
        
        $message = $request->status === 'approved' ? 'Pengeluaran berhasil disetujui.' : 'Pengeluaran ditolak.';
        return redirect()->route('expense-approvals.index')->with('success', $message);
    }
}
