<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Projects ===\n\n";

// Check all projects
$allProjects = \App\Models\Project::all();
echo "Total projects: " . $allProjects->count() . "\n\n";

foreach($allProjects as $project) {
    echo "ID: {$project->id}\n";
    echo "Code: {$project->code}\n";
    echo "Name: {$project->name}\n";
    echo "Status: {$project->status}\n";
    echo "Service Value: " . ($project->final_service_value ?? 0) . "\n";
    echo "Material Value: " . ($project->final_material_value ?? 0) . "\n";
    echo "Total Value: " . (($project->final_service_value ?? 0) + ($project->final_material_value ?? 0)) . "\n";
    echo "Client Type: " . ($project->client_type ?? 'not set') . "\n";
    echo "---\n";
}

echo "\n=== Projects with Final Values ===\n\n";

$projectsWithValues = \App\Models\Project::whereRaw('COALESCE(final_service_value, 0) + COALESCE(final_material_value, 0) > 0')->get();
echo "Projects with final values: " . $projectsWithValues->count() . "\n\n";

foreach($projectsWithValues as $project) {
    echo "ID: {$project->id}, Code: {$project->code}, Name: {$project->name}\n";
    echo "  Service: " . ($project->final_service_value ?? 0) . ", Material: " . ($project->final_material_value ?? 0) . "\n";
    echo "  Status: {$project->status}, Client Type: " . ($project->client_type ?? 'not set') . "\n";
    echo "\n";
}

echo "\n=== Projects Available for Billing ===\n\n";

// Check projects available for billing (same logic as controller)
$availableProjects = \App\Models\Project::whereRaw('COALESCE(final_service_value, 0) + COALESCE(final_material_value, 0) > 0')
    ->where(function($query) {
        // Projects that don't have any billing yet
        $query->whereDoesntHave('billings')
            // OR projects that have billings but not in any batch
            ->orWhereHas('billings', function($subQuery) {
                $subQuery->whereNull('billing_batch_id');
            });
    })
    ->orderBy('code')
    ->get();

echo "Available projects for billing: " . $availableProjects->count() . "\n\n";

foreach($availableProjects as $project) {
    echo "ID: {$project->id}, Code: {$project->code}, Name: {$project->name}\n";
    echo "  Service: " . ($project->final_service_value ?? 0) . ", Material: " . ($project->final_material_value ?? 0) . "\n";
    echo "  Status: {$project->status}, Client Type: " . ($project->client_type ?? 'not set') . "\n";
    
    // Check billings
    $billings = $project->billings;
    if ($billings->count() > 0) {
        echo "  Existing billings: " . $billings->count() . "\n";
        foreach($billings as $billing) {
            echo "    - Billing ID: {$billing->id}, Batch ID: " . ($billing->billing_batch_id ?? 'none') . "\n";
        }
    } else {
        echo "  No existing billings\n";
    }
    echo "\n";
}
