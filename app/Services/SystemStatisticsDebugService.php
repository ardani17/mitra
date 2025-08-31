<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SystemStatisticsDebugService
{
    /**
     * Debug system permissions and file access
     */
    public function debugSystemAccess(): array
    {
        $debug = [];
        
        // Check current user
        $debug['current_user'] = [
            'uid' => function_exists('posix_getuid') ? posix_getuid() : 'N/A',
            'gid' => function_exists('posix_getgid') ? posix_getgid() : 'N/A',
            'username' => function_exists('posix_getpwuid') ? posix_getpwuid(posix_getuid())['name'] : get_current_user(),
            'php_sapi' => PHP_SAPI,
        ];
        
        // Check /proc files accessibility
        $procFiles = [
            '/proc/cpuinfo',
            '/proc/meminfo',
            '/proc/loadavg',
            '/proc/stat',
            '/proc/uptime',
            '/proc/mounts',
        ];
        
        foreach ($procFiles as $file) {
            $debug['proc_files'][$file] = [
                'exists' => file_exists($file),
                'readable' => is_readable($file),
                'permissions' => file_exists($file) ? substr(sprintf('%o', fileperms($file)), -4) : 'N/A',
                'owner' => file_exists($file) && function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($file))['name'] : 'N/A',
            ];
            
            // Try to read first line
            if (is_readable($file)) {
                $content = @file_get_contents($file);
                $debug['proc_files'][$file]['can_read'] = $content !== false;
                $debug['proc_files'][$file]['first_line'] = $content ? substr(explode("\n", $content)[0], 0, 50) . '...' : 'empty';
            } else {
                $debug['proc_files'][$file]['can_read'] = false;
                $debug['proc_files'][$file]['error'] = error_get_last()['message'] ?? 'Unknown error';
            }
        }
        
        // Check shell_exec availability
        $debug['shell_exec'] = [
            'function_exists' => function_exists('shell_exec'),
            'disabled' => in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions')))),
        ];
        
        // Check exec availability
        $debug['exec'] = [
            'function_exists' => function_exists('exec'),
            'disabled' => in_array('exec', array_map('trim', explode(',', ini_get('disable_functions')))),
        ];
        
        // Test sys_getloadavg
        $debug['sys_getloadavg'] = [
            'available' => function_exists('sys_getloadavg'),
            'result' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
        ];
        
        // Test disk functions
        $debug['disk_functions'] = [
            'disk_free_space' => function_exists('disk_free_space'),
            'disk_total_space' => function_exists('disk_total_space'),
            'test_root' => [
                'free' => @disk_free_space('/'),
                'total' => @disk_total_space('/'),
            ],
        ];
        
        return $debug;
    }
    
    /**
     * Test different methods to get CPU info
     */
    public function testCpuMethods(): array
    {
        $results = [];
        
        // Method 1: /proc/stat
        if (is_readable('/proc/stat')) {
            $stat = file_get_contents('/proc/stat');
            $lines = explode("\n", $stat);
            $cpuLine = $lines[0] ?? '';
            $results['/proc/stat'] = [
                'readable' => true,
                'cpu_line' => substr($cpuLine, 0, 100),
            ];
        } else {
            $results['/proc/stat'] = ['readable' => false];
        }
        
        // Method 2: /proc/loadavg
        if (is_readable('/proc/loadavg')) {
            $loadavg = file_get_contents('/proc/loadavg');
            $results['/proc/loadavg'] = [
                'readable' => true,
                'content' => trim($loadavg),
            ];
        } else {
            $results['/proc/loadavg'] = ['readable' => false];
        }
        
        // Method 3: sys_getloadavg
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $results['sys_getloadavg'] = [
                'available' => true,
                'load' => $load,
            ];
        } else {
            $results['sys_getloadavg'] = ['available' => false];
        }
        
        // Method 4: /proc/cpuinfo
        if (is_readable('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            $results['/proc/cpuinfo'] = [
                'readable' => true,
                'processor_count' => count($matches[0]),
            ];
        } else {
            $results['/proc/cpuinfo'] = ['readable' => false];
        }
        
        return $results;
    }
    
    /**
     * Test different methods to get memory info
     */
    public function testMemoryMethods(): array
    {
        $results = [];
        
        // Method 1: /proc/meminfo
        if (is_readable('/proc/meminfo')) {
            $meminfo = file_get_contents('/proc/meminfo');
            $lines = explode("\n", $meminfo);
            $memData = [];
            foreach ($lines as $line) {
                if (preg_match('/^(MemTotal|MemFree|MemAvailable|Buffers|Cached):\s+(\d+)/', $line, $matches)) {
                    $memData[$matches[1]] = $matches[2];
                }
            }
            $results['/proc/meminfo'] = [
                'readable' => true,
                'data' => $memData,
            ];
        } else {
            $results['/proc/meminfo'] = ['readable' => false];
        }
        
        // Method 2: PHP memory functions
        $results['php_memory'] = [
            'memory_get_usage' => memory_get_usage(true),
            'memory_get_peak_usage' => memory_get_peak_usage(true),
            'memory_limit' => ini_get('memory_limit'),
        ];
        
        return $results;
    }
}