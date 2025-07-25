<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectBilling;
use App\Models\ProjectRevenue;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\FinancialReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Display financial reports dashboard
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);
        
        // Default date range (current month)
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Project summary
        $projectStats = $this->getProjectStats($startDate, $endDate);
        
        // Financial summary
        $financialStats = $this->getFinancialStats($startDate, $endDate);
        
        // Monthly trends
        $monthlyTrends = $this->getMonthlyTrends();
        
        // Top projects by revenue
        $topProjects = $this->getTopProjects($startDate, $endDate);
        
        return view('reports.index', compact(
            'projectStats',
            'financialStats', 
            'monthlyTrends',
            'topProjects',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Get project statistics
     */
    private function getProjectStats($startDate, $endDate)
    {
        $query = Project::whereBetween('created_at', [$startDate, $endDate]);
        
        return [
            'total_projects' => $query->count(),
            'completed_projects' => $query->where('status', 'completed')->count(),
            'in_progress_projects' => $query->where('status', 'in_progress')->count(),
            'planning_projects' => $query->where('status', 'planning')->count(),
            'cancelled_projects' => $query->where('status', 'cancelled')->count(),
            'total_planned_value' => $query->sum('planned_total_value'),
            'total_final_value' => $query->sum('final_total_value'),
        ];
    }
    
    /**
     * Get financial statistics
     */
    private function getFinancialStats($startDate, $endDate)
    {
        // Total expenses
        $totalExpenses = ProjectExpense::whereBetween('expense_date', [$startDate, $endDate])
                                     ->where('status', 'approved')
                                     ->sum('amount');
        
        // Total revenue
        $totalRevenue = ProjectRevenue::whereBetween('revenue_date', [$startDate, $endDate])
                                    ->sum('amount');
        
        // Total billing
        $totalBilling = ProjectBilling::whereBetween('billing_date', [$startDate, $endDate])
                                    ->where('status', 'paid')
                                    ->sum('amount');
        
        // Net profit
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
        
        return [
            'total_expenses' => $totalExpenses,
            'total_revenue' => $totalRevenue,
            'total_billing' => $totalBilling,
            'net_profit' => $netProfit,
            'profit_margin' => $profitMargin,
        ];
    }
    
    /**
     * Get monthly trends for the last 12 months
     */
    private function getMonthlyTrends()
    {
        $months = [];
        $revenues = [];
        $expenses = [];
        $profits = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            
            $monthRevenue = ProjectRevenue::whereBetween('revenue_date', [$monthStart, $monthEnd])
                                        ->sum('amount');
            
            $monthExpense = ProjectExpense::whereBetween('expense_date', [$monthStart, $monthEnd])
                                        ->where('status', 'approved')
                                        ->sum('amount');
            
            $months[] = $date->format('M Y');
            $revenues[] = $monthRevenue;
            $expenses[] = $monthExpense;
            $profits[] = $monthRevenue - $monthExpense;
        }
        
        return [
            'months' => $months,
            'revenues' => $revenues,
            'expenses' => $expenses,
            'profits' => $profits,
        ];
    }
    
    /**
     * Get top projects by revenue
     */
    private function getTopProjects($startDate, $endDate)
    {
        return Project::select('projects.*')
                     ->selectRaw('COALESCE(SUM(project_revenues.amount), 0) as total_revenue')
                     ->selectRaw('COALESCE(SUM(project_expenses.amount), 0) as total_expenses')
                     ->selectRaw('COALESCE(SUM(project_revenues.amount), 0) - COALESCE(SUM(project_expenses.amount), 0) as net_profit')
                     ->leftJoin('project_revenues', function($join) use ($startDate, $endDate) {
                         $join->on('projects.id', '=', 'project_revenues.project_id')
                              ->whereBetween('project_revenues.revenue_date', [$startDate, $endDate]);
                     })
                     ->leftJoin('project_expenses', function($join) use ($startDate, $endDate) {
                         $join->on('projects.id', '=', 'project_expenses.project_id')
                              ->where('project_expenses.status', 'approved')
                              ->whereBetween('project_expenses.expense_date', [$startDate, $endDate]);
                     })
                     ->groupBy('projects.id')
                     ->orderByDesc('total_revenue')
                     ->limit(10)
                     ->get();
    }
    
    /**
     * Export financial report to Excel
     */
    public function exportFinancial(Request $request)
    {
        $this->authorize('viewAny', Project::class);
        
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Get detailed financial data
        $projects = Project::with(['expenses' => function($query) use ($startDate, $endDate) {
                                $query->whereBetween('expense_date', [$startDate, $endDate])
                                      ->where('status', 'approved');
                            }, 'revenues' => function($query) use ($startDate, $endDate) {
                                $query->whereBetween('revenue_date', [$startDate, $endDate]);
                            }])
                           ->get();
        
        $filename = 'laporan_keuangan_' . $startDate . '_to_' . $endDate . '.xlsx';
        
        return Excel::download(new FinancialReportExport($projects, $startDate, $endDate), $filename);
    }
    
    /**
     * Project profitability analysis
     */
    public function profitability(Request $request)
    {
        $this->authorize('viewAny', Project::class);
        
        $projects = Project::select('projects.*')
                          ->selectRaw('COALESCE(SUM(project_revenues.amount), 0) as total_revenue')
                          ->selectRaw('COALESCE(SUM(project_expenses.amount), 0) as total_expenses')
                          ->selectRaw('COALESCE(SUM(project_revenues.amount), 0) - COALESCE(SUM(project_expenses.amount), 0) as net_profit')
                          ->selectRaw('CASE 
                                        WHEN COALESCE(SUM(project_revenues.amount), 0) > 0 
                                        THEN ((COALESCE(SUM(project_revenues.amount), 0) - COALESCE(SUM(project_expenses.amount), 0)) / COALESCE(SUM(project_revenues.amount), 0)) * 100 
                                        ELSE 0 
                                      END as profit_margin')
                          ->leftJoin('project_revenues', 'projects.id', '=', 'project_revenues.project_id')
                          ->leftJoin('project_expenses', function($join) {
                              $join->on('projects.id', '=', 'project_expenses.project_id')
                                   ->where('project_expenses.status', 'approved');
                          })
                          ->groupBy('projects.id')
                          ->orderByDesc('profit_margin')
                          ->paginate(15);
        
        return view('reports.profitability', compact('projects'));
    }
}
