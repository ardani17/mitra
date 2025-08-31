<?php

namespace App\Http\Controllers;

use App\Services\SystemStatisticsService;
use App\Services\SystemStatisticsAlternativeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SystemStatisticsController extends Controller
{
    protected $statisticsService;

    public function __construct()
    {
        // Check if we have open_basedir restriction
        if (\ini_get('open_basedir')) {
            // Use alternative service for restricted environments
            $this->statisticsService = new SystemStatisticsAlternativeService();
        } else {
            // Use normal service
            $this->statisticsService = new SystemStatisticsService();
        }
    }

    /**
     * Display the system statistics dashboard
     */
    public function index()
    {
        // Check if user has direktur role
        if (!Auth::user() || !Auth::user()->hasRole('direktur')) {
            abort(403, 'Hanya direktur yang dapat mengakses statistik sistem.');
        }

        // Get initial metrics for server-side rendering
        $metrics = $this->statisticsService->getAllMetrics();

        return view('system-statistics.index', compact('metrics'));
    }

    /**
     * Get system metrics as JSON for real-time updates
     */
    public function metrics(Request $request)
    {
        // Check if user has direktur role
        if (!Auth::user() || !Auth::user()->hasRole('direktur')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $metrics = $this->statisticsService->getAllMetrics();
            
            // Add status indicators
            $metrics = $this->addStatusIndicators($metrics);
            
            return response()->json([
                'success' => true,
                'data' => $metrics,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching system metrics: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch system metrics',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Add status indicators to metrics (good, warning, critical)
     */
    private function addStatusIndicators($metrics)
    {
        // CPU status
        if (isset($metrics['cpu']['usage'])) {
            $cpuUsage = $metrics['cpu']['usage'];
            $metrics['cpu']['status'] = $this->getStatus($cpuUsage, 70, 90);
            $metrics['cpu']['status_color'] = $this->getStatusColor($metrics['cpu']['status']);
        }

        // Memory status
        if (isset($metrics['memory']['percentage'])) {
            $memUsage = $metrics['memory']['percentage'];
            $metrics['memory']['status'] = $this->getStatus($memUsage, 75, 90);
            $metrics['memory']['status_color'] = $this->getStatusColor($metrics['memory']['status']);
        }

        // Disk status for each drive
        if (isset($metrics['disk']) && is_array($metrics['disk'])) {
            foreach ($metrics['disk'] as &$disk) {
                if (isset($disk['percentage'])) {
                    $disk['status'] = $this->getStatus($disk['percentage'], 80, 95);
                    $disk['status_color'] = $this->getStatusColor($disk['status']);
                }
            }
        }

        // PHP Memory status
        if (isset($metrics['php_memory']['percentage'])) {
            $phpMemUsage = $metrics['php_memory']['percentage'];
            $metrics['php_memory']['status'] = $this->getStatus($phpMemUsage, 70, 85);
            $metrics['php_memory']['status_color'] = $this->getStatusColor($metrics['php_memory']['status']);
        }

        // Database connection status
        if (isset($metrics['database']['connection_percentage'])) {
            $dbConnUsage = $metrics['database']['connection_percentage'];
            $metrics['database']['status'] = $this->getStatus($dbConnUsage, 70, 90);
            $metrics['database']['status_color'] = $this->getStatusColor($metrics['database']['status']);
        }

        return $metrics;
    }

    /**
     * Determine status based on thresholds
     */
    private function getStatus($value, $warningThreshold, $criticalThreshold)
    {
        if ($value >= $criticalThreshold) {
            return 'critical';
        } elseif ($value >= $warningThreshold) {
            return 'warning';
        }
        return 'good';
    }

    /**
     * Get color class based on status
     */
    private function getStatusColor($status)
    {
        switch ($status) {
            case 'critical':
                return 'red';
            case 'warning':
                return 'yellow';
            case 'good':
            default:
                return 'green';
        }
    }

    /**
     * Export system statistics as CSV
     */
    public function export(Request $request)
    {
        // Check if user has direktur role
        if (!Auth::user() || !Auth::user()->hasRole('direktur')) {
            abort(403, 'Hanya direktur yang dapat mengakses statistik sistem.');
        }

        $metrics = $this->statisticsService->getAllMetrics();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "system_statistics_{$timestamp}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($metrics) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['Metric', 'Value', 'Status', 'Timestamp']);
            
            // CPU
            fputcsv($file, [
                'CPU Usage',
                $metrics['cpu']['usage'] . '%',
                $metrics['cpu']['status'] ?? 'N/A',
                now()->format('Y-m-d H:i:s')
            ]);
            
            fputcsv($file, [
                'CPU Cores',
                $metrics['cpu']['cores'],
                'info',
                now()->format('Y-m-d H:i:s')
            ]);
            
            // Memory
            fputcsv($file, [
                'Memory Usage',
                $metrics['memory']['used_formatted'] . ' / ' . $metrics['memory']['total_formatted'],
                $metrics['memory']['status'] ?? 'N/A',
                now()->format('Y-m-d H:i:s')
            ]);
            
            // Disk
            foreach ($metrics['disk'] as $disk) {
                fputcsv($file, [
                    'Disk ' . $disk['mount'],
                    $disk['used_formatted'] . ' / ' . $disk['total_formatted'],
                    $disk['status'] ?? 'N/A',
                    now()->format('Y-m-d H:i:s')
                ]);
            }
            
            // PHP Memory
            fputcsv($file, [
                'PHP Memory',
                $metrics['php_memory']['current_formatted'] . ' / ' . $metrics['php_memory']['limit_formatted'],
                $metrics['php_memory']['status'] ?? 'N/A',
                now()->format('Y-m-d H:i:s')
            ]);
            
            // Database
            fputcsv($file, [
                'Database Size',
                $metrics['database']['size_formatted'],
                'info',
                now()->format('Y-m-d H:i:s')
            ]);
            
            fputcsv($file, [
                'Database Connections',
                $metrics['database']['active_connections'] . ' / ' . $metrics['database']['max_connections'],
                $metrics['database']['status'] ?? 'N/A',
                now()->format('Y-m-d H:i:s')
            ]);
            
            // System Info
            fputcsv($file, [
                'PHP Version',
                $metrics['system_info']['php_version'],
                'info',
                now()->format('Y-m-d H:i:s')
            ]);
            
            fputcsv($file, [
                'Laravel Version',
                $metrics['system_info']['laravel_version'],
                'info',
                now()->format('Y-m-d H:i:s')
            ]);
            
            fputcsv($file, [
                'Operating System',
                $metrics['system_info']['os'],
                'info',
                now()->format('Y-m-d H:i:s')
            ]);
            
            fputcsv($file, [
                'System Uptime',
                $metrics['uptime']['formatted'] ?? 'N/A',
                'info',
                now()->format('Y-m-d H:i:s')
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clear system metrics cache
     */
    public function clearCache(Request $request)
    {
        // Check if user has direktur role
        if (!Auth::user() || !Auth::user()->hasRole('direktur')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            \Cache::forget('system_metrics');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error clearing cache: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear cache',
            ], 500);
        }
    }
}