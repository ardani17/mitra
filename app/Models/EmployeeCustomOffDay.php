<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class EmployeeCustomOffDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'off_date',
        'reason',
        'period_month',
        'period_year'
    ];

    protected $casts = [
        'off_date' => 'date',
        'period_month' => 'integer',
        'period_year' => 'integer'
    ];

    /**
     * Relationship with Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope for specific period (year and month)
     */
    public function scopeForPeriod($query, $year, $month)
    {
        return $query->where('period_year', $year)
                    ->where('period_month', $month);
    }

    /**
     * Scope for date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('off_date', [
            Carbon::parse($startDate)->format('Y-m-d'),
            Carbon::parse($endDate)->format('Y-m-d')
        ]);
    }

    /**
     * Scope for specific employee
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope for current month
     */
    public function scopeCurrentMonth($query)
    {
        $now = now();
        return $query->forPeriod($now->year, $now->month);
    }

    /**
     * Get formatted off date
     */
    public function getFormattedOffDateAttribute()
    {
        return $this->off_date->format('d/m/Y');
    }

    /**
     * Get day name in Indonesian
     */
    public function getDayNameAttribute()
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        
        return $days[$this->off_date->format('l')] ?? $this->off_date->format('l');
    }

    /**
     * Get period name
     */
    public function getPeriodNameAttribute()
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $months[$this->period_month] . ' ' . $this->period_year;
    }

    /**
     * Check if this off day is in the past
     */
    public function isPast()
    {
        return $this->off_date->isPast();
    }

    /**
     * Check if this off day is today
     */
    public function isToday()
    {
        return $this->off_date->isToday();
    }

    /**
     * Check if this off day is in the future
     */
    public function isFuture()
    {
        return $this->off_date->isFuture();
    }

    /**
     * Get status badge based on date
     */
    public function getStatusBadgeAttribute()
    {
        if ($this->isToday()) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Hari Ini</span>';
        } elseif ($this->isPast()) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Sudah Lewat</span>';
        } else {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Akan Datang</span>';
        }
    }

    /**
     * Get reason or default text
     */
    public function getReasonOrDefaultAttribute()
    {
        return $this->reason ?: 'Libur custom';
    }

    /**
     * Create multiple off days for an employee
     */
    public static function createMultiple($employeeId, $dates, $reason = null, $periodMonth = null, $periodYear = null)
    {
        $now = now();
        $periodMonth = $periodMonth ?: $now->month;
        $periodYear = $periodYear ?: $now->year;
        
        $created = [];
        
        foreach ($dates as $date) {
            $carbonDate = Carbon::parse($date);
            
            $offDay = static::updateOrCreate([
                'employee_id' => $employeeId,
                'off_date' => $carbonDate->format('Y-m-d')
            ], [
                'reason' => $reason,
                'period_month' => $periodMonth,
                'period_year' => $periodYear
            ]);
            
            $created[] = $offDay;
        }
        
        return collect($created);
    }

    /**
     * Delete off days for employee in date range
     */
    public static function deleteInRange($employeeId, $startDate, $endDate)
    {
        return static::forEmployee($employeeId)
                    ->inDateRange($startDate, $endDate)
                    ->delete();
    }

    /**
     * Get off days count for employee in period
     */
    public static function countForEmployeeInPeriod($employeeId, $year, $month)
    {
        return static::forEmployee($employeeId)
                    ->forPeriod($year, $month)
                    ->count();
    }

    /**
     * Boot method to auto-set period when creating
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->period_month || !$model->period_year) {
                $offDate = Carbon::parse($model->off_date);
                $model->period_month = $model->period_month ?: $offDate->month;
                $model->period_year = $model->period_year ?: $offDate->year;
            }
        });
    }
}