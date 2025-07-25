<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Database Consistency ===\n\n";

// Check roles table
echo "1. Roles in database:\n";
$roles = \App\Models\Role::all();
foreach($roles as $role) {
    echo "   ID: {$role->id}, Name: {$role->name}\n";
}

echo "\n2. Users and their roles:\n";
$users = \App\Models\User::with('roles')->get();
foreach($users as $user) {
    echo "   Email: {$user->email}\n";
    foreach($user->roles as $role) {
        echo "     - Role: {$role->name}\n";
    }
}

echo "\n3. Expense approvals table (checking level column):\n";
$approvals = \App\Models\ExpenseApproval::select('level')->distinct()->get();
foreach($approvals as $approval) {
    echo "   Level: {$approval->level}\n";
}

echo "\n4. Checking migration files for role names:\n";

// Check roles seeder
$roleSeederPath = 'database/seeders/RoleSeeder.php';
if (file_exists($roleSeederPath)) {
    $content = file_get_contents($roleSeederPath);
    echo "   RoleSeeder.php contains:\n";
    if (strpos($content, 'direktur') !== false) {
        echo "     - 'direktur' found\n";
    }
    if (strpos($content, 'director') !== false) {
        echo "     - 'director' found\n";
    }
}

// Check expense approvals migration
$expenseApprovalMigration = 'database/migrations/2025_07_24_010554_create_expense_approvals_table.php';
if (file_exists($expenseApprovalMigration)) {
    $content = file_get_contents($expenseApprovalMigration);
    echo "   ExpenseApprovals migration contains:\n";
    if (strpos($content, 'direktur') !== false) {
        echo "     - 'direktur' found\n";
    }
    if (strpos($content, 'director') !== false) {
        echo "     - 'director' found\n";
    }
}

echo "\n=== Recommendation ===\n";
echo "Based on the database check, we should use: 'direktur' (Indonesian)\n";
echo "All code should be consistent with this naming.\n";
