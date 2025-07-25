<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectBilling;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Constructor kosong karena middleware sudah ditangani di routes
    }

    /**
     * Display dashboard based on user role
     */
    public function index()
    {
        $user = Auth::user();
        $userRoles = $user->roles->pluck('name')->toArray();

        // Dashboard untuk Direktur
        if (in_array('direktur', $userRoles)) {
            return $this->direkturDashboard();
        }

        // Dashboard untuk Project Manager
        if (in_array('project_manager', $userRoles)) {
            return $this->projectManagerDashboard();
        }

        // Dashboard untuk Finance Manager
        if (in_array('finance_manager', $userRoles)) {
            return $this->financeManagerDashboard();
        }

        // Dashboard untuk Staf
        if (in_array('staf', $userRoles)) {
            return $this->stafDashboard();
        }

        // Default dashboard jika tidak ada role yang cocok
        return $this->defaultDashboard();
    }

    /**
     * Dashboard untuk Direktur - Overview lengkap perusahaan
     */
    private function direkturDashboard()
    {
        $totalProjects = Project::count();
        $activeProjects = Project::whereIn('status', ['on_progress', 'planning'])->count();
        $completedProjects = Project::where('status', 'completed')->count();
        $totalCompanies = Company::count();

        // Statistik keuangan
        $totalBudget = Project::sum('planned_budget');
        $totalExpenses = ProjectExpense::where('status', 'approved')->sum('amount');
        $totalRevenue = ProjectBilling::where('status', 'paid')->sum('total_amount');
        $netProfit = $totalRevenue - $totalExpenses;

        // Proyek berdasarkan status
        $projectsByStatus = Project::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // Proyek berdasarkan tipe
        $projectsByType = Project::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get();

        // Pengeluaran pending approval
        $pendingExpenses = ProjectExpense::where('status', 'submitted')->count();

        // Penagihan overdue
        $overdueInvoices = ProjectBilling::where('status', 'overdue')->count();

        // Recent activities
        $recentActivities = DB::table('project_activities')
            ->join('projects', 'project_activities.project_id', '=', 'projects.id')
            ->join('users', 'project_activities.user_id', '=', 'users.id')
            ->select('project_activities.*', 'projects.name as project_name', 'users.name as user_name')
            ->orderBy('project_activities.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.direktur', compact(
            'totalProjects', 'activeProjects', 'completedProjects', 'totalCompanies',
            'totalBudget', 'totalExpenses', 'totalRevenue', 'netProfit',
            'projectsByStatus', 'projectsByType', 'pendingExpenses', 'overdueInvoices',
            'recentActivities'
        ));
    }

    /**
     * Dashboard untuk Project Manager - Fokus pada manajemen proyek
     */
    private function projectManagerDashboard()
    {
        $totalProjects = Project::count();
        $myActiveProjects = Project::whereIn('status', ['on_progress', 'planning'])->count();
        $completedProjects = Project::where('status', 'completed')->count();

        // Budget tracking
        $totalBudget = Project::sum('planned_budget');
        $totalExpenses = ProjectExpense::where('status', 'approved')->sum('amount');
        $budgetUtilization = $totalBudget > 0 ? ($totalExpenses / $totalBudget) * 100 : 0;

        // Proyek berdasarkan tipe (karena tidak ada kolom priority)
        $projectsByType = Project::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get();

        // Pengeluaran pending approval
        $pendingExpenses = ProjectExpense::where('status', 'submitted')->count();

        // Proyek yang perlu perhatian (overdue)
        $urgentProjects = Project::where(function($query) {
            $query->where('end_date', '<', now())
                  ->where('status', '!=', 'completed');
        })->limit(5)->get();

        return view('dashboard.project-manager', compact(
            'totalProjects', 'myActiveProjects', 'completedProjects',
            'totalBudget', 'totalExpenses', 'budgetUtilization',
            'projectsByType', 'pendingExpenses', 'urgentProjects'
        ));
    }

    /**
     * Dashboard untuk Finance Manager - Fokus pada keuangan
     */
    private function financeManagerDashboard()
    {
        // Statistik keuangan
        $totalRevenue = ProjectBilling::where('status', 'paid')->sum('total_amount');
        $pendingInvoices = ProjectBilling::whereIn('status', ['draft', 'sent'])->sum('total_amount');
        $overdueInvoices = ProjectBilling::where('status', 'overdue')->sum('total_amount');
        $totalExpenses = ProjectExpense::where('status', 'approved')->sum('amount');

        // Pengeluaran pending review
        $pendingExpenses = ProjectExpense::where('status', 'submitted')->count();
        $pendingExpensesAmount = ProjectExpense::where('status', 'submitted')->sum('amount');

        // Cash flow bulanan
        $monthlyRevenue = ProjectBilling::where('status', 'paid')
            ->whereMonth('paid_date', now()->month)
            ->whereYear('paid_date', now()->year)
            ->sum('total_amount');

        $monthlyExpenses = ProjectExpense::where('status', 'approved')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        // Penagihan berdasarkan status
        $billingsByStatus = ProjectBilling::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // Top 5 proyek berdasarkan revenue
        $topProjects = Project::join('project_billings', 'projects.id', '=', 'project_billings.project_id')
            ->where('project_billings.status', 'paid')
            ->select('projects.name', DB::raw('SUM(project_billings.total_amount) as total_revenue'))
            ->groupBy('projects.id', 'projects.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.finance-manager', compact(
            'totalRevenue', 'pendingInvoices', 'overdueInvoices', 'totalExpenses',
            'pendingExpenses', 'pendingExpensesAmount', 'monthlyRevenue', 'monthlyExpenses',
            'billingsByStatus', 'topProjects'
        ));
    }

    /**
     * Dashboard untuk Staf - Fokus pada tugas dan proyek yang dikerjakan
     */
    private function stafDashboard()
    {
        $userId = Auth::id();

        // Proyek yang sedang dikerjakan
        $activeProjects = Project::whereIn('status', ['on_progress', 'planning'])->count();

        // Pengeluaran yang dibuat oleh staf
        $myExpenses = ProjectExpense::where('user_id', $userId)->count();
        $myPendingExpenses = ProjectExpense::where('user_id', $userId)
            ->where('status', 'submitted')
            ->count();
        $myApprovedExpenses = ProjectExpense::where('user_id', $userId)
            ->where('status', 'approved')
            ->count();

        // Recent expenses
        $recentExpenses = ProjectExpense::with(['project'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Proyek berdasarkan status
        $projectsByStatus = Project::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // Activities yang dilakukan oleh staf
        $myActivities = DB::table('project_activities')
            ->join('projects', 'project_activities.project_id', '=', 'projects.id')
            ->where('project_activities.user_id', $userId)
            ->select('project_activities.*', 'projects.name as project_name')
            ->orderBy('project_activities.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.staf', compact(
            'activeProjects', 'myExpenses', 'myPendingExpenses', 'myApprovedExpenses',
            'recentExpenses', 'projectsByStatus', 'myActivities'
        ));
    }

    /**
     * Default dashboard
     */
    private function defaultDashboard()
    {
        $totalProjects = Project::count();
        $activeProjects = Project::whereIn('status', ['on_progress', 'planning'])->count();

        return view('dashboard.default', compact('totalProjects', 'activeProjects'));
    }
}
