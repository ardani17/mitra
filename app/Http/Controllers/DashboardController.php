<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display dashboard utama
     */
    public function index()
    {
        $user = auth()->user();
        $userRole = $user->roles->first()->name ?? 'staf';
        
        // Data berdasarkan role
        switch ($userRole) {
            case 'direktur':
                return $this->direkturDashboard();
            case 'project_manager':
                return $this->projectManagerDashboard();
            case 'finance_manager':
                return $this->financeManagerDashboard();
            default:
                return $this->stafDashboard();
        }
    }

    /**
     * Dashboard untuk Direktur
     */
    private function direkturDashboard()
    {
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'in_progress')->count();
        $totalRevenue = Project::sum('total_billed_amount') ?? 0;
        $totalExpenses = DB::table('project_expenses')
            ->where('status', 'approved')
            ->sum('amount') ?? 0;
        $netProfit = $totalRevenue - $totalExpenses;
        $totalBudget = Project::sum('planned_total_value') ?? 0;

        $projectsByStatus = Project::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $projectsByType = Project::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get();

        $pendingExpenses = DB::table('project_expenses')
            ->where('status', 'pending')
            ->count();

        $overdueInvoices = ProjectBilling::where('status', 'overdue')->count();

        $recentActivities = DB::table('project_activities')
            ->join('users', 'project_activities.user_id', '=', 'users.id')
            ->join('projects', 'project_activities.project_id', '=', 'projects.id')
            ->select('project_activities.*', 'users.name as user_name', 'projects.name as project_name')
            ->orderBy('project_activities.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.direktur', compact(
            'totalProjects', 'activeProjects', 'totalRevenue', 'totalExpenses', 
            'netProfit', 'totalBudget', 'projectsByStatus', 'projectsByType',
            'pendingExpenses', 'overdueInvoices', 'recentActivities'
        ));
    }

    /**
     * Dashboard untuk Project Manager
     */
    private function projectManagerDashboard()
    {
        $totalProjects = Project::count();
        $myActiveProjects = Project::where('status', 'in_progress')->count();
        $completedProjects = Project::where('status', 'completed')->count();
        $totalBudget = Project::sum('planned_total_value') ?? 0;
        $totalExpenses = DB::table('project_expenses')
            ->where('status', 'approved')
            ->sum('amount') ?? 0;
        $budgetUtilization = $totalBudget > 0 ? ($totalExpenses / $totalBudget) * 100 : 0;

        $projectsByType = Project::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get();

        $pendingExpenses = DB::table('project_expenses')
            ->where('status', 'pending')
            ->count();

        $urgentProjects = Project::where('status', 'in_progress')
            ->where('end_date', '<', now()->addDays(7))
            ->get();

        return view('dashboard.project-manager', compact(
            'totalProjects', 'myActiveProjects', 'completedProjects', 
            'totalBudget', 'totalExpenses', 'budgetUtilization',
            'projectsByType', 'pendingExpenses', 'urgentProjects'
        ));
    }

    /**
     * Dashboard untuk Finance Manager
     */
    private function financeManagerDashboard()
    {
        $totalRevenue = Project::sum('total_billed_amount') ?? 0;
        $pendingInvoices = ProjectBilling::where('status', 'sent')->sum('total_amount') ?? 0;
        $overdueInvoices = ProjectBilling::where('status', 'overdue')->sum('total_amount') ?? 0;
        $totalExpenses = DB::table('project_expenses')
            ->where('status', 'approved')
            ->sum('amount') ?? 0;

        $monthlyRevenue = Project::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_billed_amount') ?? 0;

        $monthlyExpenses = DB::table('project_expenses')
            ->where('status', 'approved')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount') ?? 0;

        $pendingExpenses = DB::table('project_expenses')
            ->where('status', 'pending')
            ->count();

        $pendingExpensesAmount = DB::table('project_expenses')
            ->where('status', 'pending')
            ->sum('amount') ?? 0;

        $billingsByStatus = ProjectBilling::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $topProjects = Project::select('id', 'name', 'total_billed_amount as total_revenue')
            ->orderBy('total_billed_amount', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.finance-manager', compact(
            'totalRevenue', 'pendingInvoices', 'overdueInvoices', 'totalExpenses',
            'monthlyRevenue', 'monthlyExpenses', 'pendingExpenses', 'pendingExpensesAmount',
            'billingsByStatus', 'topProjects'
        ));
    }

    /**
     * Dashboard untuk Staf
     */
    private function stafDashboard()
    {
        $userId = auth()->id();
        
        $activeProjects = Project::where('status', 'in_progress')->count();
        $myExpenses = DB::table('project_expenses')
            ->where('user_id', $userId)
            ->count();
        $myPendingExpenses = DB::table('project_expenses')
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->count();
        $myApprovedExpenses = DB::table('project_expenses')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->count();

        $recentExpenses = DB::table('project_expenses')
            ->join('projects', 'project_expenses.project_id', '=', 'projects.id')
            ->where('project_expenses.user_id', $userId)
            ->select('project_expenses.*', 'projects.name as project_name')
            ->orderBy('project_expenses.created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($expense) {
                $expense->project = (object)['name' => $expense->project_name];
                return $expense;
            });

        $projectsByStatus = Project::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

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
     * Get analytics data untuk dashboard charts
     */
    public function analytics(Request $request)
    {
        $year = $request->get('year', date('Y'));
        
        // Data Tipe Proyek
        $projectTypes = Project::select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(COALESCE(planned_total_value, 0)) as total_value'))
            ->whereYear('created_at', $year)
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $this->getProjectTypeLabel($item->type),
                    'value' => $item->count,
                    'total_value' => $item->total_value ?? 0,
                    'formatted_value' => 'Rp ' . number_format($item->total_value ?? 0, 0, ',', '.')
                ];
            });

        // Data Lokasi Proyek
        $projectLocations = Project::select('location', DB::raw('COUNT(*) as count'), DB::raw('SUM(COALESCE(planned_total_value, 0)) as total_value'))
            ->whereYear('created_at', $year)
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->groupBy('location')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->location,
                    'value' => $item->count,
                    'total_value' => $item->total_value ?? 0,
                    'formatted_value' => 'Rp ' . number_format($item->total_value ?? 0, 0, ',', '.')
                ];
            });

        // Data Status Penagihan
        $billingStatus = Project::select('billing_status', DB::raw('COUNT(*) as count'), DB::raw('SUM(COALESCE(total_billed_amount, 0)) as total_value'))
            ->whereYear('created_at', $year)
            ->groupBy('billing_status')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $this->getBillingStatusLabel($item->billing_status),
                    'value' => $item->count,
                    'total_value' => $item->total_value ?? 0,
                    'formatted_value' => 'Rp ' . number_format($item->total_value ?? 0, 0, ',', '.')
                ];
            });

        // Data Status Proyek
        $projectStatus = Project::select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(COALESCE(planned_total_value, 0)) as total_value'))
            ->whereYear('created_at', $year)
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $this->getProjectStatusLabel($item->status),
                    'value' => $item->count,
                    'total_value' => $item->total_value ?? 0,
                    'formatted_value' => 'Rp ' . number_format($item->total_value ?? 0, 0, ',', '.')
                ];
            });

        // Data Status Pembayaran dari ProjectBilling
        $paymentStatus = ProjectBilling::select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(COALESCE(total_amount, 0)) as total_value'))
            ->whereYear('created_at', $year)
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $this->getPaymentStatusLabel($item->status),
                    'value' => $item->count,
                    'total_value' => $item->total_value ?? 0,
                    'formatted_value' => 'Rp ' . number_format($item->total_value ?? 0, 0, ',', '.')
                ];
            });

        // Summary data
        $totalProjects = Project::whereYear('created_at', $year)->count();
        $totalValue = Project::whereYear('created_at', $year)->sum('planned_total_value') ?? 0;
        $totalBilled = Project::whereYear('created_at', $year)->sum('total_billed_amount') ?? 0;
        $totalExpenses = Project::whereYear('created_at', $year)
            ->with(['expenses' => function($query) {
                $query->where('status', 'approved');
            }])
            ->get()
            ->sum(function($project) {
                return $project->expenses->sum('amount');
            });

        return response()->json([
            'year' => $year,
            'summary' => [
                'total_projects' => $totalProjects,
                'total_value' => $totalValue,
                'total_billed' => $totalBilled,
                'total_expenses' => $totalExpenses,
                'net_profit' => $totalBilled - $totalExpenses,
                'formatted_total_value' => 'Rp ' . number_format($totalValue, 0, ',', '.'),
                'formatted_total_billed' => 'Rp ' . number_format($totalBilled, 0, ',', '.'),
                'formatted_total_expenses' => 'Rp ' . number_format($totalExpenses, 0, ',', '.'),
                'formatted_net_profit' => 'Rp ' . number_format($totalBilled - $totalExpenses, 0, ',', '.')
            ],
            'charts' => [
                'project_types' => $projectTypes,
                'project_locations' => $projectLocations,
                'billing_status' => $billingStatus,
                'project_status' => $projectStatus,
                'payment_status' => $paymentStatus
            ]
        ]);
    }

    /**
     * Get available years untuk filter
     */
    public function getAvailableYears()
    {
        $years = Project::selectRaw('DISTINCT YEAR(created_at) as year')
            ->whereNotNull('created_at')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Tambahkan tahun saat ini jika belum ada
        $currentYear = date('Y');
        if (!in_array($currentYear, $years)) {
            array_unshift($years, $currentYear);
        }

        return response()->json($years);
    }

    /**
     * Helper method untuk label tipe proyek
     */
    private function getProjectTypeLabel($type)
    {
        return match($type) {
            'konstruksi' => 'Konstruksi',
            'maintenance' => 'Maintenance',
            'psb' => 'PSB',
            'other' => 'Lainnya',
            default => ucfirst($type)
        };
    }

    /**
     * Helper method untuk label status penagihan
     */
    private function getBillingStatusLabel($status)
    {
        return match($status) {
            'not_billed' => 'Belum Ditagih',
            'partially_billed' => 'Sebagian Ditagih',
            'fully_billed' => 'Sudah Ditagih',
            default => ucfirst($status)
        };
    }

    /**
     * Helper method untuk label status proyek
     */
    private function getProjectStatusLabel($status)
    {
        return match($status) {
            'planning' => 'Perencanaan',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($status)
        };
    }

    /**
     * Helper method untuk label status pembayaran
     */
    private function getPaymentStatusLabel($status)
    {
        return match($status) {
            'draft' => 'Draft',
            'sent' => 'Terkirim',
            'paid' => 'Terbayar',
            'overdue' => 'Terlambat',
            default => ucfirst($status)
        };
    }
}
