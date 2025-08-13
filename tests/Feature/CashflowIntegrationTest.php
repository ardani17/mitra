<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectBilling;
use App\Models\ProjectExpense;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CashflowIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user with finance_manager role
        $this->user = User::factory()->create([
            'role' => 'finance_manager'
        ]);
        
        // Create test project
        $this->project = Project::factory()->create([
            'name' => 'Test Integration Project',
            'project_value' => 100000000
        ]);

        // Seed cashflow categories
        $this->artisan('db:seed', ['--class' => 'CashflowCategorySeeder']);
    }

    /** @test */
    public function billing_payment_creates_cashflow_entry()
    {
        // Create a project billing
        $billing = ProjectBilling::factory()->create([
            'project_id' => $this->project->id,
            'total_amount' => 50000000,
            'status' => 'sent'
        ]);

        // Update billing status to paid
        $billing->update(['status' => 'paid']);

        // Assert cashflow entry was created
        $this->assertDatabaseHas('cashflow_entries', [
            'project_id' => $this->project->id,
            'reference_type' => 'billing',
            'reference_id' => $billing->id,
            'type' => 'income',
            'amount' => 50000000,
            'status' => 'confirmed'
        ]);

        $cashflowEntry = CashflowEntry::where('reference_id', $billing->id)
            ->where('reference_type', 'billing')
            ->first();

        $this->assertNotNull($cashflowEntry);
        $this->assertEquals('Pembayaran dari penagihan: ' . $billing->invoice_number, $cashflowEntry->description);
    }

    /** @test */
    public function billing_status_change_from_paid_cancels_cashflow_entry()
    {
        // Create a paid billing with cashflow entry
        $billing = ProjectBilling::factory()->create([
            'project_id' => $this->project->id,
            'total_amount' => 30000000,
            'status' => 'paid'
        ]);

        // Verify cashflow entry exists
        $this->assertDatabaseHas('cashflow_entries', [
            'reference_id' => $billing->id,
            'reference_type' => 'billing',
            'status' => 'confirmed'
        ]);

        // Change billing status from paid
        $billing->update(['status' => 'sent']);

        // Assert cashflow entry was cancelled
        $this->assertDatabaseHas('cashflow_entries', [
            'reference_id' => $billing->id,
            'reference_type' => 'billing',
            'status' => 'cancelled'
        ]);
    }

    /** @test */
    public function expense_approval_creates_cashflow_entry()
    {
        // Create a project expense
        $expense = ProjectExpense::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 15000000,
            'status' => 'submitted'
        ]);

        // Approve the expense
        $expense->update(['status' => 'approved']);

        // Assert cashflow entry was created
        $this->assertDatabaseHas('cashflow_entries', [
            'project_id' => $this->project->id,
            'reference_type' => 'expense',
            'reference_id' => $expense->id,
            'type' => 'expense',
            'amount' => 15000000,
            'status' => 'confirmed'
        ]);

        $cashflowEntry = CashflowEntry::where('reference_id', $expense->id)
            ->where('reference_type', 'expense')
            ->first();

        $this->assertNotNull($cashflowEntry);
        $this->assertEquals('Pengeluaran: ' . $expense->description, $cashflowEntry->description);
    }

    /** @test */
    public function expense_status_change_from_approved_cancels_cashflow_entry()
    {
        // Create an approved expense with cashflow entry
        $expense = ProjectExpense::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 20000000,
            'status' => 'approved'
        ]);

        // Verify cashflow entry exists
        $this->assertDatabaseHas('cashflow_entries', [
            'reference_id' => $expense->id,
            'reference_type' => 'expense',
            'status' => 'confirmed'
        ]);

        // Change expense status from approved
        $expense->update(['status' => 'rejected']);

        // Assert cashflow entry was cancelled
        $this->assertDatabaseHas('cashflow_entries', [
            'reference_id' => $expense->id,
            'reference_type' => 'expense',
            'status' => 'cancelled'
        ]);
    }

    /** @test */
    public function manual_cashflow_entry_can_be_created()
    {
        $this->actingAs($this->user);

        $category = CashflowCategory::where('type', 'income')->first();

        $response = $this->post(route('finance.cashflow.store'), [
            'project_id' => $this->project->id,
            'category_id' => $category->id,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Manual income entry',
            'amount' => 25000000,
            'type' => 'income',
            'payment_method' => 'bank_transfer',
            'notes' => 'Test manual entry'
        ]);

        $response->assertRedirect(route('finance.cashflow.index'));

        $this->assertDatabaseHas('cashflow_entries', [
            'project_id' => $this->project->id,
            'reference_type' => 'manual',
            'type' => 'income',
            'amount' => 25000000,
            'description' => 'Manual income entry',
            'status' => 'confirmed'
        ]);
    }

    /** @test */
    public function cashflow_balance_calculation_is_accurate()
    {
        // Create multiple transactions
        $incomeCategory = CashflowCategory::where('type', 'income')->first();
        $expenseCategory = CashflowCategory::where('type', 'expense')->first();

        // Create income entries
        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Income 1',
            'amount' => 50000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'confirmed_by' => $this->user->id,
            'confirmed_at' => now()
        ]);

        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Income 2',
            'amount' => 30000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'confirmed_by' => $this->user->id,
            'confirmed_at' => now()
        ]);

        // Create expense entries
        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $expenseCategory->id,
            'transaction_date' => now(),
            'description' => 'Expense 1',
            'amount' => 20000000,
            'type' => 'expense',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'confirmed_by' => $this->user->id,
            'confirmed_at' => now()
        ]);

        // Calculate balance
        $balance = CashflowEntry::getBalance(now()->startOfDay(), now()->endOfDay());

        $this->assertEquals(80000000, $balance['income']); // 50M + 30M
        $this->assertEquals(20000000, $balance['expense']); // 20M
        $this->assertEquals(60000000, $balance['balance']); // 80M - 20M
    }

    /** @test */
    public function cashflow_dashboard_displays_correct_data()
    {
        $this->actingAs($this->user);

        // Create some test data
        $incomeCategory = CashflowCategory::where('type', 'income')->first();
        $expenseCategory = CashflowCategory::where('type', 'expense')->first();

        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Test Income',
            'amount' => 40000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'confirmed_by' => $this->user->id,
            'confirmed_at' => now()
        ]);

        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $expenseCategory->id,
            'transaction_date' => now(),
            'description' => 'Test Expense',
            'amount' => 15000000,
            'type' => 'expense',
            'reference_type' => 'manual',
            'status' => 'pending',
            'created_by' => $this->user->id
        ]);

        $response = $this->get(route('finance.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('summary');
        
        $summary = $response->viewData('summary');
        $this->assertEquals(40000000, $summary['total_income']);
        $this->assertEquals(0, $summary['total_expense']); // Pending expense not counted
        $this->assertEquals(1, $summary['pending_count']);
    }

    /** @test */
    public function bulk_actions_work_correctly()
    {
        $this->actingAs($this->user);

        $category = CashflowCategory::where('type', 'income')->first();

        // Create pending entries
        $entry1 = CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $category->id,
            'transaction_date' => now(),
            'description' => 'Pending Entry 1',
            'amount' => 10000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'pending',
            'created_by' => $this->user->id
        ]);

        $entry2 = CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $category->id,
            'transaction_date' => now(),
            'description' => 'Pending Entry 2',
            'amount' => 15000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'pending',
            'created_by' => $this->user->id
        ]);

        // Bulk confirm
        $response = $this->post(route('finance.cashflow.bulk-action'), [
            'action' => 'confirm',
            'entries' => [$entry1->id, $entry2->id]
        ]);

        $response->assertRedirect(route('finance.cashflow.index'));

        // Assert entries are confirmed
        $this->assertDatabaseHas('cashflow_entries', [
            'id' => $entry1->id,
            'status' => 'confirmed'
        ]);

        $this->assertDatabaseHas('cashflow_entries', [
            'id' => $entry2->id,
            'status' => 'confirmed'
        ]);
    }

    /** @test */
    public function export_functionality_works()
    {
        $this->actingAs($this->user);

        $category = CashflowCategory::where('type', 'income')->first();

        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $category->id,
            'transaction_date' => now(),
            'description' => 'Export Test Entry',
            'amount' => 25000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'confirmed_by' => $this->user->id,
            'confirmed_at' => now()
        ]);

        // Test CSV export
        $response = $this->get(route('finance.cashflow.export', ['format' => 'csv']));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');

        // Test Excel export
        $response = $this->get(route('finance.cashflow.export', ['format' => 'excel']));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
    }

    /** @test */
    public function filtering_and_search_work_correctly()
    {
        $this->actingAs($this->user);

        $incomeCategory = CashflowCategory::where('type', 'income')->first();
        $expenseCategory = CashflowCategory::where('type', 'expense')->first();

        // Create test entries
        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Searchable Income Entry',
            'amount' => 30000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'confirmed_by' => $this->user->id,
            'confirmed_at' => now()
        ]);

        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $expenseCategory->id,
            'transaction_date' => now(),
            'description' => 'Different Expense Entry',
            'amount' => 10000000,
            'type' => 'expense',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'confirmed_by' => $this->user->id,
            'confirmed_at' => now()
        ]);

        // Test type filter
        $response = $this->get(route('finance.cashflow.index', ['type' => 'income']));
        $response->assertStatus(200);
        $response->assertSee('Searchable Income Entry');
        $response->assertDontSee('Different Expense Entry');

        // Test search functionality
        $response = $this->get(route('finance.cashflow.index', ['search' => 'Searchable']));
        $response->assertStatus(200);
        $response->assertSee('Searchable Income Entry');
        $response->assertDontSee('Different Expense Entry');

        // Test category filter
        $response = $this->get(route('finance.cashflow.index', ['category_id' => $expenseCategory->id]));
        $response->assertStatus(200);
        $response->assertSee('Different Expense Entry');
        $response->assertDontSee('Searchable Income Entry');
    }
}