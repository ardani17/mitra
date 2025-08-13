<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class CashflowEntry extends Model
{
    protected $fillable = [
        'reference_type',
        'reference_id',
        'project_id',
        'category_id',
        'transaction_date',
        'description',
        'amount',
        'type',
        'payment_method',
        'account_code',
        'notes',
        'created_by',
        'status',
        'confirmed_at',
        'confirmed_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'confirmed_at' => 'datetime'
    ];

    /**
     * Get the project associated with this cashflow entry
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the category associated with this cashflow entry
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CashflowCategory::class);
    }

    /**
     * Get the user who created this entry
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who confirmed this entry
     */
    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Get the referenced model (polymorphic-like behavior)
     */
    public function getReferencedModelAttribute()
    {
        if (!$this->reference_id || !$this->reference_type) {
            return null;
        }

        switch ($this->reference_type) {
            case 'billing':
                return ProjectBilling::find($this->reference_id);
            case 'expense':
                return ProjectExpense::find($this->reference_id);
            default:
                return null;
        }
    }

    /**
     * Scope untuk income entries
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope untuk expense entries
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope untuk confirmed entries
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope untuk pending entries
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk date range
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope untuk project
     */
    public function scopeForProject(Builder $query, $projectId): Builder
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope untuk category
     */
    public function scopeForCategory(Builder $query, $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope untuk reference type
     */
    public function scopeReferenceType(Builder $query, $type): Builder
    {
        return $query->where('reference_type', $type);
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get formatted type
     */
    public function getFormattedTypeAttribute(): string
    {
        return $this->type === 'income' ? 'Pemasukan' : 'Pengeluaran';
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'cancelled' => 'Dibatalkan',
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
            'confirmed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Check if entry can be edited
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'pending' || $this->reference_type === 'manual';
    }

    /**
     * Check if entry can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->reference_type === 'manual' && $this->status !== 'confirmed';
    }

    /**
     * Confirm the entry
     */
    public function confirm(User $user = null): bool
    {
        $this->status = 'confirmed';
        $this->confirmed_at = now();
        $this->confirmed_by = $user ? $user->id : auth()->id();
        
        return $this->save();
    }

    /**
     * Cancel the entry
     */
    public function cancel(): bool
    {
        $this->status = 'cancelled';
        return $this->save();
    }

    /**
     * Create entry from billing
     */
    public static function createFromBilling(ProjectBilling $billing): self
    {
        $category = CashflowCategory::getSystemCategory('INC_PROJECT_BILLING');
        
        return static::create([
            'reference_type' => 'billing',
            'reference_id' => $billing->id,
            'project_id' => $billing->project_id,
            'category_id' => $category->id,
            'transaction_date' => now()->toDateString(),
            'description' => "Pembayaran penagihan proyek: {$billing->project->name}",
            'amount' => $billing->total_amount,
            'type' => 'income',
            'payment_method' => 'bank_transfer',
            'created_by' => auth()->id(),
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => auth()->id()
        ]);
    }

    /**
     * Create entry from expense
     */
    public static function createFromExpense(ProjectExpense $expense): self
    {
        $category = CashflowCategory::getSystemCategory('EXP_PROJECT');
        
        return static::create([
            'reference_type' => 'expense',
            'reference_id' => $expense->id,
            'project_id' => $expense->project_id,
            'category_id' => $category->id,
            'transaction_date' => $expense->expense_date->toDateString(),
            'description' => "Pengeluaran proyek: {$expense->description}",
            'amount' => $expense->amount,
            'type' => 'expense',
            'created_by' => $expense->user_id,
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => auth()->id()
        ]);
    }

    /**
     * Get balance for date range
     */
    public static function getBalance($startDate = null, $endDate = null): array
    {
        $query = static::confirmed();
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }
        
        $income = $query->clone()->income()->sum('amount');
        $expense = $query->clone()->expense()->sum('amount');
        
        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense
        ];
    }

    /**
     * Get monthly summary
     */
    public static function getMonthlySummary(int $year = null): \Illuminate\Support\Collection
    {
        $year = $year ?? now()->year;
        
        return static::confirmed()
            ->whereYear('transaction_date', $year)
            ->selectRaw('
                MONTH(transaction_date) as month,
                type,
                SUM(amount) as total
            ')
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get()
            ->groupBy('month')
            ->map(function ($monthData) {
                $income = $monthData->where('type', 'income')->sum('total');
                $expense = $monthData->where('type', 'expense')->sum('total');
                
                return [
                    'income' => $income,
                    'expense' => $expense,
                    'balance' => $income - $expense
                ];
            });
    }
}
