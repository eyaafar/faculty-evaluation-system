<?php
/**
 * Professor Jag Response Template - Forces Real API Data Usage
 * This template ensures Professor Jag uses your actual database data
 */

// Your API endpoint
$api_url = "https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/recommendations.php";
$api_key = "1b2423e7b7cba8c0d2105b08a7d57a49";

// Function to get real teacher data
function getTeacherData($teacher_id) {
    global $api_url, $api_key;
    
    $url = $api_url . "?api_key=" . $api_key . "&teacher_id=" . $teacher_id . "&format=ai";
    
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// Template for JotForm Knowledge Base
$template = '
WHEN user asks about Dr. Pedro Reyes:
1. ALWAYS call API: https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/recommendations.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id=4&format=ai
2. Use ONLY the data returned from this API call
3. NEVER make up numbers or ratings
4. Response must start with: "Based on your evaluation data..."

CORRECT RESPONSE FORMAT:
"Based on your evaluation data, Dr. Pedro Reyes has [API_DATA.total_evaluations] evaluations with an average rating of [API_DATA.average_rating]/5. [Use API_DATA.recommendations array]"

FORBIDDEN: Making up evaluation counts, ratings, or feedback that doesn\'t exist in the API response.
';

echo "<pre>" . htmlspecialchars($template) . "</pre>";

// Test the API to confirm real data
echo "\n\n--- TESTING REAL API DATA ---\n";
$real_data = getTeacherData(4);
echo "Real API Response:\n";
echo json_encode($real_data, JSON_PRETTY_PRINT);

echo "\n\n--- PROFESSOR JAG MUST USE THIS DATA ---\n";
echo "Total Evaluations: " . $real_data['key_metrics']['total_evaluations'] . "\n";
echo "Average Rating: " . $real_data['key_metrics']['average_rating'] . "\n";
echo "Recommendations: " . implode(", ", $real_data['recommendations']);
?>