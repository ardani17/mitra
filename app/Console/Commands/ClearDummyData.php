<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectBilling;
use App\Models\ProjectTimeline;
use App\Models\ProjectRevenue;
use App\Models\ProjectActivity;
use App\Models\ProjectDocument;
use App\Models\ProjectProfitAnalysis;
use App\Models\RevenueItem;
use App\Models\Company;
use App\Models\BillingBatch;
use App\Models\BillingStatusLog;
use App\Models\BillingDocument;
use App\Models\ExpenseApproval;
use App\Models\ImportLog;
use Illuminate\Support\Facades\DB;

class ClearDummyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clear-dummy {--confirm : Konfirmasi untuk menghapus data dummy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan semua data dummy dari database (kecuali users dan roles)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('confirm')) {
            $this->error('PERINGATAN: Command ini akan menghapus SEMUA data proyek, expenses, billing, dll.');
            $this->error('Data users dan roles akan tetap ada.');
            $this->info('Jalankan dengan --confirm untuk melanjutkan:');
            $this->info('php artisan db:clear-dummy --confirm');
            return 1;
        }

        $this->info('Memulai pembersihan data dummy...');

        try {
            DB::beginTransaction();

            // Hitung data sebelum dihapus
            $projectCount = Project::count();
            $expenseCount = ProjectExpense::count();
            $billingCount = ProjectBilling::count();
            $timelineCount = ProjectTimeline::count();
            $revenueCount = ProjectRevenue::count();
            $activityCount = ProjectActivity::count();
            $companyCount = Company::count();
            $billingBatchCount = BillingBatch::count();
            $expenseApprovalCount = ExpenseApproval::count();
            $projectDocumentCount = ProjectDocument::count();
            $revenueItemCount = RevenueItem::count();
            $profitAnalysisCount = ProjectProfitAnalysis::count();
            $billingStatusLogCount = BillingStatusLog::count();
            $billingDocumentCount = BillingDocument::count();
            $importLogCount = ImportLog::count();

            $this->info("Data yang akan dihapus:");
            $this->info("- Projects: {$projectCount}");
            $this->info("- Project Expenses: {$expenseCount}");
            $this->info("- Project Billings: {$billingCount}");
            $this->info("- Project Timelines: {$timelineCount}");
            $this->info("- Project Revenues: {$revenueCount}");
            $this->info("- Project Activities: {$activityCount}");
            $this->info("- Companies: {$companyCount}");
            $this->info("- Billing Batches: {$billingBatchCount}");
            $this->info("- Expense Approvals: {$expenseApprovalCount}");
            $this->info("- Project Documents: {$projectDocumentCount}");
            $this->info("- Revenue Items: {$revenueItemCount}");
            $this->info("- Profit Analysis: {$profitAnalysisCount}");
            $this->info("- Billing Status Logs: {$billingStatusLogCount}");
            $this->info("- Billing Documents: {$billingDocumentCount}");
            $this->info("- Import Logs: {$importLogCount}");

            // Hapus data dalam urutan yang benar (foreign key constraints)
            $this->info('Menghapus Billing Documents...');
            BillingDocument::truncate();

            $this->info('Menghapus Billing Status Logs...');
            BillingStatusLog::truncate();

            $this->info('Menghapus Project Documents...');
            ProjectDocument::truncate();

            $this->info('Menghapus Revenue Items...');
            RevenueItem::truncate();

            $this->info('Menghapus Project Profit Analysis...');
            ProjectProfitAnalysis::truncate();

            $this->info('Menghapus Expense Approvals...');
            ExpenseApproval::truncate();

            $this->info('Menghapus Project Activities...');
            ProjectActivity::truncate();

            $this->info('Menghapus Project Revenues...');
            ProjectRevenue::truncate();

            $this->info('Menghapus Project Timelines...');
            ProjectTimeline::truncate();

            $this->info('Menghapus Project Billings...');
            ProjectBilling::truncate();

            $this->info('Menghapus Billing Batches...');
            BillingBatch::truncate();

            $this->info('Menghapus Project Expenses...');
            ProjectExpense::truncate();

            $this->info('Menghapus Projects...');
            Project::truncate();

            $this->info('Menghapus Companies...');
            Company::truncate();

            $this->info('Menghapus Import Logs...');
            ImportLog::truncate();

            // Reset auto increment
            $this->info('Reset auto increment counters...');
            DB::statement('ALTER SEQUENCE projects_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE project_expenses_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE project_billings_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE project_timelines_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE project_revenues_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE project_activities_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE companies_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE billing_batches_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE expense_approvals_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE project_documents_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE revenue_items_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE project_profit_analyses_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE billing_status_logs_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE billing_documents_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE import_logs_id_seq RESTART WITH 1');

            DB::commit();

            $this->info('✅ Data dummy berhasil dibersihkan!');
            $this->info('Database sekarang bersih dan siap untuk data production.');
            
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error saat membersihkan data: ' . $e->getMessage());
            return 1;
        }
    }
}
