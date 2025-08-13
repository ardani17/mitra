<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailySalary extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'work_date',
        'amount',
        'hours_worked',
        'overtime_hours',
        'overtime_amount',
        'basic_salary',
        'meal_allowance',
        'attendance_bonus',
        'phone_allowance',
        'transport_allowance',
        'attendance_status',
        'check_in_time',
        'check_out_time',
        'deductions',
        'total_amount',
        'status',
        'notes',
        'salary_release_id',
        'created_by'
    ];

    protected $casts = [
        'work_date' => 'date',
        'amount' => 'integer',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_amount' => 'integer',
        'basic_salary' => 'integer',
        'meal_allowance' => 'integer',
        'attendance_bonus' => 'integer',
        'phone_allowance' => 'integer',
        'transport_allowance' => 'integer',
        'deductions' => 'integer',
        'total_amount' => 'integer',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
        'attendance_status' => 'string',
        'status' => 'string'
    ];

    protected $dates = [
        'deleted_at'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryRelease()
    {
        return $this->belongsTo(SalaryRelease::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeUnreleased($query)
    {
        return $query->where('status', 'confirmed')->whereNull('salary_release_id');
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('work_date', [$startDate, $endDate]);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getFormattedOvertimeAmountAttribute()
    {
        return 'Rp ' . number_format($this->overtime_amount, 0, ',', '.');
    }


    public function getFormattedTotalAmountAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => '<span class="badge bg-warning">Draft</span>',
            'confirmed' => '<span class="badge bg-success">Dikonfirmasi</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    public function getIsReleasedAttribute()
    {
        return !is_null($this->salary_release_id);
    }

    // New accessors for salary components
    public function getFormattedBasicSalaryAttribute()
    {
        return 'Rp ' . number_format($this->basic_salary, 0, ',', '.');
    }

    public function getFormattedMealAllowanceAttribute()
    {
        return 'Rp ' . number_format($this->meal_allowance, 0, ',', '.');
    }

    public function getFormattedAttendanceBonusAttribute()
    {
        return 'Rp ' . number_format($this->attendance_bonus, 0, ',', '.');
    }

    public function getFormattedPhoneAllowanceAttribute()
    {
        return 'Rp ' . number_format($this->phone_allowance, 0, ',', '.');
    }

    public function getFormattedTransportAllowanceAttribute()
    {
        return 'Rp ' . number_format($this->transport_allowance, 0, ',', '.');
    }

    public function getFormattedDeductionsAttribute()
    {
        return 'Rp ' . number_format($this->deductions, 0, ',', '.');
    }

    public function getFormattedTotalAmountNewAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getAttendanceStatusBadgeAttribute()
    {
        $badges = [
            'present' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Hadir</span>',
            'late' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Telat</span>',
            'absent' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Libur</span>',
            'sick' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Sakit</span>',
            'leave' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Cuti</span>',
        ];

        return $badges[$this->attendance_status] ?? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">-</span>';
    }

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
        
        return $days[$this->work_date->format('l')] ?? '';
    }

    // Methods
    public function calculateTotalAmount()
    {
        $total = $this->basic_salary +
                $this->meal_allowance +
                $this->attendance_bonus +
                $this->phone_allowance +
                $this->transport_allowance +
                $this->overtime_amount -
                $this->deductions;
        
        $this->update(['total_amount' => $total]);
        return $total;
    }

    public function calculateAttendanceBonus($standardAmount = 0)
    {
        // Jika hadir tepat waktu, dapat bonus
        // Jika telat, dapat potongan (negatif)
        switch ($this->attendance_status) {
            case 'present':
                return $standardAmount;
            case 'late':
                return -($standardAmount / 2); // Potongan setengah dari bonus
            case 'absent':
            case 'sick':
            case 'leave':
            default:
                return 0;
        }
    }


    public function confirm()
    {
        $this->update(['status' => 'confirmed']);
    }

    public function markAsDraft()
    {
        $this->update(['status' => 'draft']);
    }
}
