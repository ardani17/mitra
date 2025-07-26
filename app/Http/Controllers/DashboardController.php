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
            'netProfit', 'projectsByStatus', 'projectsByType',
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

        // Data Status Tagihan Berdasarkan Billing Batch
        $billingStatusData = $this->getBillingStatusData($year);

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
                'payment_status' => $paymentStatus,
                'billing_status_data' => $billingStatusData
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

    /**
     * Get project types data dengan filter advanced
     */
    public function getProjectTypes(Request $request)
    {
        $query = Project::query();

        // Apply filters
        $this->applyProjectFilters($query, $request);

        // Get data dengan grouping by type
        $projectTypes = $query->select([
                'type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(COALESCE(planned_total_value, 0)) as total_value'),
                DB::raw('AVG(COALESCE(planned_total_value, 0)) as avg_value')
            ])
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $this->getProjectTypeLabel($item->type),
                    'count' => $item->count,
                    'total_value' => $item->total_value ?? 0,
                    'avg_value' => $item->avg_value ?? 0,
                    'formatted_total_value' => 'Rp ' . number_format($item->total_value ?? 0, 0, ',', '.'),
                    'formatted_avg_value' => 'Rp ' . number_format($item->avg_value ?? 0, 0, ',', '.')
                ];
            });

        // Summary data - clone query untuk menghindari konflik
        $summaryQuery = clone $query;
        $totalProjects = $summaryQuery->count();
        
        $summaryQuery2 = clone $query;
        $totalValue = $summaryQuery2->sum('planned_total_value') ?? 0;
        
        $avgValue = $totalProjects > 0 ? $totalValue / $totalProjects : 0;

        return response()->json([
            'data' => $projectTypes,
            'summary' => [
                'total_projects' => $totalProjects,
                'total_value' => $totalValue,
                'avg_value' => $avgValue,
                'formatted_total_value' => 'Rp ' . number_format($totalValue, 0, ',', '.'),
                'formatted_avg_value' => 'Rp ' . number_format($avgValue, 0, ',', '.')
            ]
        ]);
    }

    /**
     * Get available locations untuk filter
     */
    public function getLocations()
    {
        $locations = Project::select('location')
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location')
            ->toArray();

        return response()->json($locations);
    }

    /**
     * Get available clients untuk filter
     */
    public function getClients()
    {
        $clients = Project::select('client')
            ->whereNotNull('client')
            ->where('client', '!=', '')
            ->distinct()
            ->orderBy('client')
            ->pluck('client')
            ->toArray();

        return response()->json($clients);
    }

    /**
     * Apply filters ke query berdasarkan request parameters
     */
    private function applyProjectFilters($query, Request $request)
    {
        // Period filter
        $period = $request->get('period');
        if ($period && $period !== 'all') {
            $this->applyPeriodFilter($query, $period, $request);
        }

        // Status filter
        $status = $request->get('status');
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Value range filter
        $valueRange = $request->get('valueRange');
        if ($valueRange && $valueRange !== 'all') {
            $this->applyValueRangeFilter($query, $valueRange, $request);
        }

        // Location filter
        $location = $request->get('location');
        if ($location && $location !== 'all') {
            $query->where('location', $location);
        }

        // Client filter
        $client = $request->get('client');
        if ($client && $client !== 'all') {
            $query->where('client', $client);
        }
    }

    /**
     * Apply period filter ke query
     */
    private function applyPeriodFilter($query, $period, Request $request)
    {
        $today = Carbon::today();

        switch ($period) {
            case 'today':
                $query->whereBetween('created_at', [
                    $today->copy()->startOfDay(),
                    $today->copy()->endOfDay()
                ]);
                break;
            case 'week':
                $query->whereBetween('created_at', [
                    $today->copy()->subWeek()->startOfDay(),
                    $today->copy()->endOfDay()
                ]);
                break;
            case 'month':
                $query->whereBetween('created_at', [
                    $today->copy()->startOfMonth()->startOfDay(),
                    $today->copy()->endOfDay()
                ]);
                break;
            case 'quarter':
                $query->whereBetween('created_at', [
                    $today->copy()->subMonths(3)->startOfDay(),
                    $today->copy()->endOfDay()
                ]);
                break;
            case 'semester':
                $query->whereBetween('created_at', [
                    $today->copy()->subMonths(6)->startOfDay(),
                    $today->copy()->endOfDay()
                ]);
                break;
            case 'year':
                $query->whereBetween('created_at', [
                    $today->copy()->subYear()->startOfDay(),
                    $today->copy()->endOfDay()
                ]);
                break;
            case 'custom':
                $startDate = $request->get('startDate');
                $endDate = $request->get('endDate');
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                }
                break;
        }
    }

    /**
     * Apply value range filter ke query
     */
    private function applyValueRangeFilter($query, $valueRange, Request $request)
    {
        switch ($valueRange) {
            case 'small':
                $query->where('planned_total_value', '<', 100000000); // < 100 juta
                break;
            case 'medium':
                $query->whereBetween('planned_total_value', [100000000, 1000000000]); // 100 juta - 1 miliar
                break;
            case 'large':
                $query->where('planned_total_value', '>', 1000000000); // > 1 miliar
                break;
            case 'custom':
                $minValue = $request->get('minValue');
                $maxValue = $request->get('maxValue');
                
                if ($minValue !== null && $minValue !== '') {
                    $query->where('planned_total_value', '>=', $minValue);
                }
                
                if ($maxValue !== null && $maxValue !== '') {
                    $query->where('planned_total_value', '<=', $maxValue);
                }
                break;
        }
    }

    /**
     * Get billing status data berdasarkan billing batch
     */
    private function getBillingStatusData($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        // 1. Belum Ditagih - HANYA proyek yang belum punya billing batch sama sekali
        $belumDigaih = Project::whereYear('created_at', $year)
            ->whereDoesntHave('billings.billingBatch')
            ->select(DB::raw('COUNT(*) as count'), DB::raw('SUM(COALESCE(planned_total_value, 0)) as total_value'))
            ->first();

        // 2. Tertagih - Status billing batch dari draft sampai regional verification/revision
        $tertagih = Project::whereYear('created_at', $year)
            ->whereHas('billings.billingBatch', function($q) {
                $q->whereIn('status', [
                    'draft', 
                    'sent', 
                    'area_verification', 
                    'area_revision', 
                    'regional_verification', 
                    'regional_revision'
                ]);
            })
            ->join('project_billings', 'projects.id', '=', 'project_billings.project_id')
            ->join('billing_batches', 'project_billings.billing_batch_id', '=', 'billing_batches.id')
            ->whereIn('billing_batches.status', [
                'draft', 
                'sent', 
                'area_verification', 
                'area_revision', 
                'regional_verification', 
                'regional_revision'
            ])
            ->select(
                DB::raw('COUNT(DISTINCT projects.id) as count'), 
                DB::raw('SUM(COALESCE(billing_batches.total_received_amount, 0)) as total_value')
            )
            ->first();

        // 3. Sudah Input Faktur Pajak - Status billing batch = payment_entry_ho
        $sudahInputFaktur = Project::whereYear('created_at', $year)
            ->whereHas('billings.billingBatch', function($q) {
                $q->where('status', 'payment_entry_ho');
            })
            ->join('project_billings', 'projects.id', '=', 'project_billings.project_id')
            ->join('billing_batches', 'project_billings.billing_batch_id', '=', 'billing_batches.id')
            ->where('billing_batches.status', 'payment_entry_ho')
            ->select(
                DB::raw('COUNT(DISTINCT projects.id) as count'), 
                DB::raw('SUM(COALESCE(billing_batches.total_received_amount, 0)) as total_value')
            )
            ->first();

        // 4. Lunas - Status paid
        $lunas = Project::whereYear('created_at', $year)
            ->whereHas('billings.billingBatch', function($q) {
                $q->where('status', 'paid');
            })
            ->join('project_billings', 'projects.id', '=', 'project_billings.project_id')
            ->join('billing_batches', 'project_billings.billing_batch_id', '=', 'billing_batches.id')
            ->where('billing_batches.status', 'paid')
            ->select(
                DB::raw('COUNT(DISTINCT projects.id) as count'), 
                DB::raw('SUM(COALESCE(billing_batches.total_received_amount, 0)) as total_value')
            )
            ->first();

        return [
            [
                'label' => 'Belum Ditagih',
                'value' => $belumDigaih->count ?? 0,
                'total_value' => $belumDigaih->total_value ?? 0,
                'formatted_value' => 'Rp ' . number_format($belumDigaih->total_value ?? 0, 0, ',', '.')
            ],
            [
                'label' => 'Tertagih',
                'value' => $tertagih->count ?? 0,
                'total_value' => $tertagih->total_value ?? 0,
                'formatted_value' => 'Rp ' . number_format($tertagih->total_value ?? 0, 0, ',', '.')
            ],
            [
                'label' => 'Sudah Input Faktur Pajak',
                'value' => $sudahInputFaktur->count ?? 0,
                'total_value' => $sudahInputFaktur->total_value ?? 0,
                'formatted_value' => 'Rp ' . number_format($sudahInputFaktur->total_value ?? 0, 0, ',', '.')
            ],
            [
                'label' => 'Lunas',
                'value' => $lunas->count ?? 0,
                'total_value' => $lunas->total_value ?? 0,
                'formatted_value' => 'Rp ' . number_format($lunas->total_value ?? 0, 0, ',', '.')
            ]
        ];
    }

    /**
     * Get billing status data dengan filter advanced
     */
    public function getBillingStatus(Request $request)
    {
        $query = Project::query();

        // Apply filters
        $this->applyProjectFilters($query, $request);

        // 1. Belum Ditagih - HANYA proyek yang belum punya billing batch sama sekali
        $belumDigaihQuery = clone $query;
        $belumDigaih = $belumDigaihQuery->whereDoesntHave('billings.billingBatch')
            ->select(DB::raw('COUNT(*) as count'), DB::raw('SUM(COALESCE(planned_total_value, 0)) as total_value'))
            ->first();

        // 2. Tertagih - Status billing batch dari draft sampai regional verification/revision
        $tertagihQuery = clone $query;
        $tertagih = $tertagihQuery->whereHas('billings.billingBatch', function($q) {
                $q->whereIn('status', [
                    'draft', 
                    'sent', 
                    'area_verification', 
                    'area_revision', 
                    'regional_verification', 
                    'regional_revision'
                ]);
            })
            ->join('project_billings', 'projects.id', '=', 'project_billings.project_id')
            ->join('billing_batches', 'project_billings.billing_batch_id', '=', 'billing_batches.id')
            ->whereIn('billing_batches.status', [
                'draft', 
                'sent', 
                'area_verification', 
                'area_revision', 
                'regional_verification', 
                'regional_revision'
            ])
            ->select(
                DB::raw('COUNT(DISTINCT projects.id) as count'), 
                DB::raw('SUM(COALESCE(billing_batches.total_received_amount, 0)) as total_value')
            )
            ->first();

        // 3. Sudah Input Faktur Pajak - Status billing batch = payment_entry_ho
        $sudahInputFakturQuery = clone $query;
        $sudahInputFaktur = $sudahInputFakturQuery->whereHas('billings.billingBatch', function($q) {
                $q->where('status', 'payment_entry_ho');
            })
            ->join('project_billings', 'projects.id', '=', 'project_billings.project_id')
            ->join('billing_batches', 'project_billings.billing_batch_id', '=', 'billing_batches.id')
            ->where('billing_batches.status', 'payment_entry_ho')
            ->select(
                DB::raw('COUNT(DISTINCT projects.id) as count'), 
                DB::raw('SUM(COALESCE(billing_batches.total_received_amount, 0)) as total_value')
            )
            ->first();

        // 4. Lunas - Status paid
        $lunasQuery = clone $query;
        $lunas = $lunasQuery->whereHas('billings.billingBatch', function($q) {
                $q->where('status', 'paid');
            })
            ->join('project_billings', 'projects.id', '=', 'project_billings.project_id')
            ->join('billing_batches', 'project_billings.billing_batch_id', '=', 'billing_batches.id')
            ->where('billing_batches.status', 'paid')
            ->select(
                DB::raw('COUNT(DISTINCT projects.id) as count'), 
                DB::raw('SUM(COALESCE(billing_batches.total_received_amount, 0)) as total_value')
            )
            ->first();

        $data = [
            [
                'label' => 'Belum Ditagih',
                'count' => $belumDigaih->count ?? 0,
                'total_value' => $belumDigaih->total_value ?? 0,
                'formatted_total_value' => 'Rp ' . number_format($belumDigaih->total_value ?? 0, 0, ',', '.')
            ],
            [
                'label' => 'Tertagih',
                'count' => $tertagih->count ?? 0,
                'total_value' => $tertagih->total_value ?? 0,
                'formatted_total_value' => 'Rp ' . number_format($tertagih->total_value ?? 0, 0, ',', '.')
            ],
            [
                'label' => 'Sudah Input Faktur Pajak',
                'count' => $sudahInputFaktur->count ?? 0,
                'total_value' => $sudahInputFaktur->total_value ?? 0,
                'formatted_total_value' => 'Rp ' . number_format($sudahInputFaktur->total_value ?? 0, 0, ',', '.')
            ],
            [
                'label' => 'Lunas',
                'count' => $lunas->count ?? 0,
                'total_value' => $lunas->total_value ?? 0,
                'formatted_total_value' => 'Rp ' . number_format($lunas->total_value ?? 0, 0, ',', '.')
            ]
        ];

        // Summary data
        $totalProjects = array_sum(array_column($data, 'count'));
        $totalValue = array_sum(array_column($data, 'total_value'));

        return response()->json([
            'data' => $data,
            'summary' => [
                'total_projects' => $totalProjects,
                'total_value' => $totalValue,
                'formatted_total_value' => 'Rp ' . number_format($totalValue, 0, ',', '.')
            ]
        ]);
    }
}
