<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class EmployeeWorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'schedule_type',
        'work_days_per_month',
        'standard_off_days',
        'effective_from',
        'effective_until',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'standard_off_days' => 'array',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
        'work_days_per_month' => 'integer'
    ];

    /**
     * Relationship with Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope for active schedules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for schedules effective on a specific date
     */
    public function scopeForPeriod($query, $date)
    {
        $date = Carbon::parse($date);
        return $query->where('effective_from', '<=', $date)
                    ->where(function($q) use ($date) {
                        $q->whereNull('effective_until')
                          ->orWhere('effective_until', '>=', $date);
                    });
    }

    /**
     * Scope for specific schedule type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('schedule_type', $type);
    }

    /**
     * Get schedule type badge for display
     */
    public function getScheduleTypeBadgeAttribute()
    {
        $badges = [
            'standard' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Standard</span>',
            'custom' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Custom</span>',
            'flexible' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Flexible</span>'
        ];
        
        return $badges[$this->schedule_type] ?? $this->schedule_type;
    }

    /**
     * Get formatted off days for display
     */
    public function getFormattedOffDaysAttribute()
    {
        if (!$this->standard_off_days) {
            return '-';
        }

        $dayNames = [
            0 => 'Minggu',
            1 => 'Senin', 
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu'
        ];

        $offDayNames = collect($this->standard_off_days)
            ->map(fn($day) => $dayNames[$day] ?? $day)
            ->join(', ');

        return $offDayNames ?: '-';
    }

    /**
     * Get effective period description
     */
    public function getEffectivePeriodAttribute()
    {
        $from = $this->effective_from->format('d/m/Y');
        $until = $this->effective_until ? $this->effective_until->format('d/m/Y') : 'Sekarang';
        
        return "{$from} - {$until}";
    }

    /**
     * Check if schedule is currently active
     */
    public function isCurrentlyActive()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->effective_from > $now) {
            return false;
        }

        if ($this->effective_until && $this->effective_until < $now) {
            return false;
        }

        return true;
    }

    /**
     * Get description for schedule type
     */
    public function getScheduleDescription()
    {
        switch ($this->schedule_type) {
            case 'standard':
                return 'Jadwal kerja standar dengan hari libur tetap: ' . $this->formatted_off_days;
                
            case 'custom':
                return 'Jadwal kerja custom dengan hari libur: ' . $this->formatted_off_days;
                
            case 'flexible':
                return "Jadwal kerja fleksibel dengan target {$this->work_days_per_month} hari kerja per bulan";
                
            default:
                return 'Jadwal kerja tidak diketahui';
        }
    }

    /**
     * Deactivate this schedule
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Activate this schedule and deactivate others for the same employee
     */
    public function activate()
    {
        // Deactivate other schedules for this employee
        static::where('employee_id', $this->employee_id)
              ->where('id', '!=', $this->id)
              ->update(['is_active' => false]);
        
        // Activate this schedule
        $this->update(['is_active' => true]);
    }
}