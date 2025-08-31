<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SystemStatisticsService
{
    /**
     * Get all system metrics with caching
     */
    public function getAllMetrics(): array
    {
        return Cache::remember('system_metrics', 5, function () {
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
     * Get CPU usage information
     */
    public function getCpuUsage(): array
    {
        try {
            $cpuInfo = [];
            
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows-specific CPU usage using wmic
                $cpuLoad = shell_exec('wmic cpu get loadpercentage /value');
                preg_match('/LoadPercentage=(\d+)/', $cpuLoad, $matches);
                $cpuUsage = isset($matches[1]) ? (int)$matches[1] : 0;
                
                // Get number of cores
                $coreCount = shell_exec('wmic cpu get NumberOfCores /value');
                preg_match('/NumberOfCores=(\d+)/', $coreCount, $coreMatches);
                $cores = isset($coreMatches[1]) ? (int)$coreMatches[1] : 1;
                
                // Get logical processors
                $logicalProcessors = shell_exec('wmic cpu get NumberOfLogicalProcessors /value');
                preg_match('/NumberOfLogicalProcessors=(\d+)/', $logicalProcessors, $logicalMatches);
                $threads = isset($logicalMatches[1]) ? (int)$logicalMatches[1] : $cores;
                
                $cpuInfo = [
                    'usage' => $cpuUsage,
                    'cores' => $cores,
                    'threads' => $threads,
                    'load_average' => [$cpuUsage, $cpuUsage, $cpuUsage], // Simulated for Windows
                ];
            } else {
                // Linux/Unix CPU usage
                $load = sys_getloadavg();
                $cpuInfo = [
                    'usage' => round($load[0] * 100 / shell_exec('nproc'), 2),
                    'cores' => (int)shell_exec('nproc'),
                    'threads' => (int)shell_exec('nproc'),
                    'load_average' => $load,
                ];
            }
            
            return $cpuInfo;
        } catch (\Exception $e) {
            Log::error('Error getting CPU usage: ' . $e->getMessage());
            return [
                'usage' => 0,
                'cores' => 1,
                'threads' => 1,
                'load_average' => [0, 0, 0],
            ];
        }
    }

    /**
     * Get memory usage information
     */
    public function getMemoryUsage(): array
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows-specific memory usage using wmic
                $totalMemory = shell_exec('wmic OS get TotalVisibleMemorySize /value');
                preg_match('/TotalVisibleMemorySize=(\d+)/', $totalMemory, $totalMatches);
                $total = isset($totalMatches[1]) ? (int)$totalMatches[1] * 1024 : 0; // Convert KB to bytes
                
                $freeMemory = shell_exec('wmic OS get FreePhysicalMemory /value');
                preg_match('/FreePhysicalMemory=(\d+)/', $freeMemory, $freeMatches);
                $free = isset($freeMatches[1]) ? (int)$freeMatches[1] * 1024 : 0; // Convert KB to bytes
                
                $used = $total - $free;
                $percentage = $total > 0 ? round(($used / $total) * 100, 2) : 0;
                
                return [
                    'total' => $total,
                    'total_formatted' => $this->formatBytes($total),
                    'used' => $used,
                    'used_formatted' => $this->formatBytes($used),
                    'free' => $free,
                    'free_formatted' => $this->formatBytes($free),
                    'percentage' => $percentage,
                ];
            } else {
                // Linux/Unix memory usage
                $memInfo = file_get_contents('/proc/meminfo');
                preg_match('/MemTotal:\s+(\d+)/', $memInfo, $totalMatches);
                preg_match('/MemAvailable:\s+(\d+)/', $memInfo, $availableMatches);
                
                $total = isset($totalMatches[1]) ? (int)$totalMatches[1] * 1024 : 0;
                $available = isset($availableMatches[1]) ? (int)$availableMatches[1] * 1024 : 0;
                $used = $total - $available;
                $percentage = $total > 0 ? round(($used / $total) * 100, 2) : 0;
                
                return [
                    'total' => $total,
                    'total_formatted' => $this->formatBytes($total),
                    'used' => $used,
                    'used_formatted' => $this->formatBytes($used),
                    'free' => $available,
                    'free_formatted' => $this->formatBytes($available),
                    'percentage' => $percentage,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error getting memory usage: ' . $e->getMessage());
            return [
                'total' => 0,
                'total_formatted' => '0 B',
                'used' => 0,
                'used_formatted' => '0 B',
                'free' => 0,
                'free_formatted' => '0 B',
                'percentage' => 0,
            ];
        }
    }

    /**
     * Get disk usage information for all drives
     */
    public function getDiskUsage(): array
    {
        try {
            $disks = [];
            
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows-specific disk usage
                $drives = shell_exec('wmic logicaldisk get size,freespace,caption /value');
                $lines = explode("\n", $drives);
                $currentDisk = [];
                
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) {
                        if (!empty($currentDisk) && isset($currentDisk['Caption'])) {
                            $total = isset($currentDisk['Size']) ? (int)$currentDisk['Size'] : 0;
                            $free = isset($currentDisk['FreeSpace']) ? (int)$currentDisk['FreeSpace'] : 0;
                            $used = $total - $free;
                            $percentage = $total > 0 ? round(($used / $total) * 100, 2) : 0;
                            
                            $disks[] = [
                                'mount' => $currentDisk['Caption'],
                                'total' => $total,
                                'total_formatted' => $this->formatBytes($total),
                                'used' => $used,
                                'used_formatted' => $this->formatBytes($used),
                                'free' => $free,
                                'free_formatted' => $this->formatBytes($free),
                                'percentage' => $percentage,
                            ];
                        }
                        $currentDisk = [];
                        continue;
                    }
                    
                    if (strpos($line, '=') !== false) {
                        list($key, $value) = explode('=', $line, 2);
                        $currentDisk[$key] = $value;
                    }
                }
            } else {
                // Linux/Unix disk usage
                $df = shell_exec('df -B1');
                $lines = explode("\n", $df);
                array_shift($lines); // Remove header
                
                foreach ($lines as $line) {
                    if (empty($line)) continue;
                    
                    $parts = preg_split('/\s+/', $line);
                    if (count($parts) >= 6) {
                        $total = (int)$parts[1];
                        $used = (int)$parts[2];
                        $free = (int)$parts[3];
                        $percentage = $total > 0 ? round(($used / $total) * 100, 2) : 0;
                        
                        $disks[] = [
                            'mount' => $parts[5],
                            'total' => $total,
                            'total_formatted' => $this->formatBytes($total),
                            'used' => $used,
                            'used_formatted' => $this->formatBytes($used),
                            'free' => $free,
                            'free_formatted' => $this->formatBytes($free),
                            'percentage' => $percentage,
                        ];
                    }
                }
            }
            
            // Filter out system/virtual drives if needed
            $disks = array_filter($disks, function($disk) {
                // Keep only physical drives on Windows (C:, D:, etc.)
                if (PHP_OS_FAMILY === 'Windows') {
                    return preg_match('/^[A-Z]:/', $disk['mount']) && $disk['total'] > 0;
                }
                // Keep only relevant mounts on Linux
                return !in_array($disk['mount'], ['/dev', '/sys', '/proc', '/run']) && $disk['total'] > 0;
            });
            
            return array_values($disks);
        } catch (\Exception $e) {
            Log::error('Error getting disk usage: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get PHP memory usage information
     */
    public function getPhpMemoryUsage(): array
    {
        try {
            $memoryLimit = $this->convertToBytes(ini_get('memory_limit'));
            $memoryUsage = memory_get_usage(true);
            $memoryPeak = memory_get_peak_usage(true);
            $percentage = $memoryLimit > 0 ? round(($memoryUsage / $memoryLimit) * 100, 2) : 0;
            
            return [
                'limit' => $memoryLimit,
                'limit_formatted' => $this->formatBytes($memoryLimit),
                'current' => $memoryUsage,
                'current_formatted' => $this->formatBytes($memoryUsage),
                'peak' => $memoryPeak,
                'peak_formatted' => $this->formatBytes($memoryPeak),
                'percentage' => $percentage,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting PHP memory usage: ' . $e->getMessage());
            return [
                'limit' => 0,
                'limit_formatted' => '0 B',
                'current' => 0,
                'current_formatted' => '0 B',
                'peak' => 0,
                'peak_formatted' => '0 B',
                'percentage' => 0,
            ];
        }
    }

    /**
     * Get database statistics
     */
    public function getDatabaseStats(): array
    {
        try {
            $stats = [];
            
            // Get database size
            $dbName = config('database.connections.pgsql.database');
            $sizeQuery = DB::select("SELECT pg_database_size(?) as size", [$dbName]);
            $dbSize = $sizeQuery[0]->size ?? 0;
            
            // Get table count
            $tableCount = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'public'");
            $tables = $tableCount[0]->count ?? 0;
            
            // Get connection info
            $connectionQuery = DB::select("SELECT count(*) as active, max_conn.setting as max FROM pg_stat_activity, (SELECT setting FROM pg_settings WHERE name = 'max_connections') max_conn GROUP BY max_conn.setting");
            $activeConnections = $connectionQuery[0]->active ?? 0;
            $maxConnections = $connectionQuery[0]->max ?? 100;
            
            return [
                'size' => $dbSize,
                'size_formatted' => $this->formatBytes($dbSize),
                'tables' => $tables,
                'active_connections' => $activeConnections,
                'max_connections' => $maxConnections,
                'connection_percentage' => round(($activeConnections / $maxConnections) * 100, 2),
                'type' => 'PostgreSQL',
            ];
        } catch (\Exception $e) {
            Log::error('Error getting database stats: ' . $e->getMessage());
            
            // Try MySQL if PostgreSQL fails
            try {
                $dbName = config('database.connections.mysql.database');
                $sizeQuery = DB::select("SELECT SUM(data_length + index_length) as size FROM information_schema.TABLES WHERE table_schema = ?", [$dbName]);
                $dbSize = $sizeQuery[0]->size ?? 0;
                
                $tableCount = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?", [$dbName]);
                $tables = $tableCount[0]->count ?? 0;
                
                $connectionQuery = DB::select("SHOW STATUS WHERE Variable_name = 'Threads_connected'");
                $activeConnections = $connectionQuery[0]->Value ?? 0;
                
                $maxQuery = DB::select("SHOW VARIABLES WHERE Variable_name = 'max_connections'");
                $maxConnections = $maxQuery[0]->Value ?? 100;
                
                return [
                    'size' => $dbSize,
                    'size_formatted' => $this->formatBytes($dbSize),
                    'tables' => $tables,
                    'active_connections' => $activeConnections,
                    'max_connections' => $maxConnections,
                    'connection_percentage' => round(($activeConnections / $maxConnections) * 100, 2),
                    'type' => 'MySQL',
                ];
            } catch (\Exception $e2) {
                Log::error('Error getting MySQL stats: ' . $e2->getMessage());
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
        try {
            $driver = config('cache.default');
            $stats = [
                'driver' => $driver,
                'status' => 'Active',
            ];
            
            if ($driver === 'redis') {
                try {
                    $redis = Cache::getRedis();
                    $info = $redis->info();
                    
                    $stats['memory_used'] = $info['used_memory'] ?? 0;
                    $stats['memory_used_formatted'] = $this->formatBytes($stats['memory_used']);
                    $stats['hits'] = $info['keyspace_hits'] ?? 0;
                    $stats['misses'] = $info['keyspace_misses'] ?? 0;
                    $stats['hit_rate'] = ($stats['hits'] + $stats['misses']) > 0 
                        ? round(($stats['hits'] / ($stats['hits'] + $stats['misses'])) * 100, 2) 
                        : 0;
                } catch (\Exception $e) {
                    $stats['status'] = 'Error: ' . $e->getMessage();
                }
            } elseif ($driver === 'file') {
                $cachePath = storage_path('framework/cache/data');
                if (is_dir($cachePath)) {
                    $size = 0;
                    $files = 0;
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($cachePath)
                    );
                    
                    foreach ($iterator as $file) {
                        if ($file->isFile()) {
                            $size += $file->getSize();
                            $files++;
                        }
                    }
                    
                    $stats['size'] = $size;
                    $stats['size_formatted'] = $this->formatBytes($size);
                    $stats['files'] = $files;
                }
            }
            
            return $stats;
        } catch (\Exception $e) {
            Log::error('Error getting cache stats: ' . $e->getMessage());
            return [
                'driver' => config('cache.default'),
                'status' => 'Error',
            ];
        }
    }

    /**
     * Get system uptime information
     */
    public function getSystemUptime(): array
    {
        try {
            $uptime = [];
            
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows uptime using wmic
                $bootTime = shell_exec('wmic os get lastbootuptime /value');
                preg_match('/LastBootUpTime=(\d{14})/', $bootTime, $matches);
                
                if (isset($matches[1])) {
                    $boot = $matches[1];
                    $year = substr($boot, 0, 4);
                    $month = substr($boot, 4, 2);
                    $day = substr($boot, 6, 2);
                    $hour = substr($boot, 8, 2);
                    $minute = substr($boot, 10, 2);
                    $second = substr($boot, 12, 2);
                    
                    $bootTimestamp = mktime($hour, $minute, $second, $month, $day, $year);
                    $uptimeSeconds = time() - $bootTimestamp;
                    
                    $uptime = $this->formatUptime($uptimeSeconds);
                    $uptime['boot_time'] = date('Y-m-d H:i:s', $bootTimestamp);
                }
            } else {
                // Linux/Unix uptime
                $uptimeData = file_get_contents('/proc/uptime');
                $uptimeSeconds = (int)explode(' ', $uptimeData)[0];
                
                $uptime = $this->formatUptime($uptimeSeconds);
                $uptime['boot_time'] = date('Y-m-d H:i:s', time() - $uptimeSeconds);
            }
            
            // Laravel application uptime (approximate)
            $laravelBootTime = filemtime(base_path('bootstrap/cache/config.php'));
            if (!$laravelBootTime) {
                $laravelBootTime = filemtime(base_path('vendor/autoload.php'));
            }
            $appUptimeSeconds = time() - $laravelBootTime;
            
            $uptime['app_uptime'] = $this->formatUptime($appUptimeSeconds)['formatted'];
            
            return $uptime;
        } catch (\Exception $e) {
            Log::error('Error getting system uptime: ' . $e->getMessage());
            return [
                'days' => 0,
                'hours' => 0,
                'minutes' => 0,
                'formatted' => '0 days, 0 hours',
                'boot_time' => 'Unknown',
                'app_uptime' => 'Unknown',
            ];
        }
    }

    /**
     * Get system information
     */
    public function getSystemInfo(): array
    {
        try {
            return [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'os' => PHP_OS_FAMILY . ' ' . php_uname('r'),
                'hostname' => gethostname(),
                'timezone' => date_default_timezone_get(),
                'current_time' => now()->format('Y-m-d H:i:s'),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting system info: ' . $e->getMessage());
            return [
                'php_version' => PHP_VERSION,
                'laravel_version' => 'Unknown',
                'server_software' => 'Unknown',
                'os' => PHP_OS_FAMILY,
                'hostname' => 'Unknown',
                'timezone' => date_default_timezone_get(),
                'current_time' => now()->format('Y-m-d H:i:s'),
            ];
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Convert PHP memory string to bytes
     */
    private function convertToBytes($value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
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
     * Format uptime seconds to readable format
     */
    private function formatUptime($seconds): array
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
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