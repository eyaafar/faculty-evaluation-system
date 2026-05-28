<?php
// Test to see exactly what's happening with the unauthorized access
echo "<h1>Debugging Unauthorized Access Test</h1>";

// Test the API directly
$test_url = "http://localhost/FEFS/fe-system/teacher/api/professor-jag-access.php?format=ai";

echo "<h2>Test 1: Direct API Call (No Session)</h2>";
echo "<p>URL: $test_url</p>";

// Make sure we have no session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Use cURL to get full response including headers
$ch = curl_init($test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_COOKIESESSION, true); // Start new cookie session

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

$headers = substr($response, 0, $header_size);
$body = substr($response, $header_size);

echo "<h3>HTTP Code: $http_code</h3>";
echo "<h3>Headers:</h3>";
echo "<pre>" . htmlspecialchars($headers) . "</pre>";
echo "<h3>Body:</h3>";
echo "<pre>" . htmlspecialchars($body) . "</pre>";

// Check if it's JSON
$json = json_decode($body, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "<h3>Parsed JSON:</h3>";
    echo "<pre>" . htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT)) . "</pre>";
    
    if (isset($json['access_level'])) {
        echo "<p style='color: red;'>❌ ERROR: Access level is set to: " . $json['access_level'] . "</p>";
        echo "<p>This means the API is incorrectly allowing access when it should reject it.</p>";
    } else {
        echo "<p style='color: green;'>✅ Good: No access level provided</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Invalid JSON: " . json_last_error_msg() . "</p>";
}

echo "<hr>";

// Test 2: Check what's in the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>Test 2: Session Check</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . session_status() . " (0=disabled, 1=none, 2=active)</p>";
echo "<p>Session Data:</p>";
echo "<pre>" . htmlspecialchars(json_encode($_SESSION)) . "</pre>";

// Test 3: Check if there's a cookie
echo "<h2>Test 3: Cookie Check</h2>";
echo "<p>Cookies:</p>";
echo "<pre>" . htmlspecialchars(json_encode($_COOKIE)) . "</pre>";

// Test 4: Check the actual API file
$api_file = "C:\\xampp\\htdocs\\FEFS\\fe-system\\teacher\\api\\professor-jag-access.php";
echo "<h2>Test 4: API File Check</h2>";
echo "<p>File exists: " . (file_exists($api_file) ? 'YES' : 'NO') . "</p>";
echo "<p>File size: " . filesize($api_file) . " bytes</p>";

// Check the first few lines of the API file
echo "<h3>First 30 lines of API file:</h3>";
$lines = file($api_file);
for ($i = 0; $i < min(30, count($lines)); $i++) {
    echo htmlspecialchars($lines[$i]) . "<br>";
}
?>