<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectExpense extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'description',
        'amount',
        'expense_date',
        'category',
        'receipt_number',
        'vendor',
        'notes',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ExpenseApproval::class, 'expense_id');
    }

    public function modificationApprovals(): HasMany
    {
        return $this->hasMany(ExpenseModificationApproval::class, 'expense_id');
    }

    public function pendingModifications(): HasMany
    {
        return $this->hasMany(ExpenseModificationApproval::class, 'expense_id')
            ->where('status', 'pending');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function hasPendingModifications(): bool
    {
        return $this->pendingModifications()->exists();
    }

    public function canBeModified(): bool
    {
        // Only approved expenses can be modified through approval workflow
        // And they shouldn't have pending modifications
        return $this->isApproved() && !$this->hasPendingModifications();
    }

    public function canBeDirectlyEdited(): bool
    {
        // Draft and pending expenses can be edited directly
        return in_array($this->status, ['draft', 'pending']);
    }

    public function canBeDirectlyDeleted(): bool
    {
        // Only draft expenses can be deleted directly
        return $this->status === 'draft';
    }

    public function getLatestModificationRequest(): ?ExpenseModificationApproval
    {
        return $this->modificationApprovals()
            ->latest()
            ->first();
    }

    public function createModificationRequest(string $actionType, array $proposedData = null, string $reason = '', User $requester = null): ExpenseModificationApproval
    {
        $requester = $requester ?? auth()->user();
        
        return $this->modificationApprovals()->create([
            'action_type' => $actionType,
            'requested_by' => $requester->id,
            'original_data' => $this->toArray(),
            'proposed_data' => $proposedData,
            'reason' => $reason,
            'status' => 'pending'
        ]);
    }

    public function requestEdit(array $proposedData, string $reason = '', User $requester = null): ExpenseModificationApproval
    {
        if (!$this->canBeModified()) {
            throw new \Exception('Expense cannot be modified at this time');
        }

        return $this->createModificationRequest('edit', $proposedData, $reason, $requester);
    }

    public function requestDelete(string $reason = '', User $requester = null): ExpenseModificationApproval
    {
        if (!$this->canBeModified()) {
            throw new \Exception('Expense cannot be deleted at this time');
        }

        return $this->createModificationRequest('delete', null, $reason, $requester);
    }
}
