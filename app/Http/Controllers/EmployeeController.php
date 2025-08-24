<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\SalaryPeriodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller
{
    protected $salaryPeriodService;

    public function __construct(SalaryPeriodService $salaryPeriodService)
    {
        $this->salaryPeriodService = $salaryPeriodService;
    }

    /**
     * Display employee dashboard
     */
    public function dashboard()
    {
        Gate::authorize('viewAny', Employee::class);
        
        return view('employees.dashboard');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Employee::class);

        $query = Employee::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Department filter
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // Employment type filter
        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        // Contract expiring filter
        if ($request->filled('contract_expiring')) {
            $query->contractExpiringSoon();
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $allowedSorts = ['name', 'employee_code', 'hire_date', 'daily_rate', 'department'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('name');
        }

        $employees = $query->paginate(15);

        // Get unique departments and employment types for filters
        $departments = Employee::distinct()->pluck('department')->filter()->sort();
        $employmentTypes = Employee::distinct()->pluck('employment_type')->filter()->sort();

        // Get statistics
        $stats = [
            'total' => Employee::count(),
            'active' => Employee::active()->count(),
            'inactive' => Employee::inactive()->count(),
            'salary_budget' => Employee::getTotalUnreleasedSalaryBudget(),
        ];

        // Get salary status for all employees
        try {
            $salaryStatuses = $this->salaryPeriodService->getAllEmployeesSalaryStatus();
            $salaryStatusesKeyed = $salaryStatuses->keyBy('employee_id');
        } catch (\Exception $e) {
            \Log::error('Error getting salary statuses: ' . $e->getMessage());
            $salaryStatusesKeyed = collect();
        }

        return view('employees.index', compact('employees', 'departments', 'employmentTypes', 'stats', 'salaryStatusesKeyed'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Employee::class);

        return view('employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Employee store method called', ['request_data' => $request->all()]);
        
        try {
            Gate::authorize('create', Employee::class);
        } catch (\Exception $e) {
            \Log::error('Authorization failed', ['error' => $e->getMessage()]);
            return redirect()->route('finance.employees.index')
                ->with('error', 'Anda tidak memiliki izin untuk menambah karyawan.');
        }

        // Fix contract_end_date validation
        $rules = [
            'employee_code' => 'required|string|max:20|unique:employees',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:employees',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'hire_date' => 'required|date',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female',
            'id_number' => 'nullable|string|max:50|unique:employees',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string|max:50',
            'employment_type' => 'required|in:permanent,contract,freelance',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
            'daily_rate' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ];

        // Add contract_end_date validation only if employment_type is contract
        if ($request->employment_type === 'contract') {
            $rules['contract_end_date'] = 'required|date|after:hire_date';
        } else {
            $rules['contract_end_date'] = 'nullable|date';
        }

        try {
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

        try {
            $employee = Employee::create($validated);
            \Log::info('Employee created successfully', ['employee_id' => $employee->id]);
        } catch (\Exception $e) {
            \Log::error('Failed to create employee', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan karyawan: ' . $e->getMessage());
        }

        return redirect()->route('finance.employees.show', $employee)
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee, Request $request)
    {
        Gate::authorize('view', $employee);

        // Get month and year from request, default to current month
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        
        // Validate year and month
        if ($year < 2020 || $year > 2030) {
            $year = now()->year;
        }
        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        
        // Create date range for the selected month using Carbon::create for accuracy
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        
        // Get all days in the month
        $daysInMonth = $startDate->daysInMonth;
        $monthlyCalendar = [];
        
        // Get existing daily salaries for the month
        $existingSalaries = $employee->dailySalaries()
            ->whereBetween('work_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->keyBy(function ($salary) {
                return $salary->work_date->day;
            });
        
        // Build calendar array with all days - using proper date creation
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = \Carbon\Carbon::create($year, $month, $day);
            $dayData = [
                'date' => $date,
                'day_number' => $day,
                'day_name' => $this->getDayName($date->format('l')),
                'is_weekend' => $date->isWeekend(),
                'salary' => $existingSalaries->get($day) // null if no salary record
            ];
            $monthlyCalendar[] = $dayData;
        }
        
        // Calculate monthly statistics
        $monthlyStats = [
            'total_work_days' => $existingSalaries->count(),
            'total_basic_salary' => $existingSalaries->sum('basic_salary'),
            'total_allowances' => $existingSalaries->sum(function ($salary) {
                return $salary->meal_allowance + $salary->attendance_bonus +
                       $salary->phone_allowance + $salary->transport_allowance;
            }),
            'total_overtime' => $existingSalaries->sum('overtime_amount'),
            'total_deductions' => $existingSalaries->sum('deductions'),
            'total_earnings' => $existingSalaries->sum('total_amount'),
            'average_daily' => $existingSalaries->count() > 0 ? $existingSalaries->avg('total_amount') : 0,
            'present_days' => $existingSalaries->where('attendance_status', 'present')->count(),
            'late_days' => $existingSalaries->where('attendance_status', 'late')->count(),
            'absent_days' => $existingSalaries->where('attendance_status', 'absent')->count(),
        ];
        
        // Generate year options for custom selection
        $yearOptions = [];
        for ($y = 2020; $y <= 2030; $y++) {
            $yearOptions[] = $y;
        }
        
        // Generate month options
        $monthOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        // Get current month name for display
        $currentMonthName = $monthOptions[$month];

        return view('employees.show', compact(
            'employee',
            'monthlyCalendar',
            'monthlyStats',
            'yearOptions',
            'monthOptions',
            'currentMonthName',
            'year',
            'month',
            'startDate',
            'endDate'
        ));
    }
    
    private function getDayName($englishDay)
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
        
        return $days[$englishDay] ?? $englishDay;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        Gate::authorize('update', $employee);

        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'employee_code' => 'required|string|max:20|unique:employees,employee_code,' . $employee->id,
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'hire_date' => 'required|date',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female',
            'id_number' => 'nullable|string|max:50|unique:employees,id_number,' . $employee->id,
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string|max:50',
            'employment_type' => 'required|in:permanent,contract,freelance',
            'contract_end_date' => 'nullable|date|after:hire_date|required_if:employment_type,contract',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
            'daily_rate' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($employee->avatar && file_exists(storage_path('app/public/' . $employee->avatar))) {
                unlink(storage_path('app/public/' . $employee->avatar));
            }
            
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

        $employee->update($validated);

        return redirect()->route('finance.employees.show', $employee)
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        Gate::authorize('delete', $employee);

        // Check if employee has salary records
        if ($employee->dailySalaries()->exists()) {
            return redirect()->route('finance.employees.index')
                ->with('error', 'Tidak dapat menghapus karyawan yang memiliki catatan gaji.');
        }

        $employee->delete();

        return redirect()->route('finance.employees.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }

    /**
     * Get employee salary summary
     */
    public function salarySummary(Employee $employee)
    {
        Gate::authorize('view', $employee);

        $year = request('year', now()->year);
        $month = request('month', now()->month);

        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $dailySalaries = $employee->dailySalaries()
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date')
            ->get();

        $totalSalary = $dailySalaries->sum('amount');
        $totalOvertime = $dailySalaries->sum('overtime_amount');
        $workDays = $dailySalaries->where('status', 'confirmed')->count();

        return response()->json([
            'daily_salaries' => $dailySalaries,
            'summary' => [
                'total_salary' => $totalSalary,
                'total_overtime' => $totalOvertime,
                'total_amount' => $totalSalary + $totalOvertime,
                'work_days' => $workDays,
                'period' => $startDate->format('F Y')
            ]
        ]);
    }

    /**
     * Export employees to Excel
     */
    public function export(Request $request)
    {
        Gate::authorize('viewAny', Employee::class);

        $query = Employee::query();

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        $employees = $query->orderBy('name')->get();

        return response()->json([
            'data' => $employees->map(function ($employee) {
                return [
                    'Kode Karyawan' => $employee->employee_code,
                    'Nama' => $employee->name,
                    'Email' => $employee->email,
                    'Telepon' => $employee->phone,
                    'Posisi' => $employee->position,
                    'Departemen' => $employee->department,
                    'Tanggal Masuk' => $employee->hire_date->format('d/m/Y'),
                    'Tanggal Lahir' => $employee->birth_date ? $employee->birth_date->format('d/m/Y') : '',
                    'Jenis Kelamin' => $employee->gender ? ucfirst($employee->gender) : '',
                    'Tipe Karyawan' => ucfirst($employee->employment_type),
                    'Gaji Harian' => $employee->daily_rate,
                    'Status' => ucfirst($employee->status),
                    'Kontak Darurat' => $employee->emergency_contact_name,
                    'Telepon Darurat' => $employee->emergency_contact_phone,
                    'Bank' => $employee->bank_name,
                    'No. Rekening' => $employee->bank_account_number,
                    'Catatan' => $employee->notes
                ];
            })
        ]);
    }


    /**
     * Get employee analytics
     */
    public function analytics()
    {
        Gate::authorize('viewAny', Employee::class);

        $analytics = [
            'total_employees' => Employee::count(),
            'active_employees' => Employee::active()->count(),
            'inactive_employees' => Employee::inactive()->count(),
            'contract_expiring' => Employee::contractExpiringSoon()->count(),
            'by_department' => Employee::selectRaw('department, COUNT(*) as count')
                ->groupBy('department')
                ->pluck('count', 'department'),
            'by_employment_type' => Employee::selectRaw('employment_type, COUNT(*) as count')
                ->groupBy('employment_type')
                ->pluck('count', 'employment_type'),
            'recent_hires' => Employee::where('hire_date', '>=', now()->subDays(30))->count(),
            'average_daily_rate' => Employee::avg('daily_rate'),
        ];

        return response()->json($analytics);
    }

    /**
     * Display employee documents
     */
    public function documents(Employee $employee)
    {
        Gate::authorize('view', $employee);

        $documents = collect(); // This would typically load from a documents table
        
        return view('employees.documents', compact('employee', 'documents'));
    }

    /**
     * Upload employee document
     */
    public function uploadDocument(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'document_type' => 'required|string|max:100',
            'description' => 'nullable|string|max:255'
        ]);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('employee_documents/' . $employee->id, $filename, 'public');

            // Here you would typically save to a documents table
            // For now, we'll just return success
        }

        return redirect()->route('finance.employees.documents', $employee)
            ->with('success', 'Dokumen berhasil diunggah.');
    }

    /**
     * Delete employee document
     */
    public function deleteDocument(Employee $employee, $documentId)
    {
        Gate::authorize('update', $employee);

        // Here you would typically delete from documents table and storage
        // For now, we'll just return success
        
        return redirect()->route('finance.employees.documents', $employee)
            ->with('success', 'Dokumen berhasil dihapus.');
    }

    /**
     * Generate comprehensive employee reports
     */
    public function reports(Request $request)
    {
        Gate::authorize('viewAny', Employee::class);

        $reportType = $request->get('type', 'summary');
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $department = $request->get('department');
        $employmentType = $request->get('employment_type');

        $query = Employee::query();

        // Apply filters
        if ($department) {
            $query->where('department', $department);
        }

        if ($employmentType) {
            $query->where('employment_type', $employmentType);
        }

        $employees = $query->with(['dailySalaries', 'salaryReleases'])->get();

        // Generate report data based on type
        $reportData = [];
        
        switch ($reportType) {
            case 'summary':
                $reportData = $this->generateSummaryReport($employees, $startDate, $endDate);
                break;
            case 'salary':
                $reportData = $this->generateSalaryReport($employees, $startDate, $endDate);
                break;
            case 'attendance':
                $reportData = $this->generateAttendanceReport($employees, $startDate, $endDate);
                break;
            case 'performance':
                $reportData = $this->generatePerformanceReport($employees, $startDate, $endDate);
                break;
        }

        // Get filter options
        $departments = Employee::distinct()->pluck('department')->filter()->sort();
        $employmentTypes = Employee::distinct()->pluck('employment_type')->filter()->sort();

        return view('employees.reports', compact(
            'reportData',
            'reportType',
            'startDate',
            'endDate',
            'department',
            'employmentType',
            'departments',
            'employmentTypes'
        ));
    }

    private function generateSummaryReport($employees, $startDate, $endDate)
    {
        return [
            'total_employees' => $employees->count(),
            'active_employees' => $employees->where('status', 'active')->count(),
            'inactive_employees' => $employees->where('status', 'inactive')->count(),
            'by_department' => $employees->groupBy('department')->map->count(),
            'by_employment_type' => $employees->groupBy('employment_type')->map->count(),
            'average_daily_rate' => $employees->avg('daily_rate'),
            'total_daily_rate' => $employees->sum('daily_rate'),
            'contract_expiring' => $employees->filter(function ($employee) {
                return $employee->is_contract_expiring;
            })->count(),
            'employees' => $employees
        ];
    }

    private function generateSalaryReport($employees, $startDate, $endDate)
    {
        $data = [];
        
        foreach ($employees as $employee) {
            $salaryData = $employee->dailySalaries()
                ->whereBetween('work_date', [$startDate, $endDate])
                ->where('status', 'confirmed')
                ->get();

            $data[] = [
                'employee' => $employee,
                'total_days' => $salaryData->count(),
                'total_salary' => $salaryData->sum('amount'),
                'total_overtime' => $salaryData->sum('overtime_amount'),
                'average_daily' => $salaryData->avg('amount') ?: 0,
                'released_salary' => $employee->salaryReleases()
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('net_amount')
            ];
        }

        return $data;
    }

    private function generateAttendanceReport($employees, $startDate, $endDate)
    {
        $data = [];
        $totalWorkDays = now()->parse($startDate)->diffInWeekdays(now()->parse($endDate)) + 1;
        
        foreach ($employees as $employee) {
            $workDays = $employee->dailySalaries()
                ->whereBetween('work_date', [$startDate, $endDate])
                ->where('status', 'confirmed')
                ->count();

            $attendanceRate = $totalWorkDays > 0 ? ($workDays / $totalWorkDays) * 100 : 0;

            $data[] = [
                'employee' => $employee,
                'work_days' => $workDays,
                'total_possible_days' => $totalWorkDays,
                'attendance_rate' => round($attendanceRate, 1),
                'absent_days' => $totalWorkDays - $workDays,
                'overtime_hours' => $employee->dailySalaries()
                    ->whereBetween('work_date', [$startDate, $endDate])
                    ->sum('overtime_hours')
            ];
        }

        return $data;
    }

    private function generatePerformanceReport($employees, $startDate, $endDate)
    {
        $data = [];
        
        foreach ($employees as $employee) {
            $performanceScore = $employee->getPerformanceScore();
            $avgSalary = $employee->getMonthlyAverageSalary();
            
            $data[] = [
                'employee' => $employee,
                'performance_score' => $performanceScore,
                'average_monthly_salary' => $avgSalary,
                'work_duration' => $employee->work_duration,
                'total_work_days' => $employee->getTotalWorkDays($startDate, $endDate),
                'rating' => $this->getPerformanceRating($performanceScore)
            ];
        }

        // Sort by performance score
        usort($data, function ($a, $b) {
            return $b['performance_score'] <=> $a['performance_score'];
        });

        return $data;
    }

    private function getPerformanceRating($score)
    {
        if ($score >= 90) return 'Excellent';
        if ($score >= 80) return 'Good';
        if ($score >= 70) return 'Average';
        if ($score >= 60) return 'Below Average';
        return 'Poor';
    }

}
