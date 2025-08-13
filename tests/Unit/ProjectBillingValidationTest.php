<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ProjectBilling;

class ProjectBillingValidationTest extends TestCase
{
    /** @test */
    public function can_differentiate_individual_and_batch_billing()
    {
        // Individual billing (no batch_id)
        $individualBilling = new ProjectBilling([
            'project_id' => 1,
            'billing_batch_id' => null,
            'payment_type' => 'full',
            'nilai_jasa' => 5000000,
            'nilai_material' => 2000000,
            'ppn_rate' => 11,
            'ppn_calculation' => 'normal'
        ]);

        // Batch billing (has batch_id)
        $batchBilling = new ProjectBilling([
            'project_id' => 1,
            'billing_batch_id' => 1,
            'payment_type' => 'full',
            'nilai_jasa' => 5000000,
            'nilai_material' => 2000000,
            'ppn_rate' => 11,
            'ppn_calculation' => 'normal'
        ]);

        // Test differentiation
        $this->assertNull($individualBilling->billing_batch_id);
        $this->assertNotNull($batchBilling->billing_batch_id);
        $this->assertEquals(1, $batchBilling->billing_batch_id);
    }

    /** @test */
    public function ppn_calculation_works_correctly()
    {
        $billing = new ProjectBilling([
            'nilai_jasa' => 5000000,
            'nilai_material' => 2000000,
            'ppn_rate' => 11,
            'ppn_calculation' => 'normal'
        ]);

        $subtotal = $billing->calculateSubtotal();
        $ppnAmount = $billing->calculatePpnAmount();
        $totalAmount = $billing->calculateTotalAmount();

        $this->assertEquals(7000000, $subtotal); // 5M + 2M
        $this->assertEquals(770000, $ppnAmount); // 7M * 11%
        $this->assertEquals(7770000, $totalAmount); // 7M + 770K
    }

    /** @test */
    public function ppn_calculation_methods_work()
    {
        $testCases = [
            ['method' => 'normal', 'expected' => 770000],
            ['method' => 'round_up', 'expected' => 770000],
            ['method' => 'round_down', 'expected' => 770000],
        ];

        foreach ($testCases as $case) {
            $billing = new ProjectBilling([
                'nilai_jasa' => 5000000,
                'nilai_material' => 2000000,
                'ppn_rate' => 11,
                'ppn_calculation' => $case['method']
            ]);

            $calculatedPpn = $billing->calculatePpnAmount();
            $this->assertEquals($case['expected'], $calculatedPpn, 
                "PPN calculation failed for method: {$case['method']}");
        }
    }

    /** @test */
    public function termin_payment_detection_works()
    {
        $fullPayment = new ProjectBilling(['payment_type' => 'full']);
        $terminPayment = new ProjectBilling(['payment_type' => 'termin']);

        $this->assertTrue($fullPayment->isFullPayment());
        $this->assertFalse($fullPayment->isTerminPayment());

        $this->assertTrue($terminPayment->isTerminPayment());
        $this->assertFalse($terminPayment->isFullPayment());
    }

    /** @test */
    public function termin_label_generation_works()
    {
        $fullPayment = new ProjectBilling(['payment_type' => 'full']);
        $terminPayment = new ProjectBilling([
            'payment_type' => 'termin',
            'termin_number' => 1,
            'total_termin' => 3
        ]);

        $this->assertEquals('Pembayaran Penuh', $fullPayment->getTerminLabel());
        $this->assertEquals('Termin 1 dari 3', $terminPayment->getTerminLabel());
    }

    /** @test */
    public function auto_calculation_updates_amounts()
    {
        $billing = new ProjectBilling([
            'nilai_jasa' => 5000000,
            'nilai_material' => 2000000,
            'ppn_rate' => 11,
            'ppn_calculation' => 'normal'
        ]);

        $billing->updateCalculatedAmounts();

        $this->assertEquals(7000000, $billing->subtotal);
        $this->assertEquals(770000, $billing->ppn_amount);
        $this->assertEquals(7770000, $billing->total_amount);
    }

    /** @test */
    public function currency_formatting_validation()
    {
        // Test that our system can handle large numbers correctly
        $largeAmount = 1000000000; // 1 billion
        $billing = new ProjectBilling([
            'nilai_jasa' => $largeAmount,
            'nilai_material' => 0,
            'ppn_rate' => 11,
            'ppn_calculation' => 'normal'
        ]);

        $subtotal = $billing->calculateSubtotal();
        $ppnAmount = $billing->calculatePpnAmount();
        $totalAmount = $billing->calculateTotalAmount();

        $this->assertEquals($largeAmount, $subtotal);
        $this->assertEquals($largeAmount * 0.11, $ppnAmount);
        $this->assertEquals($largeAmount + ($largeAmount * 0.11), $totalAmount);
    }
}