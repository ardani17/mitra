<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectActivity;
use App\Models\Project;
use App\Helpers\ActivityLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ActivityReportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display activity report
     */
    public function index(Request $request)
    {
        $query = ProjectActivity::with(['project']);
        
        // Filter berdasarkan proyek
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        // Filter berdasarkan tipe aktivitas
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }
        
        // Filter berdasarkan user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter berdasarkan tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('activity_type', 'like', "%{$search}%")
                  ->orWhereHas('project', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);
        
        $activities = $query->paginate(20)->withQueryString();
        
        // Data untuk filter dropdown
        $projects = Project::orderBy('name')->get();
        $users = \App\Models\User::orderBy('name')->get();
        
        // Activity types untuk filter
        $activityTypes = [
            'project_created' => 'Proyek Dibuat',
            'project_updated' => 'Proyek Diperbarui',
            'expense_created' => 'Pengeluaran Dibuat',
            'expense_approval' => 'Approval Pengeluaran',
            'billing_created' => 'Invoice Dibuat',
            'billing_batch_created' => 'Batch Billing Dibuat',
            'billing_status_changed' => 'Status Invoice Berubah',
            'document_uploaded' => 'Dokumen Diunggah',
            'timeline_created' => 'Timeline Dibuat',
            'timeline_updated' => 'Timeline Diperbarui',
            'data_import' => 'Import Data',
            'data_export' => 'Export Data',
            'profit_analysis_updated' => 'Analisis Profit Diperbarui'
        ];
        
        return view('reports.activities', compact('activities', 'projects', 'users', 'activityTypes'));
    }
    
    /**
     * Show activity details
     */
    public function show($id)
    {
        $activity = ProjectActivity::with(['project'])->findOrFail($id);
        
        return view('reports.activity-detail', compact('activity'));
    }
    
    /**
     * Get recent activities for dashboard widget
     */
    public function recent(Request $request)
    {
        $limit = $request->get('limit', 10);
        $projectId = $request->get('project_id');
        
        $query = ProjectActivity::with(['project']);
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        $activities = $query->orderBy('created_at', 'desc')
                           ->limit($limit)
                           ->get();
        
        return response()->json($activities);
    }
    
    /**
     * Get activity statistics
     */
    public function statistics(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $query = ProjectActivity::whereDate('created_at', '>=', $dateFrom)
                               ->whereDate('created_at', '<=', $dateTo);
        
        // Total activities
        $totalActivities = $query->count();
        
        // Activities by type
        $activitiesByType = $query->selectRaw('activity_type, COUNT(*) as count')
                                 ->groupBy('activity_type')
                                 ->pluck('count', 'activity_type')
                                 ->toArray();
        
        // Activities by project
        $activitiesByProject = $query->with('project')
                                    ->get()
                                    ->groupBy('project.name')
                                    ->map(function($activities) {
                                        return $activities->count();
                                    })
                                    ->toArray();
        
        // Activities by user - disabled due to no user relation
        $activitiesByUser = [];
        
        // Daily activity trend
        $dailyTrend = $query->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                           ->groupBy('date')
                           ->orderBy('date')
                           ->pluck('count', 'date')
                           ->toArray();
        
        return response()->json([
            'total_activities' => $totalActivities,
            'activities_by_type' => $activitiesByType,
            'activities_by_project' => $activitiesByProject,
            'activities_by_user' => $activitiesByUser,
            'daily_trend' => $dailyTrend
        ]);
    }
}
