<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectExpense;
use App\Models\ExpenseModificationApproval;
use App\Models\Project;
use App\Http\Requests\ExpenseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExpenseModificationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display pending modification requests
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ExpenseModificationApproval::class);

        $query = ExpenseModificationApproval::with(['expense.project', 'requester', 'approver'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by action type
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by requester
        if ($request->filled('requested_by')) {
            $query->where('requested_by', $request->requested_by);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $modifications = $query->paginate(15)->withQueryString();

        return view('expense-modifications.index', compact('modifications'));
    }

    /**
     * Show the form for requesting expense edit
     */
    public function editForm(ProjectExpense $expense)
    {
        $this->authorize('requestModification', $expense);

        if (!$expense->canBeModified()) {
            return redirect()->back()->with('error', 'Pengeluaran ini tidak dapat dimodifikasi saat ini.');
        }

        $projects = Project::all();
        $categories = [
            'material' => 'Material',
            'labor' => 'Tenaga Kerja',
            'equipment' => 'Peralatan',
            'transportation' => 'Transportasi',
            'other' => 'Lainnya'
        ];

        return view('expense-modifications.edit-form', compact('expense', 'projects', 'categories'));
    }

    /**
     * Submit expense edit request
     */
    public function requestEdit(ExpenseRequest $request, ProjectExpense $expense)
    {
        $this->authorize('requestModification', $expense);

        if (!$expense->canBeModified()) {
            return redirect()->back()->with('error', 'Pengeluaran ini tidak dapat dimodifikasi saat ini.');
        }

        try {
            $proposedData = $request->validated();
            $reason = $request->input('modification_reason', '');

            $modificationRequest = $expense->requestEdit($proposedData, $reason);

            // Create approval workflow
            $this->createModificationApprovalWorkflow($modificationRequest);

            return redirect()->route('expenses.show', $expense)
                ->with('success', 'Permintaan edit pengeluaran berhasil diajukan dan menunggu persetujuan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengajukan permintaan edit: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for requesting expense deletion
     */
    public function deleteForm(ProjectExpense $expense)
    {
        $this->authorize('requestModification', $expense);

        if (!$expense->canBeModified()) {
            return redirect()->back()->with('error', 'Pengeluaran ini tidak dapat dihapus saat ini.');
        }

        return view('expense-modifications.delete-form', compact('expense'));
    }

    /**
     * Submit expense delete request
     */
    public function requestDelete(Request $request, ProjectExpense $expense)
    {
        $this->authorize('requestModification', $expense);

        $request->validate([
            'deletion_reason' => 'required|string|max:1000'
        ]);

        if (!$expense->canBeModified()) {
            return redirect()->back()->with('error', 'Pengeluaran ini tidak dapat dihapus saat ini.');
        }

        try {
            $reason = $request->input('deletion_reason');
            $modificationRequest = $expense->requestDelete($reason);

            // Create approval workflow
            $this->createModificationApprovalWorkflow($modificationRequest);

            return redirect()->route('expenses.show', $expense)
                ->with('success', 'Permintaan hapus pengeluaran berhasil diajukan dan menunggu persetujuan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengajukan permintaan hapus: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show modification request details
     */
    public function show(ExpenseModificationApproval $modification)
    {
        $this->authorize('view', $modification);

        $modification->load(['expense.project', 'requester', 'approver']);

        return view('expense-modifications.show', compact('modification'));
    }

    /**
     * Approve modification request
     */
    public function approve(Request $request, ExpenseModificationApproval $modification)
    {
        $this->authorize('approve', $modification);

        $request->validate([
            'approval_notes' => 'nullable|string|max:1000'
        ]);

        if (!$modification->isPending()) {
            return redirect()->back()->with('error', 'Permintaan modifikasi sudah diproses sebelumnya.');
        }

        try {
            $notes = $request->input('approval_notes');
            $modification->approve(Auth::user(), $notes);

            $actionText = $modification->isEditRequest() ? 'edit' : 'hapus';
            
            return redirect()->route('expense-modifications.index')
                ->with('success', "Permintaan {$actionText} pengeluaran berhasil disetujui.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menyetujui permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Reject modification request
     */
    public function reject(Request $request, ExpenseModificationApproval $modification)
    {
        $this->authorize('approve', $modification);

        $request->validate([
            'approval_notes' => 'required|string|max:1000'
        ]);

        if (!$modification->isPending()) {
            return redirect()->back()->with('error', 'Permintaan modifikasi sudah diproses sebelumnya.');
        }

        try {
            $notes = $request->input('approval_notes');
            $modification->reject(Auth::user(), $notes);

            $actionText = $modification->isEditRequest() ? 'edit' : 'hapus';
            
            return redirect()->route('expense-modifications.index')
                ->with('success', "Permintaan {$actionText} pengeluaran berhasil ditolak.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menolak permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel modification request (by requester)
     */
    public function cancel(ExpenseModificationApproval $modification)
    {
        $this->authorize('cancel', $modification);

        if (!$modification->isPending()) {
            return redirect()->back()->with('error', 'Permintaan modifikasi sudah diproses dan tidak dapat dibatalkan.');
        }

        if ($modification->requested_by !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda hanya dapat membatalkan permintaan yang Anda ajukan sendiri.');
        }

        try {
            $modification->update([
                'status' => 'rejected',
                'approval_notes' => 'Dibatalkan oleh pengaju pada ' . now()->format('Y-m-d H:i:s')
            ]);

            $actionText = $modification->isEditRequest() ? 'edit' : 'hapus';
            
            return redirect()->route('expenses.show', $modification->expense)
                ->with('success', "Permintaan {$actionText} pengeluaran berhasil dibatalkan.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membatalkan permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Get modification history for an expense
     */
    public function history(ProjectExpense $expense)
    {
        $this->authorize('view', $expense);

        $modifications = $expense->modificationApprovals()
            ->with(['requester', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('expense-modifications.history', compact('expense', 'modifications'));
    }

    /**
     * Create approval workflow for modification request
     */
    private function createModificationApprovalWorkflow(ExpenseModificationApproval $modification)
    {
        // For now, we'll use a simple approval workflow
        // In the future, this could be extended to create multiple approval levels
        
        \Log::info('Modification approval workflow created', [
            'modification_id' => $modification->id,
            'expense_id' => $modification->expense_id,
            'action_type' => $modification->action_type,
            'requested_by' => $modification->requested_by,
            'requires_high_level' => $modification->requiresHighLevelApproval()
        ]);

        // TODO: Send notifications to approvers
        // TODO: Create approval records if using multi-level approval
    }

    /**
     * Bulk approve modifications
     */
    public function bulkApprove(Request $request)
    {
        $this->authorize('bulkApprove', ExpenseModificationApproval::class);

        $request->validate([
            'modification_ids' => 'required|array',
            'modification_ids.*' => 'exists:expense_modification_approvals,id',
            'bulk_approval_notes' => 'nullable|string|max:1000'
        ]);

        $modificationIds = $request->input('modification_ids');
        $notes = $request->input('bulk_approval_notes');
        $approved = 0;
        $errors = [];

        foreach ($modificationIds as $id) {
            try {
                $modification = ExpenseModificationApproval::findOrFail($id);
                
                if ($modification->isPending()) {
                    $modification->approve(Auth::user(), $notes);
                    $approved++;
                }
            } catch (\Exception $e) {
                $errors[] = "ID {$id}: " . $e->getMessage();
            }
        }

        $message = "{$approved} permintaan berhasil disetujui.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Export modification requests
     */
    public function export(Request $request)
    {
        $this->authorize('export', ExpenseModificationApproval::class);

        // TODO: Implement export functionality
        return redirect()->back()->with('info', 'Export functionality will be implemented soon.');
    }
}