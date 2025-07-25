<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Project;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== UPDATE EXISTING PROJECTS BILLING STATUS ===\n\n";

try {
    // Get all projects
    $projects = Project::all();
    
    echo "Found " . $projects->count() . " projects to update...\n\n";
    
    foreach ($projects as $project) {
        echo "Processing Project: {$project->code} - {$project->name}\n";
        
        // Update billing status untuk setiap project
        $project->updateBillingStatus();
        
        echo "  - Billing Status: {$project->billing_status_label}\n";
        echo "  - Total Billed: Rp " . number_format($project->total_billed_amount, 0, ',', '.') . "\n";
        echo "  - Billing Percentage: " . round($project->billing_percentage, 1) . "%\n";
        
        if ($project->latest_invoice_number) {
            echo "  - Latest Invoice: {$project->latest_invoice_number}\n";
        }
        
        if ($project->latest_sp_number) {
            echo "  - Latest SP: {$project->latest_sp_number}\n";
        }
        
        echo "  ✓ Updated successfully\n\n";
    }
    
    // Summary statistics
    echo "=== SUMMARY ===\n";
    echo "Not Billed: " . Project::byBillingStatus('not_billed')->count() . " projects\n";
    echo "Partially Billed: " . Project::byBillingStatus('partially_billed')->count() . " projects\n";
    echo "Fully Billed: " . Project::byBillingStatus('fully_billed')->count() . " projects\n";
    
    echo "\n=== PROJECTS NEEDING ATTENTION ===\n";
    $needsBilling = Project::needsBilling()->get();
    
    if ($needsBilling->count() > 0) {
        echo "Projects that need billing:\n";
        foreach ($needsBilling as $project) {
            echo "- {$project->code}: {$project->name} ({$project->billing_status_label})\n";
        }
    } else {
        echo "All projects are fully billed!\n";
    }
    
    $completedNotBilled = Project::completedButNotFullyBilled()->get();
    
    if ($completedNotBilled->count() > 0) {
        echo "\nCompleted projects not fully billed:\n";
        foreach ($completedNotBilled as $project) {
            echo "- {$project->code}: {$project->name} ({$project->billing_status_label})\n";
        }
    }
    
    echo "\n✅ All projects updated successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
