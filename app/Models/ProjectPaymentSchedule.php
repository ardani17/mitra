<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProjectPaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'termin_number',
        'total_termin',
        'termin_name',
        'percentage',
        'amount',
        'due_date',
        'created_date',
        'status',
        'billing_id',
        'description',
        'notes'
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'created_date' => 'date'
    ];

    /**
     * Relationship to Project
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relationship to ProjectBilling (when termin is billed)
     */
    public function billing(): BelongsTo
    {
        return $this->belongsTo(ProjectBilling::class, 'billing_id');
    }

    /**
     * Check if schedule is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date < now()->toDateString();
    }

    /**
     * Check if schedule is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if schedule is billed
     */
    public function isBilled(): bool
    {
        return $this->status === 'billed';
    }

    /**
     * Check if schedule is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if this is the final termin
     */
    public function isFinalTermin(): bool
    {
        return $this->termin_number === $this->total_termin;
    }

    /**
     * Get days until due date
     */
    public function getDaysUntilDue(): int
    {
        return Carbon::parse($this->due_date)->diffInDays(now(), false);
    }

    /**
     * Get status badge color for UI
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'billed' => 'bg-blue-100 text-blue-800',
            'paid' => 'bg-green-100 text-green-800',
            'overdue' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu',
            'billed' => 'Sudah Ditagih',
            'paid' => 'Lunas',
            'overdue' => 'Terlambat',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Calculate amount based on project total and percentage
     */
    public function calculateAmount(): float
    {
        if (!$this->project) {
            return 0;
        }

        $projectTotal = $this->project->calculateTotalValue();
        return $projectTotal * ($this->percentage / 100);
    }

    /**
     * Auto-update amount when percentage changes
     */
    public function updateCalculatedAmount(): void
    {
        $this->amount = $this->calculateAmount();
    }

    /**
     * Create billing from this schedule
     */
    public function createBilling(array $additionalData = []): ProjectBilling
    {
        $billingData = array_merge([
            'project_id' => $this->project_id,
            'payment_type' => 'termin',
            'termin_number' => $this->termin_number,
            'total_termin' => $this->total_termin,
            'is_final_termin' => $this->isFinalTermin(),
            'parent_schedule_id' => $this->id,
            'nilai_jasa' => $this->project->service_value ?? 0,
            'nilai_material' => $this->project->material_value ?? 0,
            'subtotal' => $this->amount,
            'ppn_rate' => 11, // Default PPN rate
            'ppn_calculation' => 'normal',
            'total_amount' => $this->amount,
            'billing_date' => now(),
            'due_date' => $this->due_date,
            'status' => 'draft',
            'notes' => "Termin {$this->termin_number} dari {$this->total_termin} - {$this->termin_name}"
        ], $additionalData);

        $billing = ProjectBilling::create($billingData);
        
        // Update schedule status and link to billing
        $this->update([
            'status' => 'billed',
            'billing_id' => $billing->id
        ]);

        return $billing;
    }

    /**
     * Boot method for auto-calculations
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($schedule) {
            // Auto-calculate amount if percentage is set
            if ($schedule->percentage && $schedule->project) {
                $schedule->updateCalculatedAmount();
            }

            // Auto-update status to overdue if past due date
            if ($schedule->status !== 'paid' && $schedule->due_date < now()->toDateString()) {
                $schedule->status = 'overdue';
            }
        });
    }

    /**
     * Scope for overdue schedules
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
                    ->where('due_date', '<', now()->toDateString());
    }

    /**
     * Scope for upcoming schedules (due within X days)
     */
    public function scopeUpcoming($query, int $days = 30)
    {
        return $query->where('status', 'pending')
                    ->where('due_date', '<=', now()->addDays($days)->toDateString())
                    ->where('due_date', '>=', now()->toDateString());
    }
}