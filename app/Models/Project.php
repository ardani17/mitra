<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'client_name',
        'client_type',
        'client',
        'planned_budget',
        'planned_service_value',
        'planned_material_value',
        'planned_total_value',
        'actual_budget',
        'final_service_value',
        'final_material_value',
        'final_total_value',
        'start_date',
        'end_date',
        'status',
        'billing_status',
        'latest_po_number',
        'latest_sp_number',
        'latest_invoice_number',
        'total_billed_amount',
        'billing_percentage',
        'last_billing_date',
        'priority',
        'location',
        'notes',
    ];

    protected $casts = [
        'planned_budget' => 'decimal:2',
        'planned_service_value' => 'decimal:2',
        'planned_material_value' => 'decimal:2',
        'planned_total_value' => 'decimal:2',
        'actual_budget' => 'decimal:2',
        'final_service_value' => 'decimal:2',
        'final_material_value' => 'decimal:2',
        'final_total_value' => 'decimal:2',
        'total_billed_amount' => 'decimal:2',
        'billing_percentage' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'last_billing_date' => 'date'
    ];


    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class);
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(ProjectTimeline::class);
    }

    public function billings(): HasMany
    {
        return $this->hasMany(ProjectBilling::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(ProjectRevenue::class);
    }

    public function profitAnalyses(): HasMany
    {
        return $this->hasMany(ProjectProfitAnalysis::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    /**
     * Boot method untuk handle cascade delete
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($project) {
            // Delete related records when project is deleted
            $project->expenses()->delete();
            $project->activities()->delete();
            $project->timelines()->delete();
            $project->billings()->delete();
            $project->revenues()->delete();
            $project->profitAnalyses()->delete();
            
            // Delete documents and their files
            foreach ($project->documents as $document) {
                // Delete physical file if exists
                if ($document->file_path && \Storage::exists($document->file_path)) {
                    \Storage::delete($document->file_path);
                }
                $document->delete();
            }
        });
    }

    public function getTotalExpensesAttribute()
    {
        return $this->expenses()->where('status', 'approved')->sum('amount');
    }
    
    /**
     * Get project progress percentage based on timeline milestones
     */
    public function getProgressPercentageAttribute()
    {
        $totalTimelines = $this->timelines->count();
        
        if ($totalTimelines === 0) {
            return 0;
        }
        
        return round($this->timelines->avg('progress_percentage'), 1);
    }
    
    /**
     * Get timeline status distribution
     */
    public function getTimelineStatusDistributionAttribute()
    {
        return [
            'planned' => $this->timelines->where('status', 'planned')->count(),
            'in_progress' => $this->timelines->where('status', 'in_progress')->count(),
            'completed' => $this->timelines->where('status', 'completed')->count(),
            'delayed' => $this->timelines->where('status', 'delayed')->count(),
        ];
    }

    public function getApprovedExpensesAttribute()
    {
        return $this->expenses()->where('status', 'approved')->get();
    }

    public function getPendingExpensesAttribute()
    {
        return $this->expenses()->where('status', 'pending')->get();
    }

    public function getRejectedExpensesAttribute()
    {
        return $this->expenses()->where('status', 'rejected')->get();
    }

    public function getTotalPendingExpensesAttribute()
    {
        return $this->expenses()->where('status', 'pending')->sum('amount');
    }

    public function getNetProfitAttribute()
    {
        $totalRevenue = $this->revenues->sum('total_amount');
        return $totalRevenue - $this->total_expenses;
    }

    public function getProfitMarginAttribute()
    {
        $totalRevenue = $this->revenues->sum('total_amount');
        if ($totalRevenue > 0) {
            return ($this->net_profit / $totalRevenue) * 100;
        }
        return 0;
    }

    /**
     * Get client type label
     */
    public function getClientTypeLabelAttribute()
    {
        return match($this->client_type) {
            'wapu' => 'WAPU (BUMN/Pemerintah)',
            'non_wapu' => 'Non-WAPU (Umum)',
            default => 'Non-WAPU (Umum)'
        };
    }

    /**
     * Get client type badge color
     */
    public function getClientTypeBadgeColorAttribute()
    {
        return match($this->client_type) {
            'wapu' => 'bg-blue-100 text-blue-800',
            'non_wapu' => 'bg-green-100 text-green-800',
            default => 'bg-green-100 text-green-800'
        };
    }

    /**
     * Check if project is WAPU
     */
    public function isWapu()
    {
        return $this->client_type === 'wapu';
    }

    /**
     * Check if project is Non-WAPU
     */
    public function isNonWapu()
    {
        return $this->client_type === 'non_wapu';
    }

    // ========== BILLING INTEGRATION METHODS ==========

    /**
     * Get billing status label dalam bahasa Indonesia
     */
    public function getBillingStatusLabelAttribute()
    {
        return match($this->billing_status) {
            'not_billed' => 'Belum Ditagih',
            'partially_billed' => 'Sebagian Ditagih',
            'fully_billed' => 'Sudah Ditagih',
            default => 'Belum Ditagih'
        };
    }

    /**
     * Get billing status badge color
     */
    public function getBillingStatusBadgeColorAttribute()
    {
        return match($this->billing_status) {
            'not_billed' => 'bg-red-100 text-red-800',
            'partially_billed' => 'bg-yellow-100 text-yellow-800',
            'fully_billed' => 'bg-green-100 text-green-800',
            default => 'bg-red-100 text-red-800'
        };
    }

    /**
     * Update billing status berdasarkan total tagihan
     */
    public function updateBillingStatus(): void
    {
        $totalBilled = $this->billings()->sum('total_amount');
        $plannedValue = $this->planned_total_value ?? 0;
        
        $this->total_billed_amount = $totalBilled;
        
        if ($plannedValue > 0) {
            $this->billing_percentage = ($totalBilled / $plannedValue) * 100;
        } else {
            $this->billing_percentage = 0;
        }

        // Tentukan status berdasarkan persentase
        if ($totalBilled == 0) {
            $this->billing_status = 'not_billed';
        } elseif ($this->billing_percentage >= 100) {
            $this->billing_status = 'fully_billed';
        } else {
            $this->billing_status = 'partially_billed';
        }

        // Update dokumen tagihan terakhir
        $latestBilling = $this->billings()
            ->whereNotNull('invoice_number')
            ->orderBy('billing_date', 'desc')
            ->first();

        if ($latestBilling) {
            $this->latest_invoice_number = $latestBilling->invoice_number;
            $this->latest_sp_number = $latestBilling->sp_number;
            $this->last_billing_date = $latestBilling->billing_date;
            
            // Ambil PO number dari billing batch jika ada
            if ($latestBilling->billingBatch) {
                $this->latest_po_number = $latestBilling->billingBatch->sp_number;
            }
        }

        $this->save();
    }

    /**
     * Get latest billing documents
     */
    public function getLatestBillingDocumentsAttribute()
    {
        return [
            'po_number' => $this->latest_po_number,
            'sp_number' => $this->latest_sp_number,
            'invoice_number' => $this->latest_invoice_number,
            'billing_date' => $this->last_billing_date
        ];
    }

    /**
     * Check if project sudah ditagih penuh
     */
    public function isFullyBilled(): bool
    {
        return $this->billing_status === 'fully_billed';
    }

    /**
     * Check if project belum ditagih sama sekali
     */
    public function isNotBilled(): bool
    {
        return $this->billing_status === 'not_billed';
    }

    /**
     * Check if project sebagian ditagih
     */
    public function isPartiallyBilled(): bool
    {
        return $this->billing_status === 'partially_billed';
    }

    /**
     * Get remaining amount yang belum ditagih
     */
    public function getRemainingBillableAmountAttribute()
    {
        $plannedValue = $this->planned_total_value ?? 0;
        return $plannedValue - $this->total_billed_amount;
    }

    /**
     * Get financial summary untuk dashboard
     */
    public function getFinancialSummaryAttribute()
    {
        return [
            'planned_value' => $this->planned_total_value,
            'total_billed' => $this->total_billed_amount,
            'total_expenses' => $this->total_expenses,
            'remaining_billable' => $this->remaining_billable_amount,
            'net_profit' => $this->total_billed_amount - $this->total_expenses,
            'billing_percentage' => $this->billing_percentage
        ];
    }

    /**
     * Scope untuk filter berdasarkan billing status
     */
    public function scopeByBillingStatus($query, string $status)
    {
        return $query->where('billing_status', $status);
    }

    /**
     * Scope untuk proyek yang perlu ditagih
     */
    public function scopeNeedsBilling($query)
    {
        return $query->whereIn('billing_status', ['not_billed', 'partially_billed'])
                    ->where('status', '!=', 'cancelled');
    }

    /**
     * Scope untuk proyek yang sudah selesai tapi belum ditagih penuh
     */
    public function scopeCompletedButNotFullyBilled($query)
    {
        return $query->where('status', 'completed')
                    ->whereIn('billing_status', ['not_billed', 'partially_billed']);
    }
}
