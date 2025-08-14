<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ExpenseModificationApproval extends Model
{
    protected $fillable = [
        'expense_id',
        'action_type',
        'requested_by',
        'original_data',
        'proposed_data',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes'
    ];

    protected $casts = [
        'original_data' => 'array',
        'proposed_data' => 'array',
        'approved_at' => 'datetime'
    ];

    /**
     * Get the expense that this modification request belongs to
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(ProjectExpense::class, 'expense_id');
    }

    /**
     * Get the user who requested the modification
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved/rejected the modification
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for pending approvals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for edit requests
     */
    public function scopeEditRequests($query)
    {
        return $query->where('action_type', 'edit');
    }

    /**
     * Scope for delete requests
     */
    public function scopeDeleteRequests($query)
    {
        return $query->where('action_type', 'delete');
    }

    /**
     * Check if this is an edit request
     */
    public function isEditRequest(): bool
    {
        return $this->action_type === 'edit';
    }

    /**
     * Check if this is a delete request
     */
    public function isDeleteRequest(): bool
    {
        return $this->action_type === 'delete';
    }

    /**
     * Check if the request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get formatted action type
     */
    public function getFormattedActionTypeAttribute(): string
    {
        return match($this->action_type) {
            'edit' => 'Edit Pengeluaran',
            'delete' => 'Hapus Pengeluaran',
            default => ucfirst($this->action_type)
        };
    }

    /**
     * Get action type badge class
     */
    public function getActionTypeBadgeClassAttribute(): string
    {
        return match($this->action_type) {
            'edit' => 'bg-blue-100 text-blue-800',
            'delete' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Approve the modification request
     */
    public function approve(User $approver = null, string $notes = null): bool
    {
        $approver = $approver ?? Auth::user();
        
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes
        ]);

        // Apply the changes if this is an edit request
        if ($this->isEditRequest() && $this->proposed_data) {
            $this->applyChanges();
        }

        // Delete the expense if this is a delete request
        if ($this->isDeleteRequest()) {
            $this->deleteExpense();
        }

        return true;
    }

    /**
     * Reject the modification request
     */
    public function reject(User $approver = null, string $notes = null): bool
    {
        $approver = $approver ?? Auth::user();
        
        return $this->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes
        ]);
    }

    /**
     * Apply the proposed changes to the expense
     */
    private function applyChanges(): void
    {
        if (!$this->proposed_data || !$this->expense) {
            return;
        }

        // Update the expense with proposed data
        $this->expense->update($this->proposed_data);

        // Log the modification
        \Log::info('Expense modification applied', [
            'expense_id' => $this->expense_id,
            'modification_id' => $this->id,
            'changes' => $this->proposed_data,
            'approved_by' => $this->approved_by
        ]);
    }

    /**
     * Delete the expense (soft delete)
     */
    private function deleteExpense(): void
    {
        if (!$this->expense) {
            return;
        }

        // Soft delete the expense
        $this->expense->delete();

        // Log the deletion
        \Log::info('Expense deleted via approval', [
            'expense_id' => $this->expense_id,
            'modification_id' => $this->id,
            'approved_by' => $this->approved_by
        ]);
    }

    /**
     * Get changes summary for display
     */
    public function getChangesSummary(): array
    {
        if (!$this->isEditRequest() || !$this->original_data || !$this->proposed_data) {
            return [];
        }

        $changes = [];
        $original = $this->original_data;
        $proposed = $this->proposed_data;

        foreach ($proposed as $field => $newValue) {
            $oldValue = $original[$field] ?? null;
            
            if ($oldValue != $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                    'field_name' => $this->getFieldDisplayName($field)
                ];
            }
        }

        return $changes;
    }

    /**
     * Get display name for field
     */
    private function getFieldDisplayName(string $field): string
    {
        return match($field) {
            'description' => 'Deskripsi',
            'amount' => 'Jumlah',
            'expense_date' => 'Tanggal Pengeluaran',
            'category' => 'Kategori',
            'receipt_number' => 'Nomor Kuitansi',
            'vendor' => 'Vendor',
            'notes' => 'Catatan',
            default => ucfirst(str_replace('_', ' ', $field))
        };
    }

    /**
     * Check if the modification requires high-level approval
     */
    public function requiresHighLevelApproval(): bool
    {
        // Delete requests always require high-level approval
        if ($this->isDeleteRequest()) {
            return true;
        }

        // Check if amount change is significant (>50%)
        if ($this->isEditRequest() && $this->original_data && $this->proposed_data) {
            $originalAmount = $this->original_data['amount'] ?? 0;
            $proposedAmount = $this->proposed_data['amount'] ?? 0;
            
            if ($originalAmount > 0) {
                $changePercentage = abs(($proposedAmount - $originalAmount) / $originalAmount) * 100;
                if ($changePercentage > 50) {
                    return true;
                }
            }
        }

        // Check if expense amount is above threshold
        $expenseAmount = $this->expense?->amount ?? 0;
        if ($expenseAmount > 10000000) { // 10 million
            return true;
        }

        return false;
    }

    /**
     * Get required approval levels
     */
    public function getRequiredApprovalLevels(): array
    {
        $levels = ['finance_manager'];

        if ($this->requiresHighLevelApproval()) {
            $levels[] = 'direktur';
        } else {
            $levels[] = 'project_manager';
        }

        return $levels;
    }
}