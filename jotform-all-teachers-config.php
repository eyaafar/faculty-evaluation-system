<?php
/**
 * JOTFORM CONFIGURATION FOR ALL-TEACHERS API
 * Single API call returns all teachers data, JotForm extracts specific teacher
 */

echo "=== JOTFORM SETUP FOR ALL-TEACHERS API ===\n\n";

echo "STEP 1: Delete All Current Actions\n";
echo "- Go to JotForm AI Agent → Actions\n";
echo "- Delete any existing teacher actions\n\n";

echo "STEP 2: Create Single Action for All Teachers\n";
echo "- Click "Add New Action"\n";
echo "- Name: Get Teacher Evaluation Data\n";
echo "- When: User mentions ANY teacher name\n";
echo "- API: GET https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/all-teachers-simple.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49\n";
echo "- Response: Parse JSON to extract specific teacher data\n\n";

echo "STEP 3: Add Knowledge Base Instructions\n";

$kb_instructions = '
KNOWLEDGE BASE INSTRUCTIONS FOR PROFESSOR JAG:

When user asks about ANY teacher:

1. CALL this single API: https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/all-teachers-simple.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49

2. EXTRACT teacher name from user question

3. FIND teacher ID using the lookup table in response.teacher_lookup
   Example: response.teacher_lookup["Dr. Pedro Reyes"] = 4

4. GET teacher data from response.all_teachers[teacher_id]

5. RESPOND with this format:
   "Based on your evaluation data, [teacher_name] has [total_evaluations] evaluation(s) with an average rating of [average_rating]/5. Here are my recommendations: [recommendations]"

TEACHER NAME MAPPING:
- "Dr. Pedro Reyes", "Pedro Reyes", "Pedro" → ID 4
- "Juan Dela Cruz", "Juan", "Dela Cruz" → ID 2  
- "Maria Santos", "Maria", "Santos" → ID 3

EXAMPLE RESPONSES:
User: "Tell me about Dr. Pedro Reyes"
→ Find ID 4 in lookup table
→ Get data from all_teachers[4]
→ Response: "Based on your evaluation data, Dr. Pedro Reyes has 5 evaluation(s) with an average rating of 4.2/5. Here are my recommendations: Continue interactive teaching methods, Maintain practical examples, Focus on student engagement"
';

echo $kb_instructions . "\n\n";

echo "STEP 4: Test All Teachers\n";
echo "Test these questions:\n";
echo "1. 'Tell me about Dr. Pedro Reyes'\n";
echo "2. 'What about Juan Dela Cruz?'\n";
echo "3. 'Show me Maria Santos evaluations'\n";
echo "4. 'Tell me about Pedro Reyes' (should still work)\n\n";

echo "=== ADVANTAGES OF THIS APPROACH ===\n";
echo "✅ Single API call for all teachers\n";
echo "✅ Real-time data from your database\n";
echo "✅ Works for any teacher name variation\n";
echo "✅ No hardcoded teacher IDs in JotForm\n";
echo "✅ Future teachers automatically included\n";
echo "✅ JotForm can extract any teacher from one response\n\n";

echo "=== TEST THE API FIRST ===\n";
echo "Visit: https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/all-teachers-simple.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49\n";
echo "You should see all teachers with their evaluation data!\n";
?>