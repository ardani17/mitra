<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectBilling;
use App\Models\ProjectExpense;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashflowObserverTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'finance_manager']);
        $this->project = Project::factory()->create();
        
        // Seed cashflow categories
        $this->artisan('db:seed', ['--class' => 'CashflowCategorySeeder']);
    }

    /** @test */
    public function project_billing_observer_creates_cashflow_entry_on_paid_status()
    {
        // Create billing with non-paid status
        $billing = ProjectBilling::factory()->create([
            'project_id' => $this->project->id,
            'total_amount' => 75000000,
            'status' => 'draft'
        ]);

        // Verify no cashflow entry exists yet
        $this->assertDatabaseMissing('cashflow_entries', [
            'reference_type' => 'billing',
            'reference_id' => $billing->id
        ]);

        // Update to paid status
        $billing->update(['status' => 'paid']);

        // Verify cashflow entry was created
        $this->assertDatabaseHas('cashflow_entries', [
            'project_id' => $this->project->id,
            'reference_type' => 'billing',
            'reference_id' => $billing->id,
            'type' => 'income',
            'amount' => 75000000,
            'status' => 'confirmed'
        ]);

        $cashflowEntry = CashflowEntry::where('reference_type', 'billing')
            ->where('reference_id', $billing->id)
            ->first();

        $this->assertNotNull($cashflowEntry);
        $this->assertStringContains('Pembayaran dari penagihan', $cashflowEntry->description);
    }

    /** @test */
    public function project_billing_observer_cancels_cashflow_entry_when_status_changes_from_paid()
    {
        // Create billing with paid status (will trigger observer)
        $billing = ProjectBilling::factory()->create([
            'project_id' => $this->project->id,
            'total_amount' => 60000000,
            'status' => 'paid'
        ]);

        // Verify cashflow entry was created
        $cashflowEntry = CashflowEntry::where('reference_type', 'billing')
            ->where('reference_id', $billing->id)
            ->first();

        $this->assertNotNull($cashflowEntry);
        $this->assertEquals('confirmed', $cashflowEntry->status);

        // Change status from paid
        $billing->update(['status' => 'cancelled']);

        // Verify cashflow entry was cancelled
        $cashflowEntry->refresh();
        $this->assertEquals('cancelled', $cashflowEntry->status);
    }

    /** @test */
    public function project_expense_observer_creates_cashflow_entry_on_approved_status()
    {
        // Create expense with non-approved status
        $expense = ProjectExpense::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 25000000,
            'status' => 'draft'
        ]);

        // Verify no cashflow entry exists yet
        $this->assertDatabaseMissing('cashflow_entries', [
            'reference_type' => 'expense',
            'reference_id' => $expense->id
        ]);

        // Update to approved status
        $expense->update(['status' => 'approved']);

        // Verify cashflow entry was created
        $this->assertDatabaseHas('cashflow_entries', [
            'project_id' => $this->project->id,
            'reference_type' => 'expense',
            'reference_id' => $expense->id,
            'type' => 'expense',
            'amount' => 25000000,
            'status' => 'confirmed'
        ]);

        $cashflowEntry = CashflowEntry::where('reference_type', 'expense')
            ->where('reference_id', $expense->id)
            ->first();

        $this->assertNotNull($cashflowEntry);
        $this->assertEquals('Pengeluaran: ' . $expense->description, $cashflowEntry->description);
    }

    /** @test */
    public function project_expense_observer_cancels_cashflow_entry_when_status_changes_from_approved()
    {
        // Create expense with approved status (will trigger observer)
        $expense = ProjectExpense::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 35000000,
            'status' => 'approved'
        ]);

        // Verify cashflow entry was created
        $cashflowEntry = CashflowEntry::where('reference_type', 'expense')
            ->where('reference_id', $expense->id)
            ->first();

        $this->assertNotNull($cashflowEntry);
        $this->assertEquals('confirmed', $cashflowEntry->status);

        // Change status from approved
        $expense->update(['status' => 'rejected']);

        // Verify cashflow entry was cancelled
        $cashflowEntry->refresh();
        $this->assertEquals('cancelled', $cashflowEntry->status);
    }

    /** @test */
    public function observer_uses_correct_cashflow_categories()
    {
        // Test billing creates income with correct category
        $billing = ProjectBilling::factory()->create([
            'project_id' => $this->project->id,
            'total_amount' => 50000000,
            'status' => 'paid'
        ]);

        $cashflowEntry = CashflowEntry::where('reference_type', 'billing')
            ->where('reference_id', $billing->id)
            ->first();

        $this->assertNotNull($cashflowEntry);
        $this->assertEquals('income', $cashflowEntry->type);
        
        $category = $cashflowEntry->category;
        $this->assertEquals('income', $category->type);
        $this->assertTrue($category->is_system);

        // Test expense creates expense with correct category
        $expense = ProjectExpense::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 20000000,
            'status' => 'approved'
        ]);

        $cashflowEntry = CashflowEntry::where('reference_type', 'expense')
            ->where('reference_id', $expense->id)
            ->first();

        $this->assertNotNull($cashflowEntry);
        $this->assertEquals('expense', $cashflowEntry->type);
        
        $category = $cashflowEntry->category;
        $this->assertEquals('expense', $category->type);
        $this->assertTrue($category->is_system);
    }

    /** @test */
    public function observer_handles_multiple_status_changes_correctly()
    {
        // Create billing
        $billing = ProjectBilling::factory()->create([
            'project_id' => $this->project->id,
            'total_amount' => 40000000,
            'status' => 'draft'
        ]);

        // Change to paid (should create cashflow entry)
        $billing->update(['status' => 'paid']);
        
        $this->assertDatabaseHas('cashflow_entries', [
            'reference_type' => 'billing',
            'reference_id' => $billing->id,
            'status' => 'confirmed'
        ]);

        // Change to sent (should cancel cashflow entry)
        $billing->update(['status' => 'sent']);
        
        $this->assertDatabaseHas('cashflow_entries', [
            'reference_type' => 'billing',
            'reference_id' => $billing->id,
            'status' => 'cancelled'
        ]);

        // Change back to paid (should create new cashflow entry)
        $billing->update(['status' => 'paid']);
        
        // Should have 2 entries: 1 cancelled, 1 confirmed
        $entries = CashflowEntry::where('reference_type', 'billing')
            ->where('reference_id', $billing->id)
            ->get();

        $this->assertEquals(2, $entries->count());
        $this->assertEquals(1, $entries->where('status', 'confirmed')->count());
        $this->assertEquals(1, $entries->where('status', 'cancelled')->count());
    }

    /** @test */
    public function observer_does_not_create_duplicate_entries()
    {
        // Create billing with paid status
        $billing = ProjectBilling::factory()->create([
            'project_id' => $this->project->id,
            'total_amount' => 30000000,
            'status' => 'paid'
        ]);

        // Verify one entry was created
        $entriesCount = CashflowEntry::where('reference_type', 'billing')
            ->where('reference_id', $billing->id)
            ->count();

        $this->assertEquals(1, $entriesCount);

        // Update billing with same paid status
        $billing->update(['status' => 'paid']);

        // Verify no additional entry was created
        $entriesCount = CashflowEntry::where('reference_type', 'billing')
            ->where('reference_id', $billing->id)
            ->count();

        $this->assertEquals(1, $entriesCount);
    }
}