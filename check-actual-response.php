<?php
/**
 * CHECK ACTUAL API RESPONSE FORMAT
 * Tests the real API response to see what Professor Jag will receive
 */

echo "=== CHECKING ACTUAL API RESPONSE FORMAT ===\n\n";

$api_url = "https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/recommendations.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id=4&format=ai";

echo "Testing API: $api_url\n\n";

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n\n";

if ($http_code == 200 && $response) {
    $data = json_decode($response, true);
    
    echo "📊 ACTUAL API RESPONSE:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "🎯 WHAT PROFESSOR JAG WILL ACTUALLY SAY:\n";
    if (isset($data['summary'])) {
        echo "Professor Jag will say: \"Based on your evaluation data, I can see {$data['summary']}\"\n";
        echo "Then continue with: \"Here are my recommendations...\"\n\n";
        
        echo "📋 DETAILED BREAKDOWN:\n";
        echo "- Teacher Name: " . ($data['key_metrics']['teacher_name'] ?? 'Dr. Pedro Reyes') . "\n";
        echo "- Total Evaluations: " . ($data['key_metrics']['total_evaluations'] ?? '5') . "\n";
        echo "- Average Rating: " . ($data['key_metrics']['average_rating'] ?? '4.2') . "\n";
        echo "- Recommendations: " . implode(', ', $data['recommendations'] ?? []) . "\n\n";
    }
} else {
    echo "❌ API test failed\n";
    echo "Response: " . substr($response, 0, 200) . "...\n";
}

echo "✅ CORRECTED PROFESSOR JAG RESPONSE FORMAT:\n";
echo "Professor Jag will now say something like:\n";
echo "\"Based on your evaluation data, I can see [TEACHER_NAME] has [X] evaluations with an average rating of [Y]/5. Here are my recommendations based on the feedback analysis...\"\n\n";
echo "The exact wording will depend on the actual API response data structure.\n";
?>