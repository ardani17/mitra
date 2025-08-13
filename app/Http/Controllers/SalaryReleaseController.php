<?php

namespace App\Http\Controllers;

use App\Models\SalaryRelease;
use App\Models\Employee;
use App\Models\DailySalary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalaryReleaseController extends Controller
{
    // Constructor removed - middleware handled by routes

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', SalaryRelease::class);

        $query = SalaryRelease::with(['employee', 'releasedBy']);

        // Date range filter
        if ($request->filled('start_date')) {
            $query->where('period_start', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('period_end', '<=', $request->end_date);
        }

        // Employee filter
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $salaryReleases = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get employees for filter
        $employees = Employee::active()->orderBy('name')->get();

        // Calculate summary
        $summary = [
            'total_releases' => $query->count(),
            'total_amount' => $query->sum('net_amount'),
            'draft_amount' => $query->where('status', 'draft')->sum('net_amount'),
            'released_amount' => $query->where('status', 'released')->sum('net_amount'),
            'paid_amount' => $query->where('status', 'paid')->sum('net_amount')
        ];

        return view('salary-releases.index', compact('salaryReleases', 'employees', 'summary'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        Gate::authorize('create', SalaryRelease::class);

        $employees = Employee::active()->orderBy('name')->get();
        $selectedEmployee = null;

        if ($request->filled('employee_id')) {
            $selectedEmployee = Employee::find($request->employee_id);
        }

        // Default period (current month)
        $periodStart = now()->startOfMonth()->format('Y-m-d');
        $periodEnd = now()->endOfMonth()->format('Y-m-d');

        return view('salary-releases.create', compact('employees', 'selectedEmployee', 'periodStart', 'periodEnd'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Employee $employee = null)
    {
        // DEBUG: Log incoming request
        \Log::info('SalaryRelease store called', [
            'method' => $request->method(),
            'url' => $request->url(),
            'all_input' => $request->all(),
            'employee_from_route' => $employee ? $employee->id : null,
            'is_ajax' => $request->ajax(),
            'expects_json' => $request->expectsJson()
        ]);

        Gate::authorize('create', SalaryRelease::class);
        // Handle both regular and employee-specific routes
        $employeeId = $employee ? $employee->id : $request->input('employee_id');

        try {
            $validated = $request->validate([
                'employee_id' => $employee ? 'nullable' : 'required|exists:employees,id',
                'period_start' => 'required|date',
                'period_end' => 'required|date|after_or_equal:period_start',
                'deductions' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string'
            ]);
            
            \Log::info('Validation passed', ['validated' => $validated]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed in salary release store', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Data tidak valid', 'errors' => $e->errors()], 422);
            }
            throw $e;
        }

        // Set employee_id if coming from employee route
        if ($employee) {
            $validated['employee_id'] = $employee->id;
        }

        $targetEmployee = Employee::findOrFail($validated['employee_id']);

        // Check if there are unreleased salaries for this period
        $unreleasedSalaries = $targetEmployee->dailySalaries()
            ->whereBetween('work_date', [$validated['period_start'], $validated['period_end']])
            ->where('status', 'confirmed')
            ->whereNull('salary_release_id')
            ->get();

        if ($unreleasedSalaries->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada gaji yang dikonfirmasi untuk periode ini.']);
            }
            return back()->withErrors(['period_start' => 'Tidak ada gaji yang dikonfirmasi untuk periode ini.'])
                        ->withInput();
        }

        // Check for overlapping releases
        $overlapping = SalaryRelease::where('employee_id', $validated['employee_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('period_start', [$validated['period_start'], $validated['period_end']])
                      ->orWhereBetween('period_end', [$validated['period_start'], $validated['period_end']])
                      ->orWhere(function ($q) use ($validated) {
                          $q->where('period_start', '<=', $validated['period_start'])
                            ->where('period_end', '>=', $validated['period_end']);
                      });
            })
            ->exists();

        if ($overlapping) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Sudah ada rilis gaji untuk periode yang tumpang tindih.']);
            }
            return back()->withErrors(['period_start' => 'Sudah ada rilis gaji untuk periode yang tumpang tindih.'])
                        ->withInput();
        }

        DB::beginTransaction();
        try {
            // Calculate totals
            $totalAmount = $unreleasedSalaries->sum('total_amount');

            $deductions = $validated['deductions'] ?? 0;
            $netAmount = $totalAmount - $deductions;

            // Log before creating
            \Log::info('Creating salary release with data', [
                'employee_id' => $validated['employee_id'],
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'total_amount' => $totalAmount,
                'deductions' => $deductions,
                'net_amount' => $netAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id()
            ]);

            // Create salary release
            $salaryRelease = SalaryRelease::create([
                'employee_id' => $validated['employee_id'],
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'total_amount' => $totalAmount,
                'deductions' => $deductions,
                'net_amount' => $netAmount,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id()
            ]);
            
            \Log::info('Salary release created successfully', ['id' => $salaryRelease->id]);

            // Attach daily salaries to this release
            $unreleasedSalaries->each(function ($salary) use ($salaryRelease) {
                $salary->update(['salary_release_id' => $salaryRelease->id]);
            });

            DB::commit();
            
            \Log::info('Transaction committed successfully');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rilis gaji berhasil dibuat.',
                    'salary_release_id' => $salaryRelease->id
                ], 200)->header('Content-Type', 'application/json');
            }

            $redirectRoute = $employee ? 'finance.employees.show' : 'finance.salary-releases.show';
            $redirectParam = $employee ? $employee : $salaryRelease;

            return redirect()->route($redirectRoute, $redirectParam)
                ->with('success', 'Rilis gaji berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Failed to create salary release', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated,
                'employee_id' => $validated['employee_id'] ?? null,
                'unreleased_count' => $unreleasedSalaries->count() ?? 0,
                'user_id' => auth()->id()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat rilis gaji: ' . $e->getMessage()
                ], 500)->header('Content-Type', 'application/json');
            }
            
            return back()->with('error', 'Gagal membuat rilis gaji: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SalaryRelease $salaryRelease)
    {
        Gate::authorize('view', $salaryRelease);

        $salaryRelease->load(['employee', 'dailySalaries', 'cashflowEntry', 'releasedBy']);

        return view('salary-releases.show', compact('salaryRelease'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalaryRelease $salaryRelease)
    {
        Gate::authorize('update', $salaryRelease);

        // Cannot edit if already released
        if ($salaryRelease->is_released) {
            return redirect()->route('finance.salary-releases.show', $salaryRelease)
                ->with('error', 'Tidak dapat mengedit rilis gaji yang sudah dirilis.');
        }

        $employees = Employee::active()->orderBy('name')->get();

        return view('salary-releases.edit', compact('salaryRelease', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalaryRelease $salaryRelease)
    {
        Gate::authorize('update', $salaryRelease);

        // Cannot update if already released
        if ($salaryRelease->is_released) {
            return redirect()->route('finance.salary-releases.show', $salaryRelease)
                ->with('error', 'Tidak dapat mengubah rilis gaji yang sudah dirilis.');
        }

        $validated = $request->validate([
            'deductions' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $deductions = $validated['deductions'] ?? 0;
        $netAmount = $salaryRelease->total_amount - $deductions;

        $salaryRelease->update([
            'deductions' => $deductions,
            'net_amount' => $netAmount,
            'notes' => $validated['notes']
        ]);

        return redirect()->route('finance.salary-releases.show', $salaryRelease)
            ->with('success', 'Rilis gaji berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalaryRelease $salaryRelease, Employee $employee = null)
    {
        Gate::authorize('delete', $salaryRelease);

        // Cannot delete if already released
        if ($salaryRelease->is_released) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus rilis gaji yang sudah dirilis.']);
            }
            return redirect()->route('finance.salary-releases.index')
                ->with('error', 'Tidak dapat menghapus rilis gaji yang sudah dirilis.');
        }

        DB::beginTransaction();
        try {
            // Detach daily salaries
            $salaryRelease->dailySalaries()->update(['salary_release_id' => null]);
            
            // Delete the release
            $salaryRelease->delete();

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Rilis gaji berhasil dihapus.']);
            }

            $redirectRoute = $employee ? 'finance.employees.show' : 'finance.salary-releases.index';
            $redirectParam = $employee ? $employee : null;

            return redirect()->route($redirectRoute, $redirectParam)
                ->with('success', 'Rilis gaji berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollback();
            
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus rilis gaji: ' . $e->getMessage()]);
            }
            
            $redirectRoute = $employee ? 'finance.employees.show' : 'finance.salary-releases.index';
            $redirectParam = $employee ? $employee : null;

            return redirect()->route($redirectRoute, $redirectParam)
                ->with('error', 'Gagal menghapus rilis gaji: ' . $e->getMessage());
        }
    }

    /**
     * Release the salary (change status to released)
     */
    public function release(Request $request, $employeeId, $salaryReleaseId)
    {
        // Manual model resolution to avoid route model binding issues
        $employee = Employee::findOrFail($employeeId);
        $salaryRelease = SalaryRelease::findOrFail($salaryReleaseId);
        
        // Add debugging
        \Log::info('Release method called', [
            'employee_id' => $employee->id,
            'salary_release_id' => $salaryRelease->id,
            'salary_release_status' => $salaryRelease->status,
            'url' => $request->url(),
            'route_params' => $request->route()->parameters(),
            'employee_param' => $employeeId,
            'salary_release_param' => $salaryReleaseId
        ]);

        // Verify that the salary release belongs to the employee
        if ($salaryRelease->employee_id != $employee->id) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Rilis gaji tidak terkait dengan karyawan ini.']);
            }
            return back()->with('error', 'Rilis gaji tidak terkait dengan karyawan ini.');
        }

        Gate::authorize('update', $salaryRelease);

        if ($salaryRelease->status !== 'draft') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Hanya rilis gaji dengan status draft yang dapat dirilis.']);
            }
            return back()->with('error', 'Hanya rilis gaji dengan status draft yang dapat dirilis.');
        }

        if ($salaryRelease->net_amount <= 0) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak dapat merilis gaji dengan jumlah bersih nol atau negatif.']);
            }
            return back()->with('error', 'Tidak dapat merilis gaji dengan jumlah bersih nol atau negatif.');
        }

        // Update status to released
        $salaryRelease->update([
            'status' => 'released',
            'released_by' => auth()->id(),
            'released_at' => now()
        ]);

        \Log::info('Salary release status updated', [
            'salary_release_id' => $salaryRelease->id,
            'new_status' => 'released'
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Gaji berhasil dirilis dan akan tercatat dalam cashflow.']);
        }

        return redirect()->route('finance.employees.show', $employee)
            ->with('success', 'Gaji berhasil dirilis dan akan tercatat dalam cashflow.');
    }

    /**
     * Mark salary as paid
     */
    public function markAsPaid(Request $request, $employeeId, $salaryReleaseId)
    {
        // Manual model resolution to avoid route model binding issues
        $employee = Employee::findOrFail($employeeId);
        $salaryRelease = SalaryRelease::findOrFail($salaryReleaseId);
        
        // Add debugging
        \Log::info('MarkAsPaid method called', [
            'employee_id' => $employee->id,
            'salary_release_id' => $salaryRelease->id,
            'salary_release_status' => $salaryRelease->status,
            'url' => $request->url(),
            'route_params' => $request->route()->parameters(),
            'employee_param' => $employeeId,
            'salary_release_param' => $salaryReleaseId
        ]);

        // Verify that the salary release belongs to the employee
        if ($salaryRelease->employee_id != $employee->id) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Rilis gaji tidak terkait dengan karyawan ini.']);
            }
            return back()->with('error', 'Rilis gaji tidak terkait dengan karyawan ini.');
        }

        Gate::authorize('update', $salaryRelease);

        if ($salaryRelease->status !== 'released') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Hanya gaji yang sudah dirilis yang dapat ditandai sebagai dibayar.']);
            }
            return back()->with('error', 'Hanya gaji yang sudah dirilis yang dapat ditandai sebagai dibayar.');
        }

        $salaryRelease->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);

        \Log::info('Salary release marked as paid', [
            'salary_release_id' => $salaryRelease->id,
            'new_status' => 'paid'
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Gaji berhasil ditandai sebagai dibayar.']);
        }

        return redirect()->route('finance.employees.show', $employee)
            ->with('success', 'Gaji berhasil ditandai sebagai dibayar.');
    }

    /**
     * Revert to draft status
     */
    public function revertToDraft(SalaryRelease $salaryRelease)
    {
        Gate::authorize('update', $salaryRelease);

        if ($salaryRelease->status === 'paid') {
            return back()->with('error', 'Tidak dapat mengembalikan gaji yang sudah dibayar ke status draft.');
        }

        $salaryRelease->markAsDraft();

        return redirect()->route('finance.salary-releases.show', $salaryRelease)
            ->with('success', 'Status gaji berhasil dikembalikan ke draft.');
    }

    /**
     * Get unreleased salaries for employee and period
     */
    public function getUnreleasedSalaries(Request $request, Employee $employee = null)
    {
        // Handle both regular and employee-specific routes
        $employeeId = $employee ? $employee->id : $request->input('employee_id');
        
        $validated = $request->validate([
            'employee_id' => $employee ? 'nullable' : 'required|exists:employees,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start'
        ]);

        // Set employee_id if coming from employee route
        if ($employee) {
            $validated['employee_id'] = $employee->id;
        }

        $targetEmployee = Employee::findOrFail($validated['employee_id']);
        
        $unreleasedSalaries = $targetEmployee->dailySalaries()
            ->whereBetween('work_date', [$validated['period_start'], $validated['period_end']])
            ->where('status', 'confirmed')
            ->whereNull('salary_release_id')
            ->orderBy('work_date')
            ->get();

        $totalAmount = $unreleasedSalaries->sum('total_amount');

        return response()->json([
            'salaries' => $unreleasedSalaries,
            'total_amount' => $totalAmount,
            'count' => $unreleasedSalaries->count(),
            'formatted_total' => 'Rp ' . number_format($totalAmount, 0, ',', '.')
        ]);
    }

    /**
     * Get salary releases for employee
     */
    public function getEmployeeSalaryReleases(Employee $employee)
    {
        $salaryReleases = SalaryRelease::where('employee_id', $employee->id)
            ->with(['employee', 'releasedBy', 'cashflowEntry'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($salaryReleases);
    }
}
