<?php

// Set content type to HTML
header('Content-Type: text/html');

// Start HTML output
echo "<!DOCTYPE html>
<html>
<head>
    <title>PHP Info</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin-bottom: 30px; }
        h2 { color: #333; }
    </style>
</head>
<body>
    <h1>PHP Configuration Info</h1>";

// Section 1: Basic Info
echo "<div class='section'>
    <h2>Basic Information</h2>
    <table>
        <tr><th>Property</th><th>Value</th></tr>
        <tr><td>PHP Version</td><td>" . PHP_VERSION . "</td></tr>
        <tr><td>SAPI</td><td>" . PHP_SAPI . "</td></tr>
        <tr><td>OS</td><td>" . PHP_OS . "</td></tr>
        <tr><td>Server Software</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</td></tr>
    </table>
</div>";

// Section 2: Extensions
echo "<div class='section'>
    <h2>Extensions</h2>
    <table>
        <tr><th>Extension</th><th>Status</th></tr>";

$extensions = ['mbstring', 'json', 'curl', 'openssl', 'pdo', 'pdo_pgsql', 'gd', 'zip'];
foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? 'Loaded' : 'Not Loaded';
    $statusClass = extension_loaded($ext) ? 'style="color: green;"' : 'style="color: red;"';
    echo "<tr><td>$ext</td><td $statusClass>$status</td></tr>";
}

echo "</table>
</div>";

// Section 3: mbstring Functions
echo "<div class='section'>
    <h2>mbstring Functions</h2>
    <table>
        <tr><th>Function</th><th>Status</th></tr>";

$mbFunctions = ['mb_split', 'mb_strlen', 'mb_strpos', 'mb_substr', 'mb_strtolower', 'mb_strtoupper'];
foreach ($mbFunctions as $func) {
    $status = function_exists($func) ? 'Exists' : 'Not Exists';
    $statusClass = function_exists($func) ? 'style="color: green;"' : 'style="color: red;"';
    echo "<tr><td>$func</td><td $statusClass>$status</td></tr>";
}

echo "</table>
</div>";

// Section 4: Configuration
echo "<div class='section'>
    <h2>Key Configuration</h2>
    <table>
        <tr><th>Setting</th><th>Value</th></tr>";

$configs = ['memory_limit', 'max_execution_time', 'upload_max_filesize', 'post_max_size'];
foreach ($configs as $config) {
    $value = ini_get($config);
    echo "<tr><td>$config</td><td>$value</td></tr>";
}

echo "</table>
</div>";

// End HTML output
echo "</body>
</html>";

?>