<?php
// Test to check if the API is working and what errors might be occurring

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Professor Jag API Debug Test</h1>";
echo "<p>Testing at: " . date('Y-m-d H:i:s') . "</p>";

// Test 1: Check if the file exists and is readable
$api_file = "C:\\xampp\\htdocs\\FEFS\\fe-system\\teacher\\api\\professor-jag-access.php";
echo "<h2>File Check</h2>";
echo "<p>File exists: " . (file_exists($api_file) ? 'YES' : 'NO') . "</p>";
echo "<p>File is readable: " . (is_readable($api_file) ? 'YES' : 'NO') . "</p>";

// Test 2: Check PHP syntax
echo "<h2>PHP Syntax Check</h2>";
$output = shell_exec('php -l "' . $api_file . '" 2>&1');
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Test 3: Try to include the file and capture any errors
echo "<h2>Include Test</h2>";
$old_error_level = error_reporting(E_ALL);
$errors = [];

// Custom error handler to capture errors
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$errors) {
    $errors[] = "Error $errno: $errstr in $errfile on line $errline";
    return true;
});

ob_start();
try {
    // Set up the environment like the API expects
    $_GET['api_key'] = 'jag-2024-enhanced-access-key-9f8e7d6c5b4a';
    $_GET['format'] = 'ai';
    
    include($api_file);
    $output = ob_get_clean();
    
    echo "<p style='color: green;'>✅ Include successful</p>";
    echo "<p>Output length: " . strlen($output) . " characters</p>";
    
    if (!empty($output)) {
        echo "<h3>Output:</h3>";
        echo "<pre style='background: #f0f0f0; padding: 10px; overflow: auto; max-height: 200px;'>";
        echo htmlspecialchars($output);
        echo "</pre>";
        
        // Check if it's JSON
        $json = json_decode($output, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p style='color: green;'>✅ Valid JSON response</p>";
        } else {
            echo "<p style='color: red;'>❌ Invalid JSON: " . json_last_error_msg() . "</p>";
        }
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
} catch (ParseError $e) {
    ob_end_clean();
    echo "<p style='color: red;'>❌ Parse Error: " . $e->getMessage() . "</p>";
}

// Restore error handler
restore_error_handler();
error_reporting($old_error_level);

if (!empty($errors)) {
    echo "<h3>Captured Errors:</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li style='color: red;'>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
}

// Test 4: Check if the API key constant is defined
echo "<h2>API Key Check</h2>";
echo "<p>PROFESSOR_JAG_API_KEY defined: " . (defined('PROFESSOR_JAG_API_KEY') ? 'YES' : 'NO') . "</p>";
if (defined('PROFESSOR_JAG_API_KEY')) {
    echo "<p>API Key value: " . PROFESSOR_JAG_API_KEY . "</p>";
}

// Test 5: Check session status
echo "<h2>Session Status</h2>";
echo "<p>Session status: " . session_status() . " (0=disabled, 1=none, 2=active)</p>";
if (session_status() === PHP_SESSION_NONE) {
    echo "<p>Session started: " . (session_start() ? 'YES' : 'NO') . "</p>";
}
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<p>Session data: " . htmlspecialchars(json_encode($_SESSION)) . "</p>";
}
?>