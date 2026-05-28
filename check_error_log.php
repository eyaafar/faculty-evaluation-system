<?php
// Script to check PHP error log for session debugging

echo "<h1>PHP Error Log Check</h1>";

// Common locations for PHP error log on Windows/XAMPP
$possible_logs = [
    'C:\xampp\php\logs\php_error_log',
    'C:\xampp\apache\logs\error.log',
    'C:\Windows\Temp\php_errors.log',
    ini_get('error_log') // Get from PHP configuration
];

$found_log = false;
foreach ($possible_logs as $log_file) {
    if ($log_file && file_exists($log_file)) {
        echo "<h2>Found error log: " . htmlspecialchars($log_file) . "</h2>";
        
        // Get the last 50 lines of the log
        $lines = file($log_file);
        $last_lines = array_slice($lines, -50);
        
        echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;'>">";
        
        // Filter for our debug messages
        foreach ($last_lines as $line) {
            if (strpos($line, 'DEBUG:') !== false) {
                echo htmlspecialchars($line);
            }
        }
        
        echo "</pre>";
        $found_log = true;
        break;
    }
}

if (!$found_log) {
    echo "<p>Could not find PHP error log. Possible locations:</p>";
    echo "<ul>";
    foreach ($possible_logs as $log_file) {
        echo "<li>" . htmlspecialchars($log_file) . "</li>";
    }
    echo "</ul>";
    
    echo "<p>Current PHP error_log setting: " . htmlspecialchars(ini_get('error_log')) . "</p>";
}

echo "<hr>";
echo "<h2>Test Instructions:</h2>";
echo "<ol>";
echo "<li>Clear this page</li>";
echo "<li>Log in as Dr. Pedro Reyes</li>";
echo "<li>Have a conversation with Professor Jag</li>";
echo "<li>Log out</li>";
echo "<li>Log in as Mr. Juan Dela Cruz</li>";
echo "<li>Check this page again to see the debug logs</li>";
echo "</ol>";

echo "<p><a href='check_session.php'>Go to Session Checker</a></p>";
?>