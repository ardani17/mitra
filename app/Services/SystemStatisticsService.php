<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SystemStatisticsService
{
    /**
     * Check if shell_exec is available
     */
    private function isShellExecAvailable(): bool
    {
        if (!function_exists('shell_exec')) {
            return false;
        }
        
        $disabled = explode(',', ini_get('disable_functions'));
        return !in_array('shell_exec', array_map('trim', $disabled));
    }

    /**
     * Safe shell exec with fallback
     */
    private function safeShellExec($command)
    {
        if ($this->isShellExecAvailable()) {
            return shell_exec($command);
        }
        return null;
    }

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
     * Get CPU usage information - Enhanced for Linux
     */
    public function getCpuUsage(): array
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows-specific CPU usage using wmic
                if ($this->isShellExecAvailable()) {
                    $cpuLoad = $this->safeShellExec('wmic cpu get loadpercentage /value');
                    preg_match('/LoadPercentage=(\d+)/', $cpuLoad ?? '', $matches);
                    $cpuUsage = isset($matches[1]) ? (int)$matches[1] : 0;
                    
                    // Get number of cores
                    $coreCount = $this->safeShellExec('wmic cpu get NumberOfCores /value');
                    preg_match('/NumberOfCores=(\d+)/', $coreCount ?? '', $coreMatches);
                    $cores = isset($coreMatches[1]) ? (int)$coreMatches[1] : 1;
                    
                    // Get logical processors
                    $logicalProcessors = $this->safeShellExec('wmic cpu get NumberOfLogicalProcessors /value');
                    preg_match('/NumberOfLogicalProcessors=(\d+)/', $logicalProcessors ?? '', $logicalMatches);
                    $threads = isset($logicalMatches[1]) ? (int)$logicalMatches[1] : $cores;
                } else {
                    // Fallback values for Windows
                    $cpuUsage = 0;
                    $cores = 1;
                    $threads = 1;
                }
                
                $cpuInfo = [
                    'usage' => $cpuUsage,
                    'cores' => $cores,
                    'threads' => $threads,
                    'load_average' => [$cpuUsage, $cpuUsage, $cpuUsage],
                ];
            } else {
                // Enhanced Linux/Unix CPU usage
                $cores = 1;
                $threads = 1;
                $cpuUsage = 0;
                $load = [0, 0, 0];
                
                // Method 1: Try to read from /proc/cpuinfo
                if (is_readable('/proc/cpuinfo')) {
                    $cpuinfo = file_get_contents('/proc/cpuinfo');
                    
                    // Count physical cores
                    preg_match_all('/^processor\s*:\s*(\d+)/m', $cpuinfo, $processorMatches);
                    $threads = count($processorMatches[0]) ?: 1;
                    
                    // Try to get physical cores
                    preg_match_all('/^cpu cores\s*:\s*(\d+)/m', $cpuinfo, $coreMatches);
                    if (!empty($coreMatches[1])) {
                        $cores = (int)$coreMatches[1][0];
                    } else {
                        $cores = $threads; // Fallback to thread count
                    }
                }
                
                // Method 2: Try nproc command if available
                if ($this->isShellExecAvailable()) {
                    $nprocResult = $this->safeShellExec('nproc 2>/dev/null');
                    if ($nprocResult) {
                        $threads = (int)trim($nprocResult) ?: $threads;
                    }
                }
                
                // Get load average
                if (function_exists('sys_getloadavg')) {
                    $load = sys_getloadavg();
                } elseif (is_readable('/proc/loadavg')) {
                    $loadavg = file_get_contents('/proc/loadavg');
                    $loadParts = explode(' ', $loadavg);
                    $load = [
                        (float)($loadParts[0] ?? 0),
                        (float)($loadParts[1] ?? 0),
                        (float)($loadParts[2] ?? 0)
                    ];
                }
                
                // Calculate CPU usage from load average
                // Load average of 1.0 on a single-core system means 100% usage
                $cpuUsage = $cores > 0 ? min(100, round(($load[0] / $cores) * 100, 2)) : 0;
                
                // Alternative: Try to get CPU usage from /proc/stat
                if ($cpuUsage == 0 && is_readable('/proc/stat')) {
                    $stat1 = file_get_contents('/proc/stat');
                    usleep(100000); // Wait 100ms
                    $stat2 = file_get_contents('/proc/stat');
                    
                    preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/m', $stat1, $cpu1);
                    preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/m', $stat2, $cpu2);
                    
                    if (count($cpu1) > 4 && count($cpu2) > 4) {
                        $idle1 = $cpu1[4] + ($cpu1[5] ?? 0);
                        $idle2 = $cpu2[4] + ($cpu2[5] ?? 0);
                        
                        $total1 = array_sum(array_slice($cpu1, 1));
                        $total2 = array_sum(array_slice($cpu2, 1));
                        
                        $totalDiff = $total2 - $total1;
                        $idleDiff = $idle2 - $idle1;
                        
                        if ($totalDiff > 0) {
                            $cpuUsage = round(100 * (1 - $idleDiff / $totalDiff), 2);
                        }
                    }
                }
                
                $cpuInfo = [
                    'usage' => max(0, min(100, $cpuUsage)),
                    'cores' => $cores,
                    'threads' => $threads,
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
     * Get memory usage information - Enhanced for Linux
     */
    public function getMemoryUsage(): array
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows-specific memory usage
                if ($this->isShellExecAvailable()) {
                    $totalMemory = $this->safeShellExec('wmic OS get TotalVisibleMemorySize /value');
                    preg_match('/TotalVisibleMemorySize=(\d+)/', $totalMemory ?? '', $totalMatches);
                    $total = isset($totalMatches[1]) ? (int)$totalMatches[1] * 1024 : 0;
                    
                    $freeMemory = $this->safeShellExec('wmic OS get FreePhysicalMemory /value');
                    preg_match('/FreePhysicalMemory=(\d+)/', $freeMemory ?? '', $freeMatches);
                    $free = isset($freeMatches[1]) ? (int)$freeMatches[1] * 1024 : 0;
                } else {
                    $total = 0;
                    $free = 0;
                }
                
                $used = $total - $free;
                $percentage = $total > 0 ? round(($used / $total) * 100, 2) : 0;
            } else {
                // Enhanced Linux/Unix memory usage
                $total = 0;
                $free = 0;
                $available = 0;
                $buffers = 0;
                $cached = 0;
                
                // Primary method: Read from /proc/meminfo
                if (is_readable('/proc/meminfo')) {
                    $memInfo = file_get_contents('/proc/meminfo');
                    
                    // Parse memory values
                    preg_match('/^MemTotal:\s+(\d+)\s*kB/m', $memInfo, $totalMatch);
                    preg_match('/^MemFree:\s+(\d+)\s*kB/m', $memInfo, $freeMatch);
                    preg_match('/^MemAvailable:\s+(\d+)\s*kB/m', $memInfo, $availMatch);
                    preg_match('/^Buffers:\s+(\d+)\s*kB/m', $memInfo, $buffersMatch);
                    preg_match('/^Cached:\s+(\d+)\s*kB/m', $memInfo, $cachedMatch);
                    preg_match('/^SReclaimable:\s+(\d+)\s*kB/m', $memInfo, $sreclaimMatch);
                    
                    $total = isset($totalMatch[1]) ? (int)$totalMatch[1] * 1024 : 0;
                    $free = isset($freeMatch[1]) ? (int)$freeMatch[1] * 1024 : 0;
                    $buffers = isset($buffersMatch[1]) ? (int)$buffersMatch[1] * 1024 : 0;
                    $cached = isset($cachedMatch[1]) ? (int)$cachedMatch[1] * 1024 : 0;
                    $sreclaimable = isset($sreclaimMatch[1]) ? (int)$sreclaimMatch[1] * 1024 : 0;
                    
                    // MemAvailable is more accurate if present (kernel 3.14+)
                    if (isset($availMatch[1])) {
                        $available = (int)$availMatch[1] * 1024;
                    } else {
                        // Calculate available memory for older kernels
                        // Available = Free + Buffers + Cached + SReclaimable
                        $available = $free + $buffers + $cached + $sreclaimable;
                    }
                }
                
                // Fallback method: Try free command if shell_exec is available
                if ($total == 0 && $this->isShellExecAvailable()) {
                    $freeOutput = $this->safeShellExec('free -b 2>/dev/null');
                    if ($freeOutput) {
                        $lines = explode("\n", $freeOutput);
                        foreach ($lines as $line) {
                            if (strpos($line, 'Mem:') === 0) {
                                $parts = preg_split('/\s+/', trim($line));
                                if (count($parts) >= 3) {
                                    $total = (int)($parts[1] ?? 0);
                                    $used = (int)($parts[2] ?? 0);
                                    $free = (int)($parts[3] ?? 0);
                                    $available = $free + (int)($parts[5] ?? 0) + (int)($parts[6] ?? 0); // free + buffers + cache
                                }
                                break;
                            }
                        }
                    }
                }
                
                // Calculate used memory
                $used = $total - $available;
                $percentage = $total > 0 ? round(($used / $total) * 100, 2) : 0;
            }
            
            return [
                'total' => $total,
                'total_formatted' => $this->formatBytes($total),
                'used' => max(0, $used),
                'used_formatted' => $this->formatBytes(max(0, $used)),
                'free' => max(0, $available),
                'free_formatted' => $this->formatBytes(max(0, $available)),
                'percentage' => max(0, min(100, $percentage)),
            ];
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
     * Get disk usage information - Enhanced for Linux
     */
    public function getDiskUsage(): array
    {
        try {
            $disks = [];
            
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows disk usage
                if ($this->isShellExecAvailable()) {
                    $drives = $this->safeShellExec('wmic logicaldisk get size,freespace,caption /value');
                    if ($drives) {
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
                    }
                } else {
                    // Fallback for Windows
                    $mount = 'C:';
                    if (is_dir($mount)) {
                        $free = disk_free_space($mount);
                        $total = disk_total_space($mount);
                        if ($free !== false && $total !== false) {
                            $used = $total - $free;
                            $percentage = $total > 0 ? round(($used / $total) * 100, 2) : 0;
                            
                            $disks[] = [
                                'mount' => $mount,
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
            } else {
                // Enhanced Linux disk usage
                
                // Method 1: Try df command if shell_exec is available
                if ($this->isShellExecAvailable()) {
                    $df = $this->safeShellExec('df -B1 -T 2>/dev/null | grep -E "^/dev/"');
                    if ($df) {
                        $lines = explode("\n", $df);
                        foreach ($lines as $line) {
                            if (empty(trim($line))) continue;
                            
                            // Parse df output: Filesystem Type Size Used Avail Use% Mounted
                            $parts = preg_split('/\s+/', trim($line));
                            if (count($parts) >= 7) {
                                $filesystem = $parts[0];
                                $type = $parts[1];
                                $total = (int)$parts[2];
                                $used = (int)$parts[3];
                                $free = (int)$parts[4];
                                $mount = $parts[6];
                                
                                // Skip special filesystems
                                if (in_array($type, ['devtmpfs', 'tmpfs', 'squashfs', 'overlay'])) {
                                    continue;
                                }
                                
                                $percentage = $total > 0 ? round(($used / $total) * 100, 2) : 0;
                                
                                $disks[] = [
                                    'mount' => $mount,
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
                }
                
                // Method 2: Read from /proc/mounts and use PHP functions
                if (empty($disks)) {
                    $mountPoints = [];
                    
                    // Get mount points from /proc/mounts
                    if (is_readable('/proc/mounts')) {
                        $mounts = file_get_contents('/proc/mounts');
                        $lines = explode("\n", $mounts);
                        
                        foreach ($lines as $line) {
                            if (empty(trim($line))) continue;
                            
                            $parts = explode(' ', $line);
                            if (count($parts) >= 3) {
                                $device = $parts[0];
                                $mount = $parts[1];
                                $type = $parts[2];
                                
                                // Only include real filesystems
                                if (strpos($device, '/dev/') === 0 && 
                                    !in_array($type, ['devtmpfs', 'tmpfs', 'squashfs', 'overlay', 'proc', 'sysfs', 'devpts'])) {
                                    $mountPoints[$mount] = $device;
                                }
                            }
                        }
                    }
                    
                    // If no mounts found, at least check root
                    if (empty($mountPoints)) {
                        $mountPoints['/'] = '/dev/root';
                    }
                    
                    // Get disk usage for each mount point
                    foreach ($mountPoints as $mount => $device) {
                        if (is_dir($mount) && is_readable($mount)) {
                            $free = @disk_free_space($mount);
                            $total = @disk_total_space($mount);
                            
                            if ($free !== false && $total !== false && $total > 0) {
                                $used = $total - $free;
                                $percentage = round(($used / $total) * 100, 2);
                                
                                // Skip if this is a duplicate mount point (same total size)
                                $duplicate = false;
                                foreach ($disks as $existingDisk) {
                                    if (abs($existingDisk['total'] - $total) < 1024) { // Within 1KB
                                        $duplicate = true;
                                        break;
                                    }
                                }
                                
                                if (!$duplicate) {
                                    $disks[] = [
                                        'mount' => $mount,
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
                    }
                }
                
                // Sort disks by mount point
                usort($disks, function($a, $b) {
                    return strcmp($a['mount'], $b['mount']);
                });
            }
            
            // Filter out system/virtual drives
            $disks = array_filter($disks, function($disk) {
                if (PHP_OS_FAMILY === 'Windows') {
                    return preg_match('/^[A-Z]:/', $disk['mount']) && $disk['total'] > 0;
                }
                // Keep only relevant Linux mounts
                return !in_array($disk['mount'], ['/dev', '/sys', '/proc', '/run', '/dev/shm']) && 
                       $disk['total'] > 1024 * 1024; // At least 1MB
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
            $activeConnections = isset($connectionQuery[0]) ? $connectionQuery[0]->active : 0;
            $maxConnections = isset($connectionQuery[0]) ? $connectionQuery[0]->max : 100;
            
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
                $activeConnections = isset($connectionQuery[0]) ? $connectionQuery[0]->Value : 0;
                
                $maxQuery = DB::select("SHOW VARIABLES WHERE Variable_name = 'max_connections'");
                $maxConnections = isset($maxQuery[0]) ? $maxQuery[0]->Value : 100;
                
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
            } elseif ($driver === 'database') {
                $stats['driver'] = 'database';
                $stats['status'] = 'Active';
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
     * Get system uptime information - Enhanced for Linux
     */
    public function getSystemUptime(): array
    {
        try {
            $uptime = [];
            
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows uptime
                if ($this->isShellExecAvailable()) {
                    $bootTime = $this->safeShellExec('wmic os get lastbootuptime /value');
                    preg_match('/LastBootUpTime=(\d{14})/', $bootTime ?? '', $matches);
                    
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
                    } else {
                        $uptime = $this->formatUptime(0);
                        $uptime['boot_time'] = 'Unknown';
                    }
                } else {
                    $uptime = $this->formatUptime(0);
                    $uptime['boot_time'] = 'Unknown';
                }
            } else {
                // Enhanced Linux uptime
                $uptimeSeconds = 0;
                
                // Method 1: Read from /proc/uptime
                if (is_readable('/proc/uptime')) {
                    $uptimeData = file_get_contents('/proc/uptime');
                    $parts = explode(' ', $uptimeData);
                    $uptimeSeconds = (int)$parts[0];
                }
                
                // Method 2: Try uptime command if available
                if ($uptimeSeconds == 0 && $this->isShellExecAvailable()) {
                    $uptimeCmd = $this->safeShellExec('cat /proc/uptime 2>/dev/null | cut -d" " -f1');
                    if ($uptimeCmd) {
                        $uptimeSeconds = (int)trim($uptimeCmd);
                    }
                }
                
                $uptime = $this->formatUptime($uptimeSeconds);
                $uptime['boot_time'] = $uptimeSeconds > 0
                    ? date('Y-m-d H:i:s', time() - $uptimeSeconds)
                    : 'Unknown';
            }
            
            // Laravel application uptime (approximate)
            $laravelBootTime = null;
            if (file_exists(base_path('bootstrap/cache/config.php'))) {
                $laravelBootTime = filemtime(base_path('bootstrap/cache/config.php'));
            } elseif (file_exists(base_path('vendor/autoload.php'))) {
                $laravelBootTime = filemtime(base_path('vendor/autoload.php'));
            }
            
            $appUptimeSeconds = $laravelBootTime ? time() - $laravelBootTime : 0;
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
            $info = [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'os' => PHP_OS_FAMILY,
                'hostname' => gethostname() ?: 'Unknown',
                'timezone' => date_default_timezone_get(),
                'current_time' => now()->format('Y-m-d H:i:s'),
            ];
            
            // Get more detailed OS information for Linux
            if (PHP_OS_FAMILY === 'Linux') {
                // Try to get distribution info
                if (is_readable('/etc/os-release')) {
                    $osRelease = parse_ini_file('/etc/os-release');
                    if (isset($osRelease['PRETTY_NAME'])) {
                        $info['os'] = $osRelease['PRETTY_NAME'];
                    } elseif (isset($osRelease['NAME']) && isset($osRelease['VERSION'])) {
                        $info['os'] = $osRelease['NAME'] . ' ' . $osRelease['VERSION'];
                    }
                } elseif (is_readable('/etc/redhat-release')) {
                    $info['os'] = trim(file_get_contents('/etc/redhat-release'));
                } elseif (is_readable('/etc/debian_version')) {
                    $info['os'] = 'Debian ' . trim(file_get_contents('/etc/debian_version'));
                }
                
                // Add kernel version
                if (is_readable('/proc/version')) {
                    $version = file_get_contents('/proc/version');
                    if (preg_match('/Linux version ([^\s]+)/', $version, $matches)) {
                        $info['os'] .= ' (Kernel ' . $matches[1] . ')';
                    }
                } elseif ($this->isShellExecAvailable()) {
                    $kernel = $this->safeShellExec('uname -r 2>/dev/null');
                    if ($kernel) {
                        $info['os'] .= ' (Kernel ' . trim($kernel) . ')';
                    }
                }
            } elseif (PHP_OS_FAMILY === 'Windows') {
                $info['os'] = 'Windows ' . php_uname('r');
            }
            
            return $info;
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
        if (empty($value)) {
            return 0;
        }
        
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