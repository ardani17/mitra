<?php

namespace App\Http\Controllers;

use App\Models\BillingBatch;
use App\Models\ProjectBilling;
use App\Models\ProjectPaymentSchedule;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class BillingDashboardController extends Controller
{
    public function __construct()
    {
        // Authentication handled by route middleware
    }

    /**
     * Display billing dashboard
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ProjectBilling::class);

        // Validate date inputs
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        // Date range filter with better defaults
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Ensure dates are properly formatted
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Overall statistics
        $overallStats = $this->getOverallStats($startDate, $endDate);

        // Batch billing statistics
        $batchStats = $this->getBatchBillingStats($startDate, $endDate);

        // Project billing statistics
        $projectStats = $this->getProjectBillingStats($startDate, $endDate);

        // Termin payment statistics
        $terminStats = $this->getTerminStats($startDate, $endDate);

        // Recent activities
        $recentActivities = $this->getRecentActivities();

        // Overdue items
        $overdueItems = $this->getOverdueItems();

        // Upcoming due dates
        $upcomingDues = $this->getUpcomingDues();

        // Monthly trends (last 6 months)
        $monthlyTrends = $this->getMonthlyTrends();

        // Additional KPIs
        $additionalKpis = $this->getAdditionalKpis($startDate, $endDate);

        return view('billing-dashboard.index', compact(
            'overallStats',
            'batchStats',
            'projectStats',
            'terminStats',
            'recentActivities',
            'overdueItems',
            'monthlyTrends',
            'additionalKpis'
        ))->with([
            'projectBillingStats' => $projectStats,
            'terminStats' => $terminStats,
            'upcomingDueDates' => $upcomingDues,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d')
        ]);
    }

    /**
     * Get overall billing statistics with caching
     */
    private function getOverallStats($startDate, $endDate)
    {
        $cacheKey = 'dashboard_overall_stats_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');
        
        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) { // Cache for 5 minutes
            $batchTotal = BillingBatch::whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_billing_amount');

            $projectTotal = ProjectBilling::whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount');

            $batchPaid = BillingBatch::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'paid')
                ->sum('total_received_amount');

            $projectPaid = ProjectBilling::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'paid')
                ->sum('total_amount');

            $totalAmount = $batchTotal + $projectTotal;
            $totalPaid = $batchPaid + $projectPaid;
            
            return [
                'total_billings' => BillingBatch::whereBetween('created_at', [$startDate, $endDate])->count() +
                                   ProjectBilling::whereBetween('created_at', [$startDate, $endDate])->count(),
                'total_amount' => $totalAmount,
                'paid_amount' => $totalPaid,
                'unpaid_amount' => $totalAmount - $totalPaid,
                'total_billed' => $totalAmount,
                'total_paid' => $totalPaid,
                'total_outstanding' => $totalAmount - $totalPaid,
                'payment_rate' => $totalAmount > 0
                    ? round(($totalPaid / $totalAmount) * 100, 2)
                    : 0
            ];
        });
    }

    /**
     * Get batch billing statistics
     */
    private function getBatchBillingStats($startDate, $endDate)
    {
        return [
            'total_batches' => BillingBatch::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_amount' => BillingBatch::whereBetween('created_at', [$startDate, $endDate])->sum('total_billing_amount'),
            'paid_amount' => BillingBatch::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'paid')->sum('total_received_amount'),
            'pending_count' => BillingBatch::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'pending')->count(),
            'overdue_count' => BillingBatch::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'overdue')->count()
        ];
    }

    /**
     * Get project billing statistics
     */
    private function getProjectBillingStats($startDate, $endDate)
    {
        $fullPayments = ProjectBilling::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_type', 'full')->count();
        
        $terminPayments = ProjectBilling::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_type', 'termin')->count();
            
        $fullAmount = ProjectBilling::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_type', 'full')->sum('total_amount');
            
        $terminAmount = ProjectBilling::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_type', 'termin')->sum('total_amount');
            
        $activeProjects = Project::whereHas('billings', function($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })->count();

        return [
            'total_billings' => ProjectBilling::whereBetween('created_at', [$startDate, $endDate])->count(),
            'full_payments' => $fullPayments,
            'termin_payments' => $terminPayments,
            'full_amount' => $fullAmount,
            'termin_amount' => $terminAmount,
            'active_projects' => $activeProjects,
            'total_amount' => ProjectBilling::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount'),
            'paid_amount' => ProjectBilling::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'paid')->sum('total_amount')
        ];
    }

    /**
     * Get termin payment statistics
     */
    private function getTerminStats($startDate, $endDate)
    {
        $totalSchedules = ProjectPaymentSchedule::whereBetween('created_at', [$startDate, $endDate])->count();
        $pendingSchedules = ProjectPaymentSchedule::where('status', 'pending')->count();
        $completedSchedules = ProjectPaymentSchedule::where('status', 'paid')->count();
        $averagePercentage = ProjectPaymentSchedule::avg('percentage') ?? 0;
        
        return [
            'total_schedules' => $totalSchedules,
            'pending_schedules' => $pendingSchedules,
            'completed_schedules' => $completedSchedules,
            'billed_schedules' => ProjectPaymentSchedule::where('status', 'billed')->count(),
            'paid_schedules' => ProjectPaymentSchedule::where('status', 'paid')->count(),
            'overdue_schedules' => ProjectPaymentSchedule::where('due_date', '<', now())
                ->where('status', '!=', 'paid')->count(),
            'total_scheduled_amount' => ProjectPaymentSchedule::sum('amount'),
            'average_percentage' => $averagePercentage
        ];
    }

    /**
     * Get recent billing activities
     */
    private function getRecentActivities()
    {
        $activities = collect();

        // Recent batch billings
        $recentBatches = BillingBatch::with(['projectBillings.project'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($batch) {
                $projectName = $batch->projectBillings->first()?->project?->name ?? 'Unknown Project';
                return [
                    'type' => 'batch',
                    'title' => "Batch Billing - {$projectName}",
                    'amount' => $batch->total_billing_amount,
                    'status' => $batch->status,
                    'date' => $batch->created_at,
                    'url' => route('billing-batches.show', $batch)
                ];
            });

        // Recent project billings
        $recentProjects = ProjectBilling::with('project')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($billing) {
                return [
                    'type' => 'project',
                    'title' => "Project Billing - {$billing->project->name}" . 
                              ($billing->isTerminPayment() ? " ({$billing->getTerminLabel()})" : ''),
                    'amount' => $billing->total_amount,
                    'status' => $billing->status,
                    'date' => $billing->created_at,
                    'url' => route('project-billings.show', $billing)
                ];
            });

        return $activities->merge($recentBatches)
                         ->merge($recentProjects)
                         ->sortByDesc('date')
                         ->take(10)
                         ->values();
    }

    /**
     * Get overdue items
     */
    private function getOverdueItems()
    {
        $overdue = collect();

        // Overdue batch billings - using created_at + 30 days as due date since due_date field was removed
        $overdueBatches = BillingBatch::with(['projectBillings.project'])
            ->whereRaw('created_at + INTERVAL \'30 days\' < ?', [now()])
            ->where('status', '!=', 'paid')
            ->orderBy('created_at')
            ->get()
            ->map(function ($batch) {
                $projectName = $batch->projectBillings->first()?->project?->name ?? 'Unknown Project';
                $estimatedDueDate = $batch->created_at->addDays(30);
                return [
                    'type' => 'batch',
                    'title' => "Batch - {$projectName}",
                    'amount' => $batch->total_billing_amount,
                    'due_date' => $estimatedDueDate,
                    'days_overdue' => now()->diffInDays($estimatedDueDate),
                    'url' => route('billing-batches.show', $batch)
                ];
            });

        // Overdue project billings - using created_at + 30 days as due date since due_date field was removed
        $overdueProjects = ProjectBilling::with('project')
            ->whereRaw('created_at + INTERVAL \'30 days\' < ?', [now()])
            ->where('status', '!=', 'paid')
            ->orderBy('created_at')
            ->get()
            ->map(function ($billing) {
                $estimatedDueDate = $billing->created_at->addDays(30);
                return [
                    'type' => 'project',
                    'title' => "Project - {$billing->project->name}" .
                              ($billing->isTerminPayment() ? " ({$billing->getTerminLabel()})" : ''),
                    'amount' => $billing->total_amount,
                    'due_date' => $estimatedDueDate,
                    'days_overdue' => now()->diffInDays($estimatedDueDate),
                    'url' => route('project-billings.show', $billing)
                ];
            });

        // Overdue payment schedules
        $overdueSchedules = ProjectPaymentSchedule::with('project')
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->orderBy('due_date')
            ->get()
            ->map(function ($schedule) {
                return [
                    'type' => 'schedule',
                    'title' => "Schedule - {$schedule->project->name} ({$schedule->termin_name})",
                    'amount' => $schedule->amount,
                    'due_date' => $schedule->due_date,
                    'days_overdue' => now()->diffInDays($schedule->due_date),
                    'url' => route('project-billings.manage-schedule', $schedule->project)
                ];
            });

        return $overdue->merge($overdueBatches)
                      ->merge($overdueProjects)
                      ->merge($overdueSchedules)
                      ->sortBy('due_date')
                      ->take(15)
                      ->values();
    }

    /**
     * Get upcoming due dates
     */
    private function getUpcomingDues()
    {
        $upcoming = collect();
        $nextWeek = now()->addWeek();

        // Upcoming batch billings - using created_at + 30 days as due date since due_date field was removed
        $upcomingBatches = BillingBatch::with(['projectBillings.project'])
            ->whereRaw('created_at + INTERVAL \'30 days\' BETWEEN ? AND ?', [now(), $nextWeek])
            ->where('status', '!=', 'paid')
            ->orderBy('created_at')
            ->get()
            ->map(function ($batch) {
                $projectName = $batch->projectBillings->first()?->project?->name ?? 'Unknown Project';
                $estimatedDueDate = $batch->created_at->addDays(30);
                return [
                    'type' => 'batch',
                    'title' => "Batch - {$projectName}",
                    'amount' => $batch->total_billing_amount,
                    'due_date' => $estimatedDueDate,
                    'days_until_due' => now()->diffInDays($estimatedDueDate, false),
                    'url' => route('billing-batches.show', $batch)
                ];
            });

        // Upcoming project billings - using created_at + 30 days as due date since due_date field was removed
        $upcomingProjects = ProjectBilling::with('project')
            ->whereRaw('created_at + INTERVAL \'30 days\' BETWEEN ? AND ?', [now(), $nextWeek])
            ->where('status', '!=', 'paid')
            ->orderBy('created_at')
            ->get()
            ->map(function ($billing) {
                $estimatedDueDate = $billing->created_at->addDays(30);
                return [
                    'type' => 'project',
                    'title' => "Project - {$billing->project->name}" .
                              ($billing->isTerminPayment() ? " ({$billing->getTerminLabel()})" : ''),
                    'amount' => $billing->total_amount,
                    'due_date' => $estimatedDueDate,
                    'days_until_due' => now()->diffInDays($estimatedDueDate, false),
                    'url' => route('project-billings.show', $billing)
                ];
            });

        // Upcoming payment schedules
        $upcomingSchedules = ProjectPaymentSchedule::with('project')
            ->whereBetween('due_date', [now(), $nextWeek])
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->get()
            ->map(function ($schedule) {
                return [
                    'type' => 'schedule',
                    'title' => "Schedule - {$schedule->project->name} ({$schedule->termin_name})",
                    'amount' => $schedule->amount,
                    'due_date' => $schedule->due_date,
                    'days_until_due' => now()->diffInDays($schedule->due_date, false),
                    'url' => route('project-billings.manage-schedule', $schedule->project)
                ];
            });

        return $upcoming->merge($upcomingBatches)
                       ->merge($upcomingProjects)
                       ->merge($upcomingSchedules)
                       ->sortBy('due_date')
                       ->take(15)
                       ->values();
    }

    /**
     * Get monthly billing trends
     */
    private function getMonthlyTrends()
    {
        $months = collect();
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $batchAmount = BillingBatch::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('total_billing_amount');

            $projectAmount = ProjectBilling::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('total_amount');

            $months->push([
                'month' => $date->format('M Y'),
                'batch_amount' => $batchAmount,
                'project_amount' => $projectAmount,
                'total_amount' => $batchAmount + $projectAmount
            ]);
        }

        return $months;
    }

    /**
     * Get additional KPIs for enhanced dashboard
     */
    private function getAdditionalKpis($startDate, $endDate)
    {
        // Payment rate calculation
        $totalAmount = BillingBatch::whereBetween('created_at', [$startDate, $endDate])->sum('total_billing_amount') +
                      ProjectBilling::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');
        
        $paidAmount = BillingBatch::whereBetween('created_at', [$startDate, $endDate])
                        ->where('status', 'paid')->sum('total_received_amount') +
                     ProjectBilling::whereBetween('created_at', [$startDate, $endDate])
                        ->where('status', 'paid')->sum('total_amount');

        $paymentRate = $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0;

        // Average billing amount
        $totalBillings = BillingBatch::whereBetween('created_at', [$startDate, $endDate])->count() +
                        ProjectBilling::whereBetween('created_at', [$startDate, $endDate])->count();
        
        $averageBillingAmount = $totalBillings > 0 ? $totalAmount / $totalBillings : 0;

        // Collection efficiency (paid vs overdue)
        $overdueAmount = BillingBatch::whereBetween('created_at', [$startDate, $endDate])
                           ->where('status', 'overdue')->sum('total_billing_amount') +
                        ProjectBilling::whereBetween('created_at', [$startDate, $endDate])
                           ->where('status', 'overdue')->sum('total_amount');

        $collectionEfficiency = $totalAmount > 0 ? (($totalAmount - $overdueAmount) / $totalAmount) * 100 : 0;

        // Monthly growth rate
        $previousMonth = Carbon::parse($startDate)->subMonth();
        $previousMonthAmount = BillingBatch::whereBetween('created_at', [
                                 $previousMonth->startOfMonth(),
                                 $previousMonth->endOfMonth()
                               ])->sum('total_billing_amount') +
                              ProjectBilling::whereBetween('created_at', [
                                 $previousMonth->startOfMonth(),
                                 $previousMonth->endOfMonth()
                               ])->sum('total_amount');

        $growthRate = $previousMonthAmount > 0 ? (($totalAmount - $previousMonthAmount) / $previousMonthAmount) * 100 : 0;

        return [
            'payment_rate' => round($paymentRate, 2),
            'average_billing_amount' => $averageBillingAmount,
            'collection_efficiency' => round($collectionEfficiency, 2),
            'growth_rate' => round($growthRate, 2),
            'total_projects_with_billing' => Project::whereHas('billings', function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })->count()
        ];
    }

    /**
     * Export dashboard data to Excel
     */
    public function export(Request $request)
    {
        Gate::authorize('viewAny', ProjectBilling::class);

        // Get date range
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Collect all dashboard data
        $data = [
            'overall_stats' => $this->getOverallStats($startDate, $endDate),
            'batch_stats' => $this->getBatchBillingStats($startDate, $endDate),
            'project_stats' => $this->getProjectBillingStats($startDate, $endDate),
            'termin_stats' => $this->getTerminStats($startDate, $endDate),
            'recent_activities' => $this->getRecentActivities(),
            'overdue_items' => $this->getOverdueItems(),
            'upcoming_dues' => $this->getUpcomingDues(),
            'monthly_trends' => $this->getMonthlyTrends(),
            'additional_kpis' => $this->getAdditionalKpis($startDate, $endDate),
            'export_date' => now()->format('Y-m-d H:i:s'),
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ]
        ];

        // Create filename with date range
        $filename = 'dashboard-penagihan-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.json';
        
        // For now, return JSON export (can be enhanced to Excel later)
        return response()->json($data)
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Get dashboard data via AJAX for real-time updates
     */
    public function getData(Request $request)
    {
        Gate::authorize('viewAny', ProjectBilling::class);

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        return response()->json([
            'success' => true,
            'data' => [
                'overall_stats' => $this->getOverallStats($startDate, $endDate),
                'batch_stats' => $this->getBatchBillingStats($startDate, $endDate),
                'project_stats' => $this->getProjectBillingStats($startDate, $endDate),
                'termin_stats' => $this->getTerminStats($startDate, $endDate),
                'recent_activities' => $this->getRecentActivities(),
                'overdue_items' => $this->getOverdueItems(),
                'upcoming_dues' => $this->getUpcomingDues(),
                'additional_kpis' => $this->getAdditionalKpis($startDate, $endDate),
                'last_updated' => now()->format('Y-m-d H:i:s')
            ]
        ]);
    }

    /**
     * Clear dashboard cache
     */
    public function clearCache(Request $request)
    {
        Gate::authorize('viewAny', ProjectBilling::class);

        // Clear all dashboard-related cache keys
        $patterns = [
            'dashboard_overall_stats_*',
            'dashboard_batch_stats_*',
            'dashboard_project_stats_*',
            'dashboard_termin_stats_*',
            'dashboard_additional_kpis_*'
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }

        // Clear cache by tags if using tagged cache
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags(['dashboard'])->flush();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cache dashboard berhasil dibersihkan'
        ]);
    }

    /**
     * Save dashboard preferences
     */
    public function savePreferences(Request $request)
    {
        Gate::authorize('viewAny', ProjectBilling::class);

        $validated = $request->validate([
            'auto_refresh' => 'boolean',
            'refresh_interval' => 'integer|min:60|max:3600', // 1 minute to 1 hour
            'show_notifications' => 'boolean',
            'default_date_range' => 'string|in:today,this_week,this_month,this_year',
            'widgets_order' => 'array',
            'widgets_visibility' => 'array'
        ]);

        $userId = auth()->id();
        $cacheKey = "dashboard_preferences_{$userId}";

        Cache::put($cacheKey, $validated, 86400); // Cache for 24 hours

        return response()->json([
            'success' => true,
            'message' => 'Preferensi dashboard berhasil disimpan'
        ]);
    }

    /**
     * Get dashboard preferences
     */
    public function getPreferences(Request $request)
    {
        Gate::authorize('viewAny', ProjectBilling::class);

        $userId = auth()->id();
        $cacheKey = "dashboard_preferences_{$userId}";

        $preferences = Cache::get($cacheKey, [
            'auto_refresh' => false,
            'refresh_interval' => 300, // 5 minutes
            'show_notifications' => true,
            'default_date_range' => 'this_month',
            'widgets_order' => ['stats', 'kpis', 'activities', 'overdue', 'upcoming', 'trends'],
            'widgets_visibility' => [
                'stats' => true,
                'kpis' => true,
                'activities' => true,
                'overdue' => true,
                'upcoming' => true,
                'trends' => true
            ]
        ]);

        return response()->json([
            'success' => true,
            'data' => $preferences
        ]);
    }
}