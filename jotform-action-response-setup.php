<?php
/**
 * JOTFORM ACTION RESPONSE SETUP
 * How to configure the action to parse API response correctly
 */

echo "=== JOTFORM ACTION CONFIGURATION ===\n\n";

echo "STEP 1: CORRECT API URL\n";
echo "✅ USE THIS EXACT URL:\n";
echo "https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/all-teachers-simple.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49\n\n";

echo "STEP 2: RESPONSE PARSING IN KNOWLEDGE BASE\n";
echo "Add this to your knowledge base:\n\n";

$parser_code = 'API RESPONSE PARSING INSTRUCTIONS:

When you get the API response, follow this EXACT process:

1. Parse the JSON response
2. Look for teacher in response.all_teachers[teacher_id]
3. Extract these values:
   - total_evaluations = response.all_teachers[teacher_id].key_metrics.total_evaluations
   - average_rating = response.all_teachers[teacher_id].key_metrics.average_rating
   - recommendations = response.all_teachers[teacher_id].recommendations

4. Use this EXACT format:
   "Based on your evaluation data, [teacher_name] has [total_evaluations] evaluation(s) with an average rating of [average_rating]/5. Here are my recommendations: [recommendations]"

EXAMPLE RESPONSES:
- Dr. Pedro Reyes: "Based on your evaluation data, Dr. Pedro Reyes has 1 evaluation(s) with an average rating of 3.0/5. Here are my recommendations: Focus on improving teaching methodology and student engagement. Encourage more students to provide feedback for better insights."

- Juan Dela Cruz: "Based on your evaluation data, Juan Dela Cruz has 0 evaluation(s). No evaluation data available yet. Encourage students to provide feedback and continue building positive relationships with students."

- Maria Santos: "Based on your evaluation data, Maria Santos has 0 evaluation(s). No evaluation data available yet. Encourage students to provide feedback and continue building positive relationships with students."';

echo $parser_code;

echo "\n\nSTEP 3: ACTION TEST\n";
echo "Test the action with: 'Tell me about Juan Dela Cruz'\n";
echo "Expected: Should show 0 evaluations, not 1 evaluation\n";
?>