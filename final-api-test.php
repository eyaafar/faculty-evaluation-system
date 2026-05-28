<?php
// Test the actual API endpoint via HTTP request
echo "<h1>HTTP API Test</h1>";

// Test URL
$test_url = "http://localhost/FEFS/fe-system/teacher/api/professor-jag-access.php?api_key=jag-2024-enhanced-access-key-9f8e7d6c5b4a&format=ai";

echo "<p>Testing: <code>$test_url</code></p>";

// Use file_get_contents for simple test
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 30,
        'ignore_errors' => true
    ]
]);

$response = @file_get_contents($test_url, false, $context);

if ($response === false) {
    $error = error_get_last();
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>❌ Failed to fetch API response</strong><br>";
    echo "Error: " . htmlspecialchars($error['message'] ?? 'Unknown error');
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
    echo "<strong>✅ API Response received</strong><br>";
    echo "Response length: " . strlen($response) . " characters";
    echo "</div>";
    
    echo "<h3>Raw Response:</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; overflow: auto; max-height: 300px; font-size: 12px;'>";
    echo htmlspecialchars($response);
    echo "</pre>";
    
    // Check if it's valid JSON
    $json_data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
        echo "<strong>✅ Valid JSON Response</strong><br>";
        echo "Success: " . ($json_data['success'] ? 'true' : 'false') . "<br>";
        echo "Access Level: " . ($json_data['access_level'] ?? 'unknown') . "<br>";
        if (isset($json_data['message'])) {
            echo "Message: " . htmlspecialchars($json_data['message']) . "<br>";
        }
        if (isset($json_data['summary'])) {
            echo "Summary: " . htmlspecialchars(json_encode($json_data['summary'])) . "<br>";
        }
        if (isset($json_data['error'])) {
            echo "Error: " . htmlspecialchars($json_data['error']) . "<br>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
        echo "<strong>❌ Invalid JSON:</strong> " . json_last_error_msg() . "<br>";
        echo "</div>";
    }
}

// Also test with cURL for more details
echo "<hr><h2>cURL Test</h2>";

$ch = curl_init($test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$curl_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $http_code</p>";
if ($error) {
    echo "<p><strong>cURL Error:</strong> " . htmlspecialchars($error) . "</p>";
}

if ($curl_response !== false) {
    $headers = substr($curl_response, 0, $header_size);
    $body = substr($curl_response, $header_size);
    
    echo "<p><strong>Headers:</strong></p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px;'>";
    echo htmlspecialchars($headers);
    echo "</pre>";
    
    echo "<p><strong>Body (first 1000 chars):</strong></p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px;'>";
    echo htmlspecialchars(substr($body, 0, 1000));
    echo "</pre>";
}
?>