#!/usr/bin/env php
<?php
/**
 * System Metrics Helper Script
 * This script runs with elevated permissions to collect system metrics
 * that require root access on Linux systems.
 * 
 * Usage: sudo php system-metrics-helper.php [command]
 * Commands: cpu, memory, disk, all
 */

// Ensure script is run from command line
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line\n");
}

// Get command from arguments
$command = $argv[1] ?? 'all';

// Security: Validate command
$validCommands = ['cpu', 'memory', 'disk', 'all'];
if (!in_array($command, $validCommands)) {
    die("Invalid command. Valid commands: " . implode(', ', $validCommands) . "\n");
}

/**
 * Get CPU metrics
 */
function getCpuMetrics() {
    $metrics = [];
    
    // Get CPU info from /proc/cpuinfo
    if (file_exists('/proc/cpuinfo')) {
        $cpuinfo = file_get_contents('/proc/cpuinfo');
        preg_match_all('/^processor\s*:\s*(\d+)/m', $cpuinfo, $processorMatches);
        $metrics['threads'] = count($processorMatches[0]);
        
        preg_match_all('/^cpu cores\s*:\s*(\d+)/m', $cpuinfo, $coreMatches);
        if (!empty($coreMatches[1])) {
            $metrics['cores'] = (int)$coreMatches[1][0];
        } else {
            $metrics['cores'] = $metrics['threads'];
        }
    }
    
    // Get load average
    if (file_exists('/proc/loadavg')) {
        $loadavg = file_get_contents('/proc/loadavg');
        $parts = explode(' ', $loadavg);
        $metrics['load_average'] = [
            (float)$parts[0],
            (float)$parts[1],
            (float)$parts[2]
        ];
    }
    
    // Get CPU usage from /proc/stat
    if (file_exists('/proc/stat')) {
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
                $metrics['usage'] = round(100 * (1 - $idleDiff / $totalDiff), 2);
            }
        }
    }
    
    // Fallback: Calculate from load average
    if (!isset($metrics['usage']) && isset($metrics['load_average']) && isset($metrics['cores'])) {
        $metrics['usage'] = min(100, round(($metrics['load_average'][0] / $metrics['cores']) * 100, 2));
    }
    
    return $metrics;
}

/**
 * Get memory metrics
 */
function getMemoryMetrics() {
    $metrics = [];
    
    if (file_exists('/proc/meminfo')) {
        $meminfo = file_get_contents('/proc/meminfo');
        
        // Parse memory values
        preg_match('/^MemTotal:\s+(\d+)\s*kB/m', $meminfo, $totalMatch);
        preg_match('/^MemFree:\s+(\d+)\s*kB/m', $meminfo, $freeMatch);
        preg_match('/^MemAvailable:\s+(\d+)\s*kB/m', $meminfo, $availMatch);
        preg_match('/^Buffers:\s+(\d+)\s*kB/m', $meminfo, $buffersMatch);
        preg_match('/^Cached:\s+(\d+)\s*kB/m', $meminfo, $cachedMatch);
        preg_match('/^SReclaimable:\s+(\d+)\s*kB/m', $meminfo, $sreclaimMatch);
        
        $metrics['total'] = isset($totalMatch[1]) ? (int)$totalMatch[1] * 1024 : 0;
        $metrics['free'] = isset($freeMatch[1]) ? (int)$freeMatch[1] * 1024 : 0;
        $metrics['buffers'] = isset($buffersMatch[1]) ? (int)$buffersMatch[1] * 1024 : 0;
        $metrics['cached'] = isset($cachedMatch[1]) ? (int)$cachedMatch[1] * 1024 : 0;
        $metrics['sreclaimable'] = isset($sreclaimMatch[1]) ? (int)$sreclaimMatch[1] * 1024 : 0;
        
        // MemAvailable is more accurate if present
        if (isset($availMatch[1])) {
            $metrics['available'] = (int)$availMatch[1] * 1024;
        } else {
            // Calculate available memory for older kernels
            $metrics['available'] = $metrics['free'] + $metrics['buffers'] + $metrics['cached'] + $metrics['sreclaimable'];
        }
        
        $metrics['used'] = $metrics['total'] - $metrics['available'];
        $metrics['percentage'] = $metrics['total'] > 0 ? round(($metrics['used'] / $metrics['total']) * 100, 2) : 0;
    }
    
    return $metrics;
}

/**
 * Get disk metrics
 */
function getDiskMetrics() {
    $disks = [];
    
    // Use df command for accurate disk usage
    $output = shell_exec('df -B1 -T 2>/dev/null | grep -E "^/dev/"');
    if ($output) {
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
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
                    'filesystem' => $filesystem,
                    'type' => $type,
                    'total' => $total,
                    'used' => $used,
                    'free' => $free,
                    'percentage' => $percentage,
                ];
            }
        }
    }
    
    // Fallback: Read from /proc/mounts
    if (empty($disks) && file_exists('/proc/mounts')) {
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
                    
                    $free = disk_free_space($mount);
                    $total = disk_total_space($mount);
                    
                    if ($free !== false && $total !== false && $total > 0) {
                        $used = $total - $free;
                        $percentage = round(($used / $total) * 100, 2);
                        
                        $disks[] = [
                            'mount' => $mount,
                            'filesystem' => $device,
                            'type' => $type,
                            'total' => $total,
                            'used' => $used,
                            'free' => $free,
                            'percentage' => $percentage,
                        ];
                    }
                }
            }
        }
    }
    
    return $disks;
}

// Execute requested command
$result = [];

switch ($command) {
    case 'cpu':
        $result = getCpuMetrics();
        break;
    case 'memory':
        $result = getMemoryMetrics();
        break;
    case 'disk':
        $result = getDiskMetrics();
        break;
    case 'all':
        $result = [
            'cpu' => getCpuMetrics(),
            'memory' => getMemoryMetrics(),
            'disk' => getDiskMetrics(),
            'timestamp' => time(),
        ];
        break;
}

// Output as JSON
echo json_encode($result, JSON_PRETTY_PRINT) . "\n";