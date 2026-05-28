<?php
/**
 * FORCE PROFESSOR JAG TO USE REAL API DATA
 * This script demonstrates the exact API call Professor Jag MUST make
 */

// Your real API data (test this yourself)
$api_url = "https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/recommendations.php";
$api_key = "1b2423e7b7cba8c0d2105b08a7d57a49";

// Function to get REAL teacher data
function getRealTeacherData($teacher_id) {
    global $api_url, $api_key;
    $url = $api_url . "?api_key=" . $api_key . "&teacher_id=" . $teacher_id . "&format=ai";
    
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// Get the real data
echo "=== REAL API DATA FOR DR. PEDRO REYES ===\n";
$real_data = getRealTeacherData(4);
echo json_encode($real_data, JSON_PRETTY_PRINT);

// Create the EXACT response Professor Jag should give
echo "\n\n=== PROFESSOR JAG MUST RESPOND WITH THIS ===\n";
$correct_response = "Based on your evaluation data, Dr. Pedro Reyes has " . 
                   $real_data['key_metrics']['total_evaluations'] . 
                   " evaluation with an average rating of " . 
                   $real_data['key_metrics']['average_rating'] . 
                   "/5. Here are my recommendations: " . 
                   implode(", ", $real_data['recommendations']);

echo $correct_response;

// Template for JotForm Knowledge Base
echo "\n\n=== COPY THIS TO JOTFORM KNOWLEDGE BASE ===\n";
$knowledge_base = '
CRITICAL INSTRUCTIONS FOR PROFESSOR JAG:

When user asks "how many evaluations" or "tell me about Dr. Pedro Reyes":

1. IMMEDIATELY call: https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/recommendations.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id=4&format=ai

2. PARSE the JSON response and extract:
   - key_metrics.total_evaluations
   - key_metrics.average_rating  
   - recommendations array

3. RESPOND with this EXACT format:
   "Based on your evaluation data, Dr. Pedro Reyes has [TOTAL] evaluation(s) with an average rating of [RATING]/5. Here are my recommendations: [RECOMMENDATIONS]"

4. NEVER use numbers like "25" - only use API data

REAL DATA FROM API:
- Total Evaluations: 1
- Average Rating: 3.0
- Recommendations: Focus on improving teaching methodology and student engagement, Encourage more students to provide feedback for better insights

MANDATORY: Professor Jag MUST make the API call before responding!
';

echo $knowledge_base;
?>