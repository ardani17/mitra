<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeCustomOffDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class EmployeeCustomOffDayController extends Controller
{
    /**
     * Display custom off days for an employee
     */
    public function index(Employee $employee, Request $request)
    {
        Gate::authorize('view', $employee);

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $offDays = $employee->customOffDays()
            ->forPeriod($year, $month)
            ->orderBy('off_date')
            ->get();

        $monthOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $yearOptions = range(2020, 2030);

        return view('employees.custom-off-days.index', compact(
            'employee', 'offDays', 'year', 'month', 'monthOptions', 'yearOptions'
        ));
    }

    /**
     * Show the form for creating new custom off days
     */
    public function create(Employee $employee, Request $request)
    {
        Gate::authorize('update', $employee);

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $monthOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $yearOptions = range(2020, 2030);

        return view('employees.custom-off-days.create', compact(
            'employee', 'year', 'month', 'monthOptions', 'yearOptions'
        ));
    }

    /**
     * Store newly created custom off days
     */
    public function store(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'off_dates' => 'required|array|min:1',
            'off_dates.*' => 'date|after_or_equal:today',
            'reason' => 'nullable|string|max:255',
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2020|max:2030'
        ]);

        $created = EmployeeCustomOffDay::createMultiple(
            $employee->id,
            $validated['off_dates'],
            $validated['reason'],
            $validated['period_month'],
            $validated['period_year']
        );

        return redirect()->route('finance.employees.custom-off-days.index', [
            'employee' => $employee,
            'year' => $validated['period_year'],
            'month' => $validated['period_month']
        ])->with('success', "Berhasil menambahkan {$created->count()} hari libur custom.");
    }

    /**
     * Display the specified custom off day
     */
    public function show(Employee $employee, EmployeeCustomOffDay $customOffDay)
    {
        Gate::authorize('view', $employee);

        // Ensure the custom off day belongs to the employee
        if ($customOffDay->employee_id !== $employee->id) {
            abort(404);
        }

        return view('employees.custom-off-days.show', compact('employee', 'customOffDay'));
    }

    /**
     * Show the form for editing the specified custom off day
     */
    public function edit(Employee $employee, EmployeeCustomOffDay $customOffDay)
    {
        Gate::authorize('update', $employee);

        // Ensure the custom off day belongs to the employee
        if ($customOffDay->employee_id !== $employee->id) {
            abort(404);
        }

        $monthOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $yearOptions = range(2020, 2030);

        return view('employees.custom-off-days.edit', compact(
            'employee', 'customOffDay', 'monthOptions', 'yearOptions'
        ));
    }

    /**
     * Update the specified custom off day
     */
    public function update(Request $request, Employee $employee, EmployeeCustomOffDay $customOffDay)
    {
        Gate::authorize('update', $employee);

        // Ensure the custom off day belongs to the employee
        if ($customOffDay->employee_id !== $employee->id) {
            abort(404);
        }

        $validated = $request->validate([
            'off_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2020|max:2030'
        ]);

        $customOffDay->update($validated);

        return redirect()->route('finance.employees.custom-off-days.show', [$employee, $customOffDay])
            ->with('success', 'Hari libur custom berhasil diperbarui.');
    }

    /**
     * Remove the specified custom off day
     */
    public function destroy(Employee $employee, EmployeeCustomOffDay $customOffDay)
    {
        Gate::authorize('update', $employee);

        // Ensure the custom off day belongs to the employee
        if ($customOffDay->employee_id !== $employee->id) {
            abort(404);
        }

        $customOffDay->delete();

        return redirect()->route('finance.employees.custom-off-days.index', $employee)
            ->with('success', 'Hari libur custom berhasil dihapus.');
    }

    /**
     * Bulk delete custom off days for a period
     */
    public function bulkDelete(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2020|max:2030'
        ]);

        $deleted = $employee->customOffDays()
            ->forPeriod($validated['period_year'], $validated['period_month'])
            ->delete();

        return redirect()->back()
            ->with('success', "Berhasil menghapus {$deleted} hari libur custom.");
    }

    /**
     * Get calendar view for custom off days
     */
    public function calendar(Employee $employee, Request $request)
    {
        Gate::authorize('view', $employee);

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // Get all days in the month
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        // Get custom off days for the month
        $offDays = $employee->customOffDays()
            ->forPeriod($year, $month)
            ->get()
            ->keyBy(function($item) {
                return $item->off_date->day;
            });

        // Build calendar array
        $calendar = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $calendar[] = [
                'date' => $date,
                'day_number' => $day,
                'day_name' => $this->getDayName($date->format('l')),
                'is_weekend' => $date->isWeekend(),
                'is_today' => $date->isToday(),
                'custom_off_day' => $offDays->get($day),
                'has_custom_off' => $offDays->has($day)
            ];
        }

        $monthOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $yearOptions = range(2020, 2030);

        return view('employees.custom-off-days.calendar', compact(
            'employee', 'calendar', 'year', 'month', 'monthOptions', 'yearOptions'
        ));
    }

    /**
     * Quick add custom off day via AJAX
     */
    public function quickAdd(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'off_date' => 'required|date',
            'reason' => 'nullable|string|max:255'
        ]);

        $date = Carbon::parse($validated['off_date']);
        
        $customOffDay = EmployeeCustomOffDay::updateOrCreate([
            'employee_id' => $employee->id,
            'off_date' => $date->format('Y-m-d')
        ], [
            'reason' => $validated['reason'] ?: 'Libur custom',
            'period_month' => $date->month,
            'period_year' => $date->year
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Hari libur custom berhasil ditambahkan',
                'data' => $customOffDay
            ]);
        }

        return redirect()->back()
            ->with('success', 'Hari libur custom berhasil ditambahkan.');
    }

    /**
     * Quick remove custom off day via AJAX
     */
    public function quickRemove(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'off_date' => 'required|date'
        ]);

        $deleted = $employee->customOffDays()
            ->where('off_date', $validated['off_date'])
            ->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Hari libur custom berhasil dihapus',
                'deleted' => $deleted
            ]);
        }

        return redirect()->back()
            ->with('success', 'Hari libur custom berhasil dihapus.');
    }

    /**
     * Get day name in Indonesian
     */
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
}