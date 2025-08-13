<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use App\Models\SalaryRelease;
use App\Models\DailySalary;

echo "=== DEBUG SALARY RELEASE DATA ===\n\n";

// Check Employee ID 4
$employee = Employee::find(4);
if ($employee) {
    echo "✓ Employee ID 4 found: {$employee->name}\n";
} else {
    echo "✗ Employee ID 4 NOT found\n";
}

// Check SalaryRelease ID 10
$salaryRelease = SalaryRelease::find(10);
if ($salaryRelease) {
    echo "✓ SalaryRelease ID 10 found:\n";
    echo "  - Employee ID: {$salaryRelease->employee_id}\n";
    echo "  - Status: {$salaryRelease->status}\n";
    echo "  - Period: {$salaryRelease->period_start} to {$salaryRelease->period_end}\n";
    echo "  - Total Amount: {$salaryRelease->total_amount}\n";
    echo "  - Net Amount: {$salaryRelease->net_amount}\n";
} else {
    echo "✗ SalaryRelease ID 10 NOT found\n";
}

// List all salary releases
echo "\n=== ALL SALARY RELEASES ===\n";
$allReleases = SalaryRelease::with('employee')->get();
foreach ($allReleases as $release) {
    echo "ID: {$release->id} | Employee: {$release->employee->name} | Status: {$release->status} | Period: {$release->period_start} - {$release->period_end}\n";
}

// Check if there are any salary releases for employee 4
echo "\n=== SALARY RELEASES FOR EMPLOYEE 4 ===\n";
$employeeReleases = SalaryRelease::where('employee_id', 4)->get();
if ($employeeReleases->count() > 0) {
    foreach ($employeeReleases as $release) {
        echo "ID: {$release->id} | Status: {$release->status} | Period: {$release->period_start} - {$release->period_end}\n";
    }
} else {
    echo "No salary releases found for employee 4\n";
}

// Check daily salaries for employee 4
echo "\n=== DAILY SALARIES FOR EMPLOYEE 4 ===\n";
$dailySalaries = DailySalary::where('employee_id', 4)->get();
if ($dailySalaries->count() > 0) {
    echo "Found {$dailySalaries->count()} daily salary records:\n";
    foreach ($dailySalaries->take(5) as $salary) {
        echo "ID: {$salary->id} | Date: {$salary->date} | Amount: {$salary->amount} | Status: {$salary->status}\n";
    }
    if ($dailySalaries->count() > 5) {
        echo "... and " . ($dailySalaries->count() - 5) . " more records\n";
    }
} else {
    echo "No daily salary records found for employee 4\n";
}

echo "\n=== DEBUG COMPLETE ===\n";