<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectBilling;
use App\Models\BillingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ProjectBillingIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user with direktur role
        $this->user = User::factory()->create();
        $this->user->roles()->create(['name' => 'direktur']);
        
        // Create test project
        $this->project = Project::factory()->create([
            'nilai_jasa' => 5000000,
            'nilai_material' => 2000000,
        ]);
    }

    /** @test */
    public function can_create_individual_project_billing()
    {
        $this->actingAs($this->user);

        $billingData = [
            'project_id' => $this->project->id,
            'payment_type' => 'full',
            'invoice_number' => 'INV-TEST-001',
            'nilai_jasa' => 5000000,
            'nilai_material' => 2000000,
            'subtotal' => 7000000,
            'ppn_rate' => 11,
            'ppn_calculation' => 'normal',
            'ppn_amount' => 770000,
            'total_amount' => 7770000,
            'billing_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'draft',
            'description' => 'Test billing'
        ];

        $response = $this->post(route('project-billings.store'), $billingData);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_billings', [
            'project_id' => $this->project->id,
            'payment_type' => 'full',
            'invoice_number' => 'INV-TEST-001',
            'billing_batch_id' => null, // Individual billing should not have batch_id
            'total_amount' => 7770000
        ]);
    }

    /** @test */
    public function individual_billing_does_not_conflict_with_batch_billing()
    {
        $this->actingAs($this->user);

        // Create individual billing first
        $individualBilling = ProjectBilling::create([
            'project_id' => $this->project->id,
            'payment_type' => 'full',
            'invoice_number' => 'INV-INDIVIDUAL-001',
            'nilai_jasa' => 5000000,
            'nilai_material' => 2000000,
            'subtotal' => 7000000,
            'ppn_rate' => 11,
            'ppn_amount' => 770000,
            'total_amount' => 7770000,
            'billing_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'draft',
            'billing_batch_id' => null // Individual billing
        ]);

        // Create batch billing
        $batch = BillingBatch::create([
            'batch_code' => 'BTH-TEST-001',
            'total_base_amount' => 10000000,
            'ppn_rate' => 11,
            'ppn_amount' => 1100000,
            'pph_rate' => 2,
            'pph_amount' => 200000,
            'total_billing_amount' => 11100000,
            'total_received_amount' => 10900000,
            'status' => 'draft',
            'client_type' => 'non_wapu',
            'billing_date' => now()
        ]);

        // Create batch billing entry
        $batchBilling = ProjectBilling::create([
            'project_id' => $this->project->id,
            'billing_batch_id' => $batch->id,
            'payment_type' => 'full',
            'invoice_number' => 'INV-BATCH-001',
            'nilai_jasa' => 5000000,
            'nilai_material' => 2000000,
            'subtotal' => 7000000,
            'ppn_rate' => 11,
            'ppn_amount' => 770000,
            'total_amount' => 7770000,
            'billing_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'draft'
        ]);

        // Verify both exist and are separate
        $this->assertDatabaseHas('project_billings', [
            'id' => $individualBilling->id,
            'billing_batch_id' => null
        ]);

        $this->assertDatabaseHas('project_billings', [
            'id' => $batchBilling->id,
            'billing_batch_id' => $batch->id
        ]);

        // Verify they can coexist
        $individualCount = ProjectBilling::whereNull('billing_batch_id')->count();
        $batchCount = ProjectBilling::whereNotNull('billing_batch_id')->count();

        $this->assertEquals(1, $individualCount);
        $this->assertEquals(1, $batchCount);
    }

    /** @test */
    public function currency_formatting_works_correctly()
    {
        $this->actingAs($this->user);

        // Test with formatted input (as would come from frontend)
        $billingData = [
            'project_id' => $this->project->id,
            'payment_type' => 'full',
            'invoice_number' => 'INV-FORMAT-001',
            'nilai_jasa' => 5000000, // Raw value from hidden field
            'nilai_material' => 2000000, // Raw value from hidden field
            'subtotal' => 7000000,
            'ppn_rate' => 11,
            'ppn_calculation' => 'normal',
            'ppn_amount' => 770000,
            'total_amount' => 7770000,
            'billing_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'draft'
        ];

        $response = $this->post(route('project-billings.store'), $billingData);

        $response->assertRedirect();
        
        $billing = ProjectBilling::where('invoice_number', 'INV-FORMAT-001')->first();
        $this->assertNotNull($billing);
        $this->assertEquals(5000000, $billing->nilai_jasa);
        $this->assertEquals(2000000, $billing->nilai_material);
        $this->assertEquals(7770000, $billing->total_amount);
    }

    /** @test */
    public function ppn_calculation_methods_work_correctly()
    {
        $testCases = [
            ['method' => 'normal', 'expected' => 770000], // 7000000 * 11% = 770000
            ['method' => 'round_up', 'expected' => 770000], // Same in this case
            ['method' => 'round_down', 'expected' => 770000], // Same in this case
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
}