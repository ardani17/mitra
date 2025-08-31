<?php
// Test script to check shell_exec availability
// IMPORTANT: Delete this file after testing for security reasons!

echo "PHP Shell Exec Test\n";
echo "==================\n\n";

// Check if function exists
echo "1. function_exists('shell_exec'): ";
echo function_exists('shell_exec') ? "YES" : "NO";
echo "\n\n";

// Check disabled functions
echo "2. Disabled functions: ";
$disabled = ini_get('disable_functions');
echo $disabled ?: "None";
echo "\n\n";

// Check if shell_exec is in disabled list
echo "3. Is shell_exec disabled: ";
$disabled_array = explode(',', $disabled);
$disabled_array = array_map('trim', $disabled_array);
echo in_array('shell_exec', $disabled_array) ? "YES" : "NO";
echo "\n\n";

// Try to use shell_exec
echo "4. Testing shell_exec:\n";
if (function_exists('shell_exec') && !in_array('shell_exec', $disabled_array)) {
    try {
        $result = @shell_exec('echo "Hello from shell"');
        echo "   Result: " . ($result ?: "No output");
    } catch (Exception $e) {
        echo "   Error: " . $e->getMessage();
    }
} else {
    echo "   Cannot test - function not available";
}
echo "\n\n";

// Check other exec functions
echo "5. Other exec functions:\n";
$functions = ['exec', 'system', 'passthru', 'proc_open', 'popen'];
foreach ($functions as $func) {
    echo "   - $func: ";
    echo function_exists($func) ? "Available" : "Not available";
    if (in_array($func, $disabled_array)) {
        echo " (disabled)";
    }
    echo "\n";
}
echo "\n";

// Check if we can read /proc files
echo "6. Can read /proc files:\n";
$proc_files = ['/proc/cpuinfo', '/proc/meminfo', '/proc/loadavg', '/proc/stat'];
foreach ($proc_files as $file) {
    echo "   - $file: ";
    echo is_readable($file) ? "YES" : "NO";
    echo "\n";
}
echo "\n";

// PHP info
echo "7. PHP Info:\n";
echo "   - PHP Version: " . PHP_VERSION . "\n";
echo "   - PHP SAPI: " . PHP_SAPI . "\n";
echo "   - OS: " . PHP_OS . "\n";
echo "   - User: " . get_current_user() . "\n";

echo "\n==================\n";
echo "IMPORTANT: Delete this file after testing!\n";