<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Project;
use App\Models\CashflowEntry;
use App\Models\CashflowCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class CashflowModelTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $incomeCategory;
    protected $expenseCategory;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'finance_manager']);
        $this->project = Project::factory()->create();
        
        // Create categories
        $this->incomeCategory = CashflowCategory::factory()->create([
            'name' => 'Test Income Category',
            'type' => 'income'
        ]);
        
        $this->expenseCategory = CashflowCategory::factory()->create([
            'name' => 'Test Expense Category',
            'type' => 'expense'
        ]);
    }

    /** @test */
    public function cashflow_entry_can_be_created()
    {
        $entry = CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Test income entry',
            'amount' => 50000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'confirmed_by' => $this->user->id,
            'confirmed_at' => now()
        ]);

        $this->assertInstanceOf(CashflowEntry::class, $entry);
        $this->assertEquals('Test income entry', $entry->description);
        $this->assertEquals(50000000, $entry->amount);
        $this->assertEquals('income', $entry->type);
    }

    /** @test */
    public function cashflow_entry_has_correct_relationships()
    {
        $entry = CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Test relationship entry',
            'amount' => 25000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id
        ]);

        $this->assertEquals($this->project->id, $entry->project->id);
        $this->assertEquals($this->incomeCategory->id, $entry->category->id);
        $this->assertEquals($this->user->id, $entry->creator->id);
    }

    /** @test */
    public function cashflow_entry_scopes_work_correctly()
    {
        // Create income entry
        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Income entry',
            'amount' => 30000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id
        ]);

        // Create expense entry
        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->expenseCategory->id,
            'transaction_date' => now(),
            'description' => 'Expense entry',
            'amount' => 15000000,
            'type' => 'expense',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id
        ]);

        // Create pending entry
        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Pending entry',
            'amount' => 10000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'pending',
            'created_by' => $this->user->id
        ]);

        // Test scopes
        $this->assertEquals(2, CashflowEntry::income()->count());
        $this->assertEquals(1, CashflowEntry::expense()->count());
        $this->assertEquals(2, CashflowEntry::confirmed()->count());
        $this->assertEquals(1, CashflowEntry::pending()->count());
    }

    /** @test */
    public function get_balance_method_calculates_correctly()
    {
        $startDate = now()->startOfDay();
        $endDate = now()->endOfDay();

        // Create confirmed entries
        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Income 1',
            'amount' => 100000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id
        ]);

        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->expenseCategory->id,
            'transaction_date' => now(),
            'description' => 'Expense 1',
            'amount' => 40000000,
            'type' => 'expense',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id
        ]);

        // Create pending entry (should not be counted)
        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Pending income',
            'amount' => 20000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'pending',
            'created_by' => $this->user->id
        ]);

        $balance = CashflowEntry::getBalance($startDate, $endDate);

        $this->assertEquals(100000000, $balance['income']);
        $this->assertEquals(40000000, $balance['expense']);
        $this->assertEquals(60000000, $balance['balance']);
    }

    /** @test */
    public function formatted_attributes_work_correctly()
    {
        $incomeEntry = CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Test income',
            'amount' => 75000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id
        ]);

        $expenseEntry = CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->expenseCategory->id,
            'transaction_date' => now(),
            'description' => 'Test expense',
            'amount' => 25000000,
            'type' => 'expense',
            'reference_type' => 'manual',
            'status' => 'pending',
            'created_by' => $this->user->id
        ]);

        // Test formatted amount
        $this->assertEquals('Rp 75.000.000', $incomeEntry->formatted_amount);
        $this->assertEquals('Rp 25.000.000', $expenseEntry->formatted_amount);

        // Test formatted type
        $this->assertEquals('Pemasukan', $incomeEntry->formatted_type);
        $this->assertEquals('Pengeluaran', $expenseEntry->formatted_type);

        // Test formatted status
        $this->assertEquals('Terkonfirmasi', $incomeEntry->formatted_status);
        $this->assertEquals('Menunggu', $expenseEntry->formatted_status);
    }

    /** @test */
    public function can_be_edited_method_works_correctly()
    {
        // Manual entry should be editable
        $manualEntry = CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Manual entry',
            'amount' => 30000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id
        ]);

        // Auto-generated entry should not be editable
        $autoEntry = CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Auto entry',
            'amount' => 50000000,
            'type' => 'income',
            'reference_type' => 'billing',
            'reference_id' => 1,
            'status' => 'confirmed',
            'created_by' => $this->user->id
        ]);

        $this->assertTrue($manualEntry->canBeEdited());
        $this->assertFalse($autoEntry->canBeEdited());
    }

    /** @test */
    public function can_be_deleted_method_works_correctly()
    {
        // Manual confirmed entry should be deletable
        $manualEntry = CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Manual entry',
            'amount' => 30000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id
        ]);

        // Auto-generated entry should not be deletable
        $autoEntry = CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => now(),
            'description' => 'Auto entry',
            'amount' => 50000000,
            'type' => 'income',
            'reference_type' => 'billing',
            'reference_id' => 1,
            'status' => 'confirmed',
            'created_by' => $this->user->id
        ]);

        $this->assertTrue($manualEntry->canBeDeleted());
        $this->assertFalse($autoEntry->canBeDeleted());
    }

    /** @test */
    public function date_range_scope_works_correctly()
    {
        $today = now();
        $yesterday = now()->subDay();
        $tomorrow = now()->addDay();

        // Create entries on different dates
        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => $yesterday,
            'description' => 'Yesterday entry',
            'amount' => 10000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id
        ]);

        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => $today,
            'description' => 'Today entry',
            'amount' => 20000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id
        ]);

        CashflowEntry::create([
            'project_id' => $this->project->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => $tomorrow,
            'description' => 'Tomorrow entry',
            'amount' => 30000000,
            'type' => 'income',
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'created_by' => $this->user->id
        ]);

        // Test date range scope
        $todayEntries = CashflowEntry::dateRange($today->startOfDay(), $today->endOfDay())->count();
        $this->assertEquals(1, $todayEntries);

        $allEntries = CashflowEntry::dateRange($yesterday->startOfDay(), $tomorrow->endOfDay())->count();
        $this->assertEquals(3, $allEntries);
    }
}