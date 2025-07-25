<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Setup database connection
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'pgsql',
    'host' => 'localhost',
    'database' => 'mitra_db',
    'username' => 'postgres',
    'password' => 'password',
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== Memperbaiki Data Pajak Billing Batch ===\n\n";

try {
    // Get all billing batches
    $batches = Capsule::table('billing_batches')->get();
    
    echo "Ditemukan " . count($batches) . " billing batch\n\n";
    
    foreach ($batches as $batch) {
        echo "Processing Batch: {$batch->batch_code}\n";
        echo "- Total Base Amount: " . number_format($batch->total_base_amount, 0, ',', '.') . "\n";
        echo "- PPN Rate: {$batch->ppn_rate}%\n";
        echo "- PPh Rate: {$batch->pph_rate}%\n";
        
        // Calculate correct tax amounts
        $baseAmount = $batch->total_base_amount;
        $ppnAmount = ($baseAmount * $batch->ppn_rate) / 100;
        $pphAmount = ($baseAmount * $batch->pph_rate) / 100;
        $totalBilling = $baseAmount + $ppnAmount;
        $totalReceived = $totalBilling - $pphAmount;
        
        echo "- Calculated PPN: " . number_format($ppnAmount, 0, ',', '.') . "\n";
        echo "- Calculated PPh: " . number_format($pphAmount, 0, ',', '.') . "\n";
        echo "- Current PPN in DB: " . number_format($batch->ppn_amount, 0, ',', '.') . "\n";
        echo "- Current PPh in DB: " . number_format($batch->pph_amount, 0, ',', '.') . "\n";
        
        // Update if values are different
        if ($batch->ppn_amount != $ppnAmount || $batch->pph_amount != $pphAmount) {
            echo "- UPDATING tax amounts...\n";
            
            Capsule::table('billing_batches')
                ->where('id', $batch->id)
                ->update([
                    'ppn_amount' => $ppnAmount,
                    'pph_amount' => $pphAmount,
                    'total_billing_amount' => $totalBilling,
                    'total_received_amount' => $totalReceived,
                    'updated_at' => now()
                ]);
                
            echo "- ✓ Updated successfully\n";
        } else {
            echo "- ✓ Tax amounts already correct\n";
        }
        
        echo "\n";
    }
    
    echo "=== Selesai ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
