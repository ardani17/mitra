<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code',
        'name',
        'email',
        'phone',
        'address',
        'position',
        'department',
        'hire_date',
        'birth_date',
        'gender',
        'id_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'employment_type',
        'contract_end_date',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'daily_rate',
        'status',
        'notes',
        'avatar'
    ];

    protected $casts = [
        'hire_date' => 'date',
        'birth_date' => 'date',
        'contract_end_date' => 'date',
        'daily_rate' => 'decimal:2',
        'status' => 'string',
        'employment_type' => 'string',
        'gender' => 'string'
    ];

    protected $dates = [
        'deleted_at'
    ];

    // Relationships
    public function dailySalaries()
    {
        return $this->hasMany(DailySalary::class);
    }

    public function salaryReleases()
    {
        return $this->hasMany(SalaryRelease::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    public function scopeContractExpiringSoon($query, $days = 30)
    {
        return $query->where('employment_type', 'contract')
                    ->whereNotNull('contract_end_date')
                    ->whereBetween('contract_end_date', [now(), now()->addDays($days)]);
    }

    // Accessors
    public function getFormattedDailyRateAttribute()
    {
        return 'Rp ' . number_format($this->daily_rate, 0, ',', '.');
    }

    public function getStatusBadgeAttribute()
    {
        return $this->status === 'active'
            ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>'
            : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Tidak Aktif</span>';
    }

    public function getEmploymentTypeBadgeAttribute()
    {
        $badges = [
            'permanent' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Tetap</span>',
            'contract' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Kontrak</span>',
            'freelance' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Freelance</span>'
        ];
        
        return $badges[$this->employment_type] ?? $this->employment_type;
    }

    public function getAgeAttribute()
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    public function getWorkDurationAttribute()
    {
        return $this->hire_date->diffForHumans(null, true);
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && file_exists(storage_path('app/public/' . $this->avatar))) {
            return asset('storage/' . $this->avatar);
        }
        
        // Generate avatar with initials
        $initials = collect(explode(' ', $this->name))->map(function ($name) {
            return strtoupper(substr($name, 0, 1));
        })->take(2)->implode('');
        
        return "https://ui-avatars.com/api/?name={$initials}&color=7F9CF5&background=EBF4FF&size=200";
    }

    public function getIsContractExpiringAttribute()
    {
        if ($this->employment_type !== 'contract' || !$this->contract_end_date) {
            return false;
        }
        
        return $this->contract_end_date->isBefore(now()->addDays(30));
    }

    // Methods
    public function getTotalSalaryForPeriod($startDate, $endDate)
    {
        return $this->dailySalaries()
            ->whereBetween('work_date', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->sum('amount');
    }

    public function getUnreleasedSalaryTotal()
    {
        return $this->dailySalaries()
            ->where('status', 'confirmed')
            ->whereNull('salary_release_id')
            ->sum('amount');
    }

    public function hasUnreleasedSalaries()
    {
        return $this->dailySalaries()
            ->where('status', 'confirmed')
            ->whereNull('salary_release_id')
            ->exists();
    }

    public function getMonthlyAverageSalary($months = 3)
    {
        $startDate = now()->subMonths($months)->startOfMonth();
        
        return $this->dailySalaries()
            ->where('status', 'confirmed')
            ->where('work_date', '>=', $startDate)
            ->avg('amount') ?? 0;
    }

    public function getTotalWorkDays($startDate = null, $endDate = null)
    {
        $query = $this->dailySalaries()->where('status', 'confirmed');
        
        if ($startDate) {
            $query->where('work_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('work_date', '<=', $endDate);
        }
        
        return $query->count();
    }

    public function getPerformanceScore()
    {
        // Simple performance calculation based on attendance and salary consistency
        $totalDays = $this->getTotalWorkDays(now()->subMonths(3));
        $expectedDays = now()->subMonths(3)->diffInWeekdays(now());
        
        if ($expectedDays == 0) return 0;
        
        $attendanceScore = min(($totalDays / $expectedDays) * 100, 100);
        
        return round($attendanceScore, 1);
    }
}
