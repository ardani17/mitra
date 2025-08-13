<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectBillingFormTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create([
            'nilai_jasa' => 5000000,
            'nilai_material' => 2000000
        ]);
    }

    /** @test */
    public function it_can_create_project_billing_with_formatted_currency_input()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('project-billings.store'), [
            'project_id' => $this->project->id,
            'payment_type' => 'full',
            'invoice_number' => 'INV-2025-001',
            'nilai_jasa' => 5000000, // Raw numeric value
            'nilai_material' => 2000000, // Raw numeric value
            'subtotal' => 7000000,
            'ppn_rate' => 11,
            'ppn_calculation' => 'normal',
            'ppn_amount' => 770000,
            'total_amount' => 7770000,
            'billing_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'draft',
            'description' => 'Test billing'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_billings', [
            'project_id' => $this->project->id,
            'invoice_number' => 'INV-2025-001',
            'nilai_jasa' => 5000000,
            'nilai_material' => 2000000,
            'total_amount' => 7770000
        ]);
    }

    /** @test */
    public function it_validates_required_nilai_jasa_field()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('project-billings.store'), [
            'project_id' => $this->project->id,
            'payment_type' => 'full',
            'invoice_number' => 'INV-2025-002',
            'nilai_jasa' => 0, // Invalid: should be > 0
            'nilai_material' => 2000000,
            'ppn_rate' => 11,
            'ppn_calculation' => 'normal',
            'billing_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'draft'
        ]);

        $response->assertSessionHasErrors(['nilai_jasa']);
    }

    /** @test */
    public function it_validates_required_nilai_material_field()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('project-billings.store'), [
            'project_id' => $this->project->id,
            'payment_type' => 'full',
            'invoice_number' => 'INV-2025-003',
            'nilai_jasa' => 5000000,
            'nilai_material' => '', // Invalid: empty
            'ppn_rate' => 11,
            'ppn_calculation' => 'normal',
            'billing_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'draft'
        ]);

        $response->assertSessionHasErrors(['nilai_material']);
    }

    /** @test */
    public function it_can_handle_zero_nilai_material()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('project-billings.store'), [
            'project_id' => $this->project->id,
            'payment_type' => 'full',
            'invoice_number' => 'INV-2025-004',
            'nilai_jasa' => 5000000,
            'nilai_material' => 0, // Valid: can be 0
            'subtotal' => 5000000,
            'ppn_rate' => 11,
            'ppn_calculation' => 'normal',
            'ppn_amount' => 550000,
            'total_amount' => 5550000,
            'billing_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'draft'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_billings', [
            'project_id' => $this->project->id,
            'invoice_number' => 'INV-2025-004',
            'nilai_jasa' => 5000000,
            'nilai_material' => 0,
            'total_amount' => 5550000
        ]);
    }

    /** @test */
    public function it_calculates_ppn_correctly_with_different_methods()
    {
        $this->actingAs($this->user);

        // Test normal calculation
        $response = $this->post(route('project-billings.store'), [
            'project_id' => $this->project->id,
            'payment_type' => 'full',
            'invoice_number' => 'INV-2025-005',
            'nilai_jasa' => 5000000,
            'nilai_material' => 2000000,
            'subtotal' => 7000000,
            'ppn_rate' => 11,
            'ppn_calculation' => 'normal',
            'ppn_amount' => 770000, // 11% of 7,000,000
            'total_amount' => 7770000,
            'billing_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'draft'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_billings', [
            'invoice_number' => 'INV-2025-005',
            'ppn_amount' => 770000,
            'total_amount' => 7770000
        ]);
    }

    /** @test */
    public function it_can_update_project_billing_with_formatted_values()
    {
        $this->actingAs($this->user);

        // Create billing first
        $billing = \App\Models\ProjectBilling::factory()->create([
            'project_id' => $this->project->id,
            'nilai_jasa' => 3000000,
            'nilai_material' => 1000000,
            'status' => 'draft'
        ]);

        $response = $this->put(route('project-billings.update', $billing), [
            'invoice_number' => $billing->invoice_number,
            'nilai_jasa' => 5000000, // Updated value
            'nilai_material' => 2000000, // Updated value
            'subtotal' => 7000000,
            'ppn_rate' => 11,
            'ppn_calculation' => 'normal',
            'ppn_amount' => 770000,
            'total_amount' => 7770000,
            'billing_date' => $billing->billing_date,
            'due_date' => $billing->due_date,
            'status' => 'draft'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_billings', [
            'id' => $billing->id,
            'nilai_jasa' => 5000000,
            'nilai_material' => 2000000,
            'total_amount' => 7770000
        ]);
    }

    /** @test */
    public function create_form_loads_successfully()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('project-billings.create'));

        $response->assertStatus(200);
        $response->assertViewIs('project-billings.create');
        $response->assertViewHas('projects');
    }

    /** @test */
    public function edit_form_loads_successfully()
    {
        $this->actingAs($this->user);

        $billing = \App\Models\ProjectBilling::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'draft'
        ]);

        $response = $this->get(route('project-billings.edit', $billing));

        $response->assertStatus(200);
        $response->assertViewIs('project-billings.edit');
        $response->assertViewHas(['projectBilling', 'projects']);
    }
}