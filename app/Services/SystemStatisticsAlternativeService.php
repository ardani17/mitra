<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Alternative System Statistics Service for restricted environments
 * Works with open_basedir restrictions
 */
class SystemStatisticsAlternativeService
{
    /**
     * Get all metrics using only PHP built-in functions
     */
    public function getAllMetrics(): array
    {
        return Cache::remember('system_metrics_alt', 5, function () {
            return [
                'cpu' => $this->getCpuUsage(),
                'memory' => $this->getMemoryUsage(),
                'disk' => $this->getDiskUsage(),
                'php_memory' => $this->getPhpMemoryUsage(),
                'database' => $this->getDatabaseStats(),
                'cache' => $this->getCacheStats(),
                'uptime' => $this->getSystemUptime(),
                'system_info' => $this->getSystemInfo(),
            ];
        });
    }

    /**
     * Get CPU usage - simplified for restricted environments
     */
    public function getCpuUsage(): array
    {
        // Use sys_getloadavg if available
        $load = [0, 0, 0];
        if (\function_exists('sys_getloadavg')) {
            $load = \sys_getloadavg();
        }
        
        // Estimate CPU cores (fallback)
        $cores = 1;
        if (isset($_SERVER['NUMBER_OF_PROCESSORS'])) {
            $cores = (int)$_SERVER['NUMBER_OF_PROCESSORS'];
        } elseif (\function_exists('shell_exec') && !\ini_get('open_basedir')) {
            // Only try if no open_basedir restriction
            $cores_check = @\shell_exec('nproc 2>/dev/null');
            if ($cores_check) {
                $cores = (int)$cores_check ?: 1;
            }
        }
        
        // Calculate usage from load average
        $usage = $cores > 0 ? \min(100, \round(($load[0] / $cores) * 100, 2)) : 0;
        
        return [
            'usage' => $usage,
            'cores' => $cores,
            'threads' => $cores, // Assume threads = cores
            'load_average' => $load,
        ];
    }

    /**
     * Get memory usage - using PHP's memory functions
     */
    public function getMemoryUsage(): array
    {
        // Get PHP memory info as a proxy
        $phpLimit = $this->convertToBytes(\ini_get('memory_limit'));
        $phpUsage = \memory_get_usage(true);
        $phpReal = \memory_get_usage(false);
        
        // Try to get system memory from PHP info
        $total = $phpLimit * 10; // Estimate system has 10x PHP limit
        $used = $phpUsage * 10; // Rough estimate
        
        // If we can get from environment
        if (isset($_SERVER['MEMORY_LIMIT'])) {
            $total = $this->convertToBytes($_SERVER['MEMORY_LIMIT']);
        }
        
        // Calculate percentage
        $percentage = $total > 0 ? \round(($used / $total) * 100, 2) : 0;
        
        return [
            'total' => $total,
            'total_formatted' => $this->formatBytes($total),
            'used' => $used,
            'used_formatted' => $this->formatBytes($used),
            'free' => $total - $used,
            'free_formatted' => $this->formatBytes($total - $used),
            'percentage' => $percentage,
            'note' => 'Estimated values due to system restrictions',
        ];
    }

    /**
     * Get disk usage - only for allowed directories
     */
    public function getDiskUsage(): array
    {
        $disks = [];
        
        // Get disk usage for current directory (Laravel root)
        $path = base_path();
        $free = @\disk_free_space($path);
        $total = @\disk_total_space($path);
        
        if ($free !== false && $total !== false) {
            $used = $total - $free;
            $percentage = $total > 0 ? \round(($used / $total) * 100, 2) : 0;
            
            $disks[] = [
                'mount' => 'Application Directory',
                'path' => $path,
                'total' => $total,
                'total_formatted' => $this->formatBytes($total),
                'used' => $used,
                'used_formatted' => $this->formatBytes($used),
                'free' => $free,
                'free_formatted' => $this->formatBytes($free),
                'percentage' => $percentage,
            ];
        }
        
        // Try to get info for storage directory
        $storagePath = storage_path();
        if ($storagePath !== $path) {
            $storageFree = @\disk_free_space($storagePath);
            $storageTotal = @\disk_total_space($storagePath);
            
            if ($storageFree !== false && $storageTotal !== false) {
                // Only add if it's a different disk
                if (\abs($storageTotal - $total) > 1024) {
                    $storageUsed = $storageTotal - $storageFree;
                    $storagePercentage = $storageTotal > 0 ? \round(($storageUsed / $storageTotal) * 100, 2) : 0;
                    
                    $disks[] = [
                        'mount' => 'Storage Directory',
                        'path' => $storagePath,
                        'total' => $storageTotal,
                        'total_formatted' => $this->formatBytes($storageTotal),
                        'used' => $storageUsed,
                        'used_formatted' => $this->formatBytes($storageUsed),
                        'free' => $storageFree,
                        'free_formatted' => $this->formatBytes($storageFree),
                        'percentage' => $storagePercentage,
                    ];
                }
            }
        }
        
        return $disks;
    }

    /**
     * Get PHP memory usage
     */
    public function getPhpMemoryUsage(): array
    {
        $memoryLimit = $this->convertToBytes(\ini_get('memory_limit'));
        $memoryUsage = \memory_get_usage(true);
        $memoryPeak = \memory_get_peak_usage(true);
        $percentage = $memoryLimit > 0 ? \round(($memoryUsage / $memoryLimit) * 100, 2) : 0;
        
        return [
            'limit' => $memoryLimit,
            'limit_formatted' => $this->formatBytes($memoryLimit),
            'current' => $memoryUsage,
            'current_formatted' => $this->formatBytes($memoryUsage),
            'peak' => $memoryPeak,
            'peak_formatted' => $this->formatBytes($memoryPeak),
            'percentage' => $percentage,
        ];
    }

    /**
     * Get database statistics
     */
    public function getDatabaseStats(): array
    {
        try {
            // Try PostgreSQL first
            $dbName = config('database.connections.pgsql.database');
            $sizeQuery = DB::select("SELECT pg_database_size(?) as size", [$dbName]);
            $dbSize = $sizeQuery[0]->size ?? 0;
            
            $tableCount = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'public'");
            $tables = $tableCount[0]->count ?? 0;
            
            $connectionQuery = DB::select("SELECT count(*) as active FROM pg_stat_activity");
            $activeConnections = $connectionQuery[0]->active ?? 0;
            
            return [
                'size' => $dbSize,
                'size_formatted' => $this->formatBytes($dbSize),
                'tables' => $tables,
                'active_connections' => $activeConnections,
                'max_connections' => 100,
                'connection_percentage' => \round(($activeConnections / 100) * 100, 2),
                'type' => 'PostgreSQL',
            ];
        } catch (\Exception $e) {
            // Try MySQL
            try {
                $dbName = config('database.connections.mysql.database');
                $sizeQuery = DB::select("SELECT SUM(data_length + index_length) as size FROM information_schema.TABLES WHERE table_schema = ?", [$dbName]);
                $dbSize = $sizeQuery[0]->size ?? 0;
                
                $tableCount = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?", [$dbName]);
                $tables = $tableCount[0]->count ?? 0;
                
                return [
                    'size' => $dbSize,
                    'size_formatted' => $this->formatBytes($dbSize),
                    'tables' => $tables,
                    'active_connections' => 0,
                    'max_connections' => 100,
                    'connection_percentage' => 0,
                    'type' => 'MySQL',
                ];
            } catch (\Exception $e2) {
                return [
                    'size' => 0,
                    'size_formatted' => '0 B',
                    'tables' => 0,
                    'active_connections' => 0,
                    'max_connections' => 0,
                    'connection_percentage' => 0,
                    'type' => 'Unknown',
                ];
            }
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        $driver = config('cache.default');
        $stats = [
            'driver' => $driver,
            'status' => 'Active',
        ];
        
        if ($driver === 'file') {
            $cachePath = storage_path('framework/cache/data');
            if (\is_dir($cachePath)) {
                $size = 0;
                $files = 0;
                
                try {
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($cachePath)
                    );
                    
                    foreach ($iterator as $file) {
                        if ($file->isFile()) {
                            $size += $file->getSize();
                            $files++;
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore permission errors
                }
                
                $stats['size'] = $size;
                $stats['size_formatted'] = $this->formatBytes($size);
                $stats['files'] = $files;
            }
        }
        
        return $stats;
    }

    /**
     * Get system uptime - simplified version
     */
    public function getSystemUptime(): array
    {
        // Get Laravel application uptime
        $laravelBootTime = null;
        if (\file_exists(base_path('bootstrap/cache/config.php'))) {
            $laravelBootTime = \filemtime(base_path('bootstrap/cache/config.php'));
        } elseif (\file_exists(base_path('vendor/autoload.php'))) {
            $laravelBootTime = \filemtime(base_path('vendor/autoload.php'));
        }
        
        $appUptimeSeconds = $laravelBootTime ? \time() - $laravelBootTime : 0;
        $appUptime = $this->formatUptime($appUptimeSeconds);
        
        return [
            'days' => $appUptime['days'],
            'hours' => $appUptime['hours'],
            'minutes' => $appUptime['minutes'],
            'formatted' => $appUptime['formatted'],
            'boot_time' => 'Not available (restricted environment)',
            'app_uptime' => $appUptime['formatted'],
        ];
    }

    /**
     * Get system information
     */
    public function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'os' => PHP_OS_FAMILY,
            'hostname' => \gethostname() ?: 'Unknown',
            'timezone' => \date_default_timezone_get(),
            'current_time' => now()->format('Y-m-d H:i:s'),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug') ? 'Enabled' : 'Disabled',
            'restrictions' => 'open_basedir active',
        ];
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = \max($bytes, 0);
        $pow = \floor(($bytes ? \log($bytes) : 0) / \log(1024));
        $pow = \min($pow, \count($units) - 1);
        
        $bytes /= \pow(1024, $pow);
        
        return \round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Convert PHP memory string to bytes
     */
    private function convertToBytes($value): int
    {
        $value = \trim($value);
        if (empty($value)) {
            return 0;
        }
        
        $last = \strtolower($value[\strlen($value) - 1]);
        $value = (int)$value;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }

    /**
     * Format uptime
     */
    private function formatUptime($seconds): array
    {
        $days = \floor($seconds / 86400);
        $hours = \floor(($seconds % 86400) / 3600);
        $minutes = \floor(($seconds % 3600) / 60);
        
        $formatted = '';
        if ($days > 0) {
            $formatted .= $days . ' ' . ($days == 1 ? 'day' : 'days');
        }
        if ($hours > 0) {
            $formatted .= ($formatted ? ', ' : '') . $hours . ' ' . ($hours == 1 ? 'hour' : 'hours');
        }
        if ($days == 0 && $minutes > 0) {
            $formatted .= ($formatted ? ', ' : '') . $minutes . ' ' . ($minutes == 1 ? 'minute' : 'minutes');
        }
        
        if (empty($formatted)) {
            $formatted = 'Just started';
        }
        
        return [
            'days' => $days,
            'hours' => $hours,
            'minutes' => $minutes,
            'formatted' => $formatted,
        ];
    }
}