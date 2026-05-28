<?php
/**
 * Professor Jag Integration Guide
 * Shows how to use the recommendations API with JotForm AI
 */

echo "=== PROFESSOR JAG INTEGRATION GUIDE ===\n\n";

// 1. API ENDPOINT (what you give to Professor Jag)
$api_endpoint = "https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/recommendations.php";
$api_key = "1b2423e7b7cba8c0d2105b08a7d57a49";

echo "1. GIVE THIS TO PROFESSOR JAG:\n";
echo "   API Endpoint: {$api_endpoint}\n";
echo "   API Key: {$api_key}\n";
echo "   Full URL: {$api_endpoint}?api_key={$api_key}&teacher_id=4&format=ai\n\n";

// 2. SAMPLE RESPONSE (what Professor Jag will receive)
$sample_response = [
    "summary" => "Teacher Dr. Pedro Reyes has 5 evaluations with average rating 4.2/5",
    "key_metrics" => [
        "total_evaluations" => 5,
        "average_rating" => 4.2,
        "student_evaluations" => 3,
        "faculty_evaluations" => 2
    ],
    "recommendations" => [
        "Maintain current teaching standards",
        "Consider adjusting teaching pace", 
        "Add more real-world examples"
    ]
];

echo "2. SAMPLE RESPONSE PROFESSOR JAG GETS:\n";
echo json_encode($sample_response, JSON_PRETTY_PRINT) . "\n\n";

// 3. HOW PROFESSOR JAG SHOULD USE THIS DATA
echo "3. HOW PROFESSOR JAG SHOULD USE THIS:\n";
echo "   a) Parse the JSON response\n";
echo "   b) Use 'summary' for quick overview\n";
echo "   c) Use 'key_metrics' for detailed stats\n";
echo "   d) Use 'recommendations' for actionable advice\n\n";

// 4. EXAMPLE PROFESSOR JAG PROMPT
echo "4. EXAMPLE PROMPT FOR PROFESSOR JAG:\n";
echo "   \"Based on this teacher evaluation data, provide personalized recommendations:\"\n";
echo "   [INSERT JSON DATA HERE]\n\n";

// 5. DYNAMIC TEACHER SELECTION
echo "5. DYNAMIC TEACHER SELECTION:\n";
echo "   For different teachers, just change teacher_id parameter:\n";
echo "   - Teacher 2 (Juan): &teacher_id=2\n";
echo "   - Teacher 3 (Maria): &teacher_id=3\n";
echo "   - Teacher 4 (Pedro): &teacher_id=4\n\n";

echo "=== INTEGRATION COMPLETE ===\n";
echo "Professor Jag can now get real-time teacher recommendations!\n";
?>