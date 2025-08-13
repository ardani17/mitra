<?php

namespace App\Http\Controllers;

use App\Models\CashflowEntry;
use App\Models\CashflowCategory;
use App\Models\Project;
use App\Models\ProjectBilling;
use App\Models\ProjectExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceDashboardController extends Controller
{
    // Middleware will be applied in routes

    /**
     * Display finance dashboard
     */
    public function index(Request $request)
    {
        try {
            // Handle period filter
            $period = $request->get('period', 'this_month');
            $dateRange = $this->getDateRangeFromPeriod($period);
            
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];

            // Get summary data
            $summary = $this->getDashboardSummary($startDate, $endDate);

            // Recent transactions
            $recent_transactions = $this->getRecentTransactions();

            // Monthly trends (last 6 months)
            $monthly_trends = $this->getMonthlyTrends();

            // Category breakdown for expenses (default)
            $expense_categories = $this->getCategoryData('expense', $startDate, $endDate);
            $income_categories = $this->getCategoryData('income', $startDate, $endDate);

            // Top projects by revenue
            $top_projects = $this->getTopProjects($startDate, $endDate);

            return view('finance-dashboard.index', compact(
                'summary',
                'recent_transactions',
                'monthly_trends',
                'expense_categories',
                'income_categories',
                'top_projects',
                'startDate',
                'endDate'
            ));
        } catch (\Exception $e) {
            \Log::error('Finance Dashboard Error: ' . $e->getMessage());
            
            // Return with empty data if error occurs
            $summary = [
                'total_income' => 0,
                'total_income_formatted' => 'Rp 0',
                'income_count' => 0,
                'total_expense' => 0,
                'total_expense_formatted' => 'Rp 0',
                'expense_count' => 0,
                'net_cashflow' => 0,
                'net_cashflow_formatted' => 'Rp 0',
                'pending_count' => 0
            ];
            
            return view('finance-dashboard.index', [
                'summary' => $summary,
                'recent_transactions' => collect(),
                'monthly_trends' => collect(),
                'expense_categories' => collect(),
                'income_categories' => collect(),
                'top_projects' => collect(),
                'startDate' => now()->startOfMonth(),
                'endDate' => now()->endOfMonth()
            ]);
        }
    }

    /**
     * Get overall financial summary
     */
    private function getFinancialSummary($startDate, $endDate)
    {
        $cashflowBalance = CashflowEntry::getBalance($startDate, $endDate);
        
        // Total project value
        $totalProjectValue = Project::whereBetween('created_at', [$startDate, $endDate])
            ->sum('project_value');

        // Outstanding billings
        $outstandingBillings = ProjectBilling::where('status', '!=', 'paid')
            ->sum('total_amount');

        // Pending expenses
        $pendingExpenses = ProjectExpense::where('status', 'submitted')
            ->sum('amount');

        return [
            'cash_balance' => $cashflowBalance['balance'],
            'total_income' => $cashflowBalance['income'],
            'total_expense' => $cashflowBalance['expense'],
            'total_project_value' => $totalProjectValue,
            'outstanding_billings' => $outstandingBillings,
            'pending_expenses' => $pendingExpenses,
            'net_profit_margin' => $cashflowBalance['income'] > 0 
                ? (($cashflowBalance['income'] - $cashflowBalance['expense']) / $cashflowBalance['income']) * 100 
                : 0
        ];
    }

    /**
     * Get cashflow summary by type
     */
    private function getCashflowSummary($startDate, $endDate)
    {
        $summary = CashflowEntry::confirmed()
            ->dateRange($startDate, $endDate)
            ->selectRaw('
                type,
                reference_type,
                COUNT(*) as count,
                SUM(amount) as total
            ')
            ->groupBy('type', 'reference_type')
            ->get()
            ->groupBy('type');

        $result = [
            'income' => [
                'total' => 0,
                'count' => 0,
                'breakdown' => []
            ],
            'expense' => [
                'total' => 0,
                'count' => 0,
                'breakdown' => []
            ]
        ];

        foreach ($summary as $type => $items) {
            $result[$type]['total'] = $items->sum('total');
            $result[$type]['count'] = $items->sum('count');
            
            foreach ($items as $item) {
                $result[$type]['breakdown'][$item->reference_type] = [
                    'total' => $item->total,
                    'count' => $item->count
                ];
            }
        }

        return $result;
    }

    /**
     * Get recent transactions
     */
    private function getRecentTransactions()
    {
        return CashflowEntry::with(['project', 'category', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get monthly trends for the last 6 months
     */
    private function getMonthlyTrends()
    {
        $months = collect();
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $balance = CashflowEntry::getBalance($startOfMonth, $endOfMonth);

            $months->push([
                'month' => $date->format('M Y'),
                'month_short' => $date->format('M'),
                'income' => $balance['income'],
                'expense' => $balance['expense'],
                'balance' => $balance['balance']
            ]);
        }

        return $months;
    }

    /**
     * Get category breakdown
     */
    private function getCategoryBreakdown($startDate, $endDate)
    {
        return CashflowEntry::with('category')
            ->confirmed()
            ->dateRange($startDate, $endDate)
            ->selectRaw('
                category_id,
                type,
                COUNT(*) as count,
                SUM(amount) as total
            ')
            ->groupBy('category_id', 'type')
            ->get()
            ->groupBy('type')
            ->map(function ($items) {
                return $items->map(function ($item) {
                    return [
                        'category' => $item->category->name,
                        'total' => $item->total,
                        'count' => $item->count
                    ];
                })->sortByDesc('total')->values();
            });
    }

    /**
     * Get project financial overview
     */
    private function getProjectFinancialOverview($startDate, $endDate)
    {
        return Project::with(['billings', 'expenses'])
            ->whereHas('cashflowEntries', function ($query) use ($startDate, $endDate) {
                $query->dateRange($startDate, $endDate);
            })
            ->get()
            ->map(function ($project) use ($startDate, $endDate) {
                $income = $project->cashflowEntries()
                    ->income()
                    ->confirmed()
                    ->dateRange($startDate, $endDate)
                    ->sum('amount');

                $expense = $project->cashflowEntries()
                    ->expense()
                    ->confirmed()
                    ->dateRange($startDate, $endDate)
                    ->sum('amount');

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'income' => $income,
                    'expense' => $expense,
                    'profit' => $income - $expense,
                    'margin' => $income > 0 ? (($income - $expense) / $income) * 100 : 0
                ];
            })
            ->sortByDesc('profit')
            ->take(10)
            ->values();
    }

    /**
     * Get pending transactions
     */
    private function getPendingTransactions()
    {
        return CashflowEntry::with(['project', 'category', 'creator'])
            ->pending()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get dashboard data via AJAX
     */
    public function getDashboardData(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        return response()->json([
            'success' => true,
            'data' => [
                'financial_summary' => $this->getFinancialSummary($startDate, $endDate),
                'cashflow_summary' => $this->getCashflowSummary($startDate, $endDate),
                'recent_transactions' => $this->getRecentTransactions(),
                'category_breakdown' => $this->getCategoryBreakdown($startDate, $endDate),
                'project_overview' => $this->getProjectFinancialOverview($startDate, $endDate),
                'pending_transactions' => $this->getPendingTransactions(),
                'last_updated' => now()->format('Y-m-d H:i:s')
            ]
        ]);
    }

    /**
     * Get cashflow chart data
     */
    public function getCashflowChart(Request $request)
    {
        $period = $request->get('period', 'monthly'); // daily, weekly, monthly
        $months = (int) $request->get('months', 6);

        $data = collect();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $balance = CashflowEntry::getBalance($startOfMonth, $endOfMonth);

            $data->push([
                'period' => $date->format('M Y'),
                'income' => (float) $balance['income'],
                'expense' => (float) $balance['expense'],
                'balance' => (float) $balance['balance']
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Export dashboard data
     */
    public function export(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $data = [
            'export_date' => now()->format('Y-m-d H:i:s'),
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'financial_summary' => $this->getFinancialSummary($startDate, $endDate),
            'cashflow_summary' => $this->getCashflowSummary($startDate, $endDate),
            'monthly_trends' => $this->getMonthlyTrends(),
            'category_breakdown' => $this->getCategoryBreakdown($startDate, $endDate),
            'project_overview' => $this->getProjectFinancialOverview($startDate, $endDate)
        ];

        $filename = 'finance-dashboard-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.json';
        
        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Get date range from period filter
     */
    private function getDateRangeFromPeriod($period)
    {
        switch ($period) {
            case 'this_month':
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth()
                ];
            case 'last_month':
                return [
                    'start' => now()->subMonth()->startOfMonth(),
                    'end' => now()->subMonth()->endOfMonth()
                ];
            case 'this_quarter':
                return [
                    'start' => now()->startOfQuarter(),
                    'end' => now()->endOfQuarter()
                ];
            case 'this_year':
                return [
                    'start' => now()->startOfYear(),
                    'end' => now()->endOfYear()
                ];
            default:
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth()
                ];
        }
    }

    /**
     * Get dashboard summary data
     */
    private function getDashboardSummary($startDate, $endDate)
    {
        $income = CashflowEntry::income()
            ->confirmed()
            ->dateRange($startDate, $endDate)
            ->sum('amount');

        $expense = CashflowEntry::expense()
            ->confirmed()
            ->dateRange($startDate, $endDate)
            ->sum('amount');

        $income_count = CashflowEntry::income()
            ->confirmed()
            ->dateRange($startDate, $endDate)
            ->count();

        $expense_count = CashflowEntry::expense()
            ->confirmed()
            ->dateRange($startDate, $endDate)
            ->count();

        $pending_count = CashflowEntry::pending()
            ->count();

        return [
            'total_income' => $income,
            'total_income_formatted' => 'Rp ' . number_format($income, 0, ',', '.'),
            'income_count' => $income_count,
            'total_expense' => $expense,
            'total_expense_formatted' => 'Rp ' . number_format($expense, 0, ',', '.'),
            'expense_count' => $expense_count,
            'net_cashflow' => $income - $expense,
            'net_cashflow_formatted' => 'Rp ' . number_format($income - $expense, 0, ',', '.'),
            'pending_count' => $pending_count
        ];
    }

    /**
     * Get category data for charts
     */
    private function getCategoryData($type, $startDate, $endDate)
    {
        try {
            return CashflowEntry::with('category')
                ->where('type', $type)
                ->confirmed()
                ->dateRange($startDate, $endDate)
                ->selectRaw('category_id, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('category_id')
                ->orderByDesc('total')
                ->get()
                ->map(function ($item) {
                    return (object) [
                        'name' => $item->category ? $item->category->name : 'Unknown Category',
                        'total' => $item->total ?? 0,
                        'count' => $item->count ?? 0
                    ];
                });
        } catch (\Exception $e) {
            \Log::error('Error getting category data: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get top projects by revenue
     */
    private function getTopProjects($startDate, $endDate)
    {
        try {
            $projects = Project::whereHas('cashflowEntries', function ($query) use ($startDate, $endDate) {
                    $query->dateRange($startDate, $endDate);
                })
                ->withCount(['cashflowEntries as transactions_count' => function ($query) use ($startDate, $endDate) {
                    $query->dateRange($startDate, $endDate);
                }])
                ->get();

            return $projects->map(function ($project) use ($startDate, $endDate) {
                $income = $project->cashflowEntries()
                    ->income()
                    ->confirmed()
                    ->dateRange($startDate, $endDate)
                    ->sum('amount') ?? 0;

                $expense = $project->cashflowEntries()
                    ->expense()
                    ->confirmed()
                    ->dateRange($startDate, $endDate)
                    ->sum('amount') ?? 0;

                return (object) [
                    'id' => $project->id,
                    'name' => $project->name ?? 'Unknown Project',
                    'total_income' => $income,
                    'total_expense' => $expense,
                    'net_amount' => $income - $expense,
                    'transactions_count' => $project->transactions_count ?? 0
                ];
            })
            ->sortByDesc('total_income')
            ->take(5)
            ->values();
        } catch (\Exception $e) {
            \Log::error('Error getting top projects: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get summary data for AJAX
     */
    public function getSummary(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $dateRange = $this->getDateRangeFromPeriod($period);
        
        $summary = $this->getDashboardSummary($dateRange['start'], $dateRange['end']);
        
        return response()->json($summary);
    }

    /**
     * Get categories data for AJAX
     */
    public function getCategories(Request $request)
    {
        $type = $request->get('type', 'expense');
        $period = $request->get('period', 'this_month');
        $dateRange = $this->getDateRangeFromPeriod($period);
        
        $categories = $this->getCategoryData($type, $dateRange['start'], $dateRange['end']);
        
        return response()->json($categories);
    }

    /**
     * Generate cashflow report
     */
    public function cashflowReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Get all transactions in period
        $transactions = CashflowEntry::with(['category', 'project'])
            ->dateRange($startDate, $endDate)
            ->orderBy('transaction_date')
            ->get();

        // Calculate running balance
        $runningBalance = 0;
        $transactions = $transactions->map(function ($transaction) use (&$runningBalance) {
            if ($transaction->type === 'income') {
                $runningBalance += $transaction->amount;
            } else {
                $runningBalance -= $transaction->amount;
            }
            
            $transaction->running_balance = $runningBalance;
            return $transaction;
        });

        $summary = $this->getDashboardSummary($startDate, $endDate);

        return view('finance-dashboard.reports.cashflow', compact(
            'transactions',
            'summary',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate balance summary report
     */
    public function balanceSummary(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Category breakdown
        $categoryBreakdown = $this->getCategoryBreakdown($startDate, $endDate);
        
        // Project breakdown
        $projectBreakdown = $this->getProjectFinancialOverview($startDate, $endDate);
        
        // Monthly comparison
        $monthlyComparison = $this->getMonthlyTrends();
        
        $summary = $this->getDashboardSummary($startDate, $endDate);

        return view('finance-dashboard.reports.balance-summary', compact(
            'categoryBreakdown',
            'projectBreakdown',
            'monthlyComparison',
            'summary',
            'startDate',
            'endDate'
        ));
    }
}
