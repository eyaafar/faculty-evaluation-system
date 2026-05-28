<?php
// Test the Professor Jag API with different scenarios
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Professor Jag API Authentication Tests</h1>";
echo "<p>Testing at: " . date('Y-m-d H:i:s') . "</p>";

// Test 1: No credentials (should fail)
echo "<h2>Test 1: No Credentials (Unauthorized Access)</h2>";
$url1 = "http://localhost/FEFS/fe-system/teacher/api/professor-jag-access.php?format=ai";
echo "<p>URL: <code>$url1</code></p>";

// Clear any existing session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

$context1 = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10
    ]
]);

$response1 = @file_get_contents($url1, false, $context1);

if ($response1 === false) {
    echo "<p style='color: red;'>❌ Failed to connect to API</p>";
} else {
    $json1 = json_decode($response1, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p style='color: green;'>✅ Valid JSON Response</p>";
        echo "<p><strong>Success:</strong> " . ($json1['success'] ? 'true' : 'false') . "</p>";
        echo "<p><strong>Access Level:</strong> " . ($json1['access_level'] ?? 'none') . "</p>";
        echo "<p><strong>Error:</strong> " . ($json1['error'] ?? 'none') . "</p>";
        echo "<p><strong>Details:</strong> " . ($json1['details'] ?? 'none') . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Invalid JSON: " . json_last_error_msg() . "</p>";
        echo "<pre>" . htmlspecialchars($response1) . "</pre>";
    }
}

echo "<hr>";

// Test 2: Invalid API key (should fail)
echo "<h2>Test 2: Invalid API Key</h2>";
$url2 = "http://localhost/FEFS/fe-system/teacher/api/professor-jag-access.php?api_key=invalid-key&format=ai";
echo "<p>URL: <code>$url2</code></p>";

$context2 = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10
    ]
]);

$response2 = @file_get_contents($url2, false, $context2);

if ($response2 === false) {
    echo "<p style='color: red;'>❌ Failed to connect to API</p>";
} else {
    $json2 = json_decode($response2, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p style='color: green;'>✅ Valid JSON Response</p>";
        echo "<p><strong>Success:</strong> " . ($json2['success'] ? 'true' : 'false') . "</p>";
        echo "<p><strong>Access Level:</strong> " . ($json2['access_level'] ?? 'none') . "</p>";
        echo "<p><strong>Error:</strong> " . ($json2['error'] ?? 'none') . "</p>";
        echo "<p><strong>Details:</strong> " . ($json2['details'] ?? 'none') . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Invalid JSON: " . json_last_error_msg() . "</p>";
        echo "<pre>" . htmlspecialchars($response2) . "</pre>";
    }
}

echo "<hr>";

// Test 3: Valid Professor Jag API key (should succeed)
echo "<h2>Test 3: Valid Professor Jag API Key</h2>";
$url3 = "http://localhost/FEFS/fe-system/teacher/api/professor-jag-access.php?api_key=jag-2024-enhanced-access-key-9f8e7d6c5b4a&format=ai";
echo "<p>URL: <code>$url3</code></p>";

$context3 = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10
    ]
]);

$response3 = @file_get_contents($url3, false, $context3);

if ($response3 === false) {
    echo "<p style='color: red;'>❌ Failed to connect to API</p>";
} else {
    $json3 = json_decode($response3, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p style='color: green;'>✅ Valid JSON Response</p>";
        echo "<p><strong>Success:</strong> " . ($json3['success'] ? 'true' : 'false') . "</p>";
        echo "<p><strong>Access Level:</strong> " . ($json3['access_level'] ?? 'none') . "</p>";
        echo "<p><strong>Error:</strong> " . ($json3['error'] ?? 'none') . "</p>";
        if (isset($json3['summary'])) {
            echo "<p><strong>Summary:</strong> " . json_encode($json3['summary']) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Invalid JSON: " . json_last_error_msg() . "</p>";
        echo "<pre>" . htmlspecialchars($response3) . "</pre>";
    }
}

echo "<hr>";

// Test 4: Check what happens with session (should show teacher access if logged in)
echo "<h2>Test 4: Session-based Access</h2>";

// Start a test session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set up a fake teacher session for testing
$_SESSION['role'] = 'teacher';
$_SESSION['user_id'] = 123;
$_SESSION['username'] = 'test_teacher';

$url4 = "http://localhost/FEFS/fe-system/teacher/api/professor-jag-access.php?format=ai";
echo "<p>URL: <code>$url4</code></p>";
echo "<p>Session data: " . htmlspecialchars(json_encode($_SESSION)) . "</p>";

$context4 = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10,
        'header' => 'Cookie: ' . session_name() . '=' . session_id()
    ]
]);

$response4 = @file_get_contents($url4, false, $context4);

if ($response4 === false) {
    echo "<p style='color: red;'>❌ Failed to connect to API</p>";
} else {
    $json4 = json_decode($response4, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p style='color: green;'>✅ Valid JSON Response</p>";
        echo "<p><strong>Success:</strong> " . ($json4['success'] ? 'true' : 'false') . "</p>";
        echo "<p><strong>Access Level:</strong> " . ($json4['access_level'] ?? 'none') . "</p>";
        echo "<p><strong>Error:</strong> " . ($json4['error'] ?? 'none') . "</p>";
        echo "<p><strong>Details:</strong> " . ($json4['details'] ?? 'none') . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Invalid JSON: " . json_last_error_msg() . "</p>";
        echo "<pre>" . htmlspecialchars($response4) . "</pre>";
    }
}

// Clean up
session_destroy();

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>If Test 1 shows 'Access Level: none' and 'Success: false', then the unauthorized access test is working correctly.</p>";
echo "<p>If Test 3 shows 'Access Level: professor_jag' and 'Success: true', then Professor Jag access is working correctly.</p>";
?>