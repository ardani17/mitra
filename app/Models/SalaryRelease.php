<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SalaryRelease extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'release_code',
        'period_start',
        'period_end',
        'total_amount',
        'deductions',
        'net_amount',
        'status',
        'notes',
        'cashflow_entry_id',
        'released_by',
        'released_at',
        'created_by',
        'paid_at'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_amount' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'status' => 'string',
        'released_at' => 'datetime'
    ];

    protected $dates = [
        'deleted_at'
    ];

    // Boot method to generate release code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->release_code)) {
                $model->release_code = $model->generateReleaseCode();
            }
        });
    }

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function dailySalaries()
    {
        return $this->hasMany(DailySalary::class);
    }

    public function cashflowEntry()
    {
        return $this->belongsTo(CashflowEntry::class);
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeReleased($query)
    {
        return $query->where('status', 'released');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('period_start', [$startDate, $endDate])
              ->orWhereBetween('period_end', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('period_start', '<=', $startDate)
                     ->where('period_end', '>=', $endDate);
              });
        });
    }

    // Accessors
    public function getFormattedTotalAmountAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getFormattedDeductionsAttribute()
    {
        return 'Rp ' . number_format($this->deductions, 0, ',', '.');
    }

    public function getFormattedNetAmountAttribute()
    {
        return 'Rp ' . number_format($this->net_amount, 0, ',', '.');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => '<span class="badge bg-warning">Draft</span>',
            'released' => '<span class="badge bg-success">Dirilis</span>',
            'paid' => '<span class="badge bg-primary">Dibayar</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    public function getPeriodLabelAttribute()
    {
        return $this->period_start->format('d/m/Y') . ' - ' . $this->period_end->format('d/m/Y');
    }

    public function getIsReleasedAttribute()
    {
        return in_array($this->status, ['released', 'paid']);
    }

    public function getIsPaidAttribute()
    {
        return $this->status === 'paid';
    }

    // Methods
    public function generateReleaseCode()
    {
        $prefix = 'SR';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        
        return $prefix . $date . $random;
    }

    public function calculateTotals()
    {
        $totalAmount = $this->dailySalaries()->sum('amount') + $this->dailySalaries()->sum('overtime_amount');
        $netAmount = $totalAmount - $this->deductions;

        $this->update([
            'total_amount' => $totalAmount,
            'net_amount' => $netAmount
        ]);

        return $this;
    }

    public function release($userId = null)
    {
        $this->update([
            'status' => 'released',
            'released_by' => $userId ?? auth()->id(),
            'released_at' => now()
        ]);

        return $this;
    }

    public function markAsPaid()
    {
        $this->update(['status' => 'paid']);
        return $this;
    }

    public function markAsDraft()
    {
        $this->update([
            'status' => 'draft',
            'released_by' => null,
            'released_at' => null
        ]);
        return $this;
    }

    public function getDailySalariesForPeriod()
    {
        return $this->employee->dailySalaries()
            ->whereBetween('work_date', [$this->period_start, $this->period_end])
            ->where('status', 'confirmed')
            ->whereNull('salary_release_id');
    }

    public function attachDailySalaries()
    {
        $dailySalaries = $this->getDailySalariesForPeriod();
        $dailySalaries->update(['salary_release_id' => $this->id]);
        
        return $this->calculateTotals();
    }

    public function detachDailySalaries()
    {
        $this->dailySalaries()->update(['salary_release_id' => null]);
        
        $this->update([
            'total_amount' => 0,
            'net_amount' => 0 - $this->deductions
        ]);

        return $this;
    }
}
