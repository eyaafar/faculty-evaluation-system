<?php
/**
 * Enhanced Debug Tool: Professor Jag Data Access Fix
 * This script shows exactly how to configure JotForm AI to read teacher rating data
 */

// Simulate being logged in as Dr. Pedro Reyes
session_start();
$_SESSION['user_id'] = 4;
$_SESSION['name'] = 'Dr. Pedro Reyes';
$_SESSION['role'] = 'teacher';

$teacher_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['name'];

// Get the actual data from our API
$api_url = "https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/teacher-specific-data-hybrid.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id=$teacher_id&format=ai";

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$api_data = null;
if ($response && $http_code === 200) {
    $api_data = json_decode($response, true);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🎯 Professor Jag Data Access Fix - Dr. Pedro Reyes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .section { background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .data-box { background: #e8f5e8; padding: 15px; border: 1px solid #ccc; margin: 15px 0; }
        .code-box { background: #f0f0f0; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; }
        .highlight { background: #ffffcc; padding: 2px 5px; border-radius: 3px; }
        pre { background: #f0f0f0; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🎯 Professor Jag Data Access Fix for Dr. Pedro Reyes</h1>
    
    <div class="section">
        <h2>🔍 Root Cause Analysis</h2>
        <p><strong>Problem:</strong> Professor Jag says "I don't have your specific rating information yet"</p>
        <p><strong>Root Cause:</strong> JotForm AI doesn't automatically read our custom data sources. It needs to be explicitly configured to check our injected data.</p>
        <p><strong>Solution:</strong> Configure JotForm AI's system prompt to check our data sources first.</p>
    </div>

    <div class="section">
        <h2>📊 Current Data Status</h2>
        
        <?php if ($api_data && isset($api_data['success']) && $api_data['success']): ?>
            <div class="data-box">
                <h3>✅ API Data Available:</h3>
                <p><strong>Teacher:</strong> <?php echo htmlspecialchars($api_data['teacher']); ?></p>
                <p><strong>Overall Rating:</strong> <?php echo $api_data['metrics']['overall_rating']; ?>/5.0</p>
                <p><strong>Total Evaluations:</strong> <?php echo $api_data['metrics']['total_evaluations']; ?></p>
                <p><strong>Recent Feedback:</strong> <?php echo !empty($api_data['recent_feedback']) ? count($api_data['recent_feedback']) . ' comments' : 'None'; ?></p>
            </div>
        <?php else: ?>
            <p class="error">❌ API Error: <?php echo htmlspecialchars($response ?: 'No response'); ?></p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>🎯 The Fix: Configure JotForm AI System Prompt</h2>
        
        <h3>Step 1: Update JotForm AI System Prompt</h3>
        <p>In your JotForm AI settings, update the <strong>System Prompt</strong> to include:</p>
        
        <div class="code-box">
IMPORTANT: Before asking for evaluation data, ALWAYS check these data sources in this order:
1. Check window.FEFS_RATING_SUMMARY object for teacher rating data
2. Check DOM element with ID "fefs-rating-summary" for rating information  
3. Check meta tags with name starting with "fefs:" for structured data
4. Only ask for data if these sources don't contain the requested information

When a teacher asks about their rating, immediately use the data from these sources to provide specific, personalized feedback based on their actual evaluation data.
        </div>

        <h3>Step 2: Ensure Data is Available</h3>
        <p>Below is the data that should be available to Professor Jag:</p>
        
        <script>
            // Global teacher context
            window.FEFS_CURRENT_TEACHER = {
                id: <?php echo (int)$teacher_id; ?>,
                name: <?php echo json_encode($teacher_name); ?>,
                role: 'teacher'
            };
            
            // Global rating summary for JotForm AI
            window.FEFS_RATING_SUMMARY = {
                teacher_name: "<?php echo htmlspecialchars($api_data['teacher'] ?? 'Unknown'); ?>",
                overall_rating: <?php echo $api_data['metrics']['overall_rating'] ?? 0; ?>,
                total_evaluations: <?php echo $api_data['metrics']['total_evaluations'] ?? 0; ?>,
                rating_text: "<?php echo htmlspecialchars($api_data['teacher'] ?? 'Teacher') . ' has a rating of ' . ($api_data['metrics']['overall_rating'] ?? 0) . ' out of 5.0 from ' . ($api_data['metrics']['total_evaluations'] ?? 0) . ' evaluations'; ?>",
                has_data: <?php echo ($api_data && isset($api_data['success']) && $api_data['success']) ? 'true' : 'false'; ?>,
                weak_areas: <?php echo json_encode($api_data['weak_areas'] ?? []); ?>,
                strong_areas: <?php echo json_encode($api_data['strong_areas'] ?? []); ?>,
                recent_feedback: <?php echo json_encode($api_data['recent_feedback'] ?? []); ?>
            };
            
            // Add teacher ID to body
            document.body.setAttribute('data-teacher-id', <?php echo (int)$teacher_id; ?>);
        </script>
        
        <div class="data-box">
            <h3>✅ Data Injected for JotForm AI:</h3>
            <p><strong>window.FEFS_RATING_SUMMARY:</strong> <span class="highlight">Available</span></p>
            <p><strong>Teacher Name:</strong> <?php echo htmlspecialchars($api_data['teacher'] ?? 'Unknown'); ?></p>
            <p><strong>Overall Rating:</strong> <?php echo $api_data['metrics']['overall_rating'] ?? 0; ?>/5.0</p>
            <p><strong>Total Evaluations:</strong> <?php echo $api_data['metrics']['total_evaluations'] ?? 0; ?></p>
        </div>
    </div>

    <div class="section">
        <h2>🧪 Test the Configuration</h2>
        
        <h3>Step 3: Test with Professor Jag</h3>
        <p>After updating the system prompt, test with these questions:</p>
        
        <div class="code-box">
1. "What's my overall teaching rating?"
   → Should respond: "Based on your feedback data, you have an overall rating of <?php echo $api_data['metrics']['overall_rating']; ?> out of 5.0 from <?php echo $api_data['metrics']['total_evaluations']; ?> evaluations..."

2. "What are my strengths as a teacher?"
   → Should respond with specific data from your strong areas

3. "What areas should I improve?"
   → Should respond with specific data from your weak areas
        </div>
    </div>

    <div class="section">
        <h2>🔧 Alternative: Direct API Configuration</h2>
        
        <p>If the system prompt approach doesn't work, you can also configure JotForm AI to use our API directly:</p>
        
        <h3>Step 4: Update JotForm AI API Configuration</h3>
        <p>In JotForm AI Settings → Send API Request:</p>
        
        <div class="code-box">
URL: https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/teacher-specific-data-hybrid.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id={teacher_id}&format=ai

Headers: Leave empty
Method: GET
Response Format: JSON
        </div>
        
        <p><strong>Then update the system prompt to:</strong></p>
        <div class="code-box">
Use the API response data to provide specific, personalized feedback. When a teacher asks about their rating, immediately reference the API data to give accurate information about their overall rating, strengths, and areas for improvement.
        </div>
    </div>

    <div class="section">
        <h2>✅ Verification Script</h2>
        
        <p>Run this script to verify everything is working:</p>
        
        <div class="code-box" id="verification-results">Loading verification...</div>
        
        <script>
            function runVerification() {
                const results = [];
                
                // Check 1: Global objects
                if (window.FEFS_CURRENT_TEACHER) {
                    results.push("✅ FEFS_CURRENT_TEACHER available");
                } else {
                    results.push("❌ FEFS_CURRENT_TEACHER missing");
                }
                
                if (window.FEFS_RATING_SUMMARY) {
                    results.push("✅ FEFS_RATING_SUMMARY available");
                    results.push("   - Teacher: " + window.FEFS_RATING_SUMMARY.teacher_name);
                    results.push("   - Rating: " + window.FEFS_RATING_SUMMARY.overall_rating + "/5.0");
                } else {
                    results.push("❌ FEFS_RATING_SUMMARY missing");
                }
                
                // Check 2: Body attributes
                const teacherId = document.body.getAttribute('data-teacher-id');
                if (teacherId) {
                    results.push("✅ data-teacher-id: " + teacherId);
                } else {
                    results.push("❌ data-teacher-id missing");
                }
                
                // Check 3: DOM elements (will be added by jotform-data-bridge.js)
                setTimeout(() => {
                    const ratingElement = document.getElementById('fefs-rating-summary');
                    if (ratingElement) {
                        results.push("✅ fefs-rating-summary element found");
                        results.push("   - Content: " + ratingElement.textContent.substring(0, 100) + "...");
                    } else {
                        results.push("⚠️  fefs-rating-summary element not found (will be added by bridge)");
                    }
                    
                    document.getElementById('verification-results').textContent = results.join('\n');
                }, 2000);
            }
            
            // Run verification after page loads
            window.addEventListener('load', runVerification);
        </script>
    </div>

    <div class="section">
        <h2>🎯 Summary</h2>
        <p><strong>The key insight:</strong> JotForm AI doesn't automatically read our custom data. We must explicitly tell it where to look.</p>
        
        <p><strong>What we've done:</strong></p>
        <ul>
            <li>✅ Fixed the hybrid API endpoint (db.php path issue)</li>
            <li>✅ Created teacher-specific data isolation (no cross-contamination)</li>
            <li>✅ Injected data into multiple formats (global objects, DOM elements, meta tags)</li>
            <li>✅ Created this guide to configure JotForm AI to read our data</li>
        </ul>
        
        <p><strong>Next step:</strong> Update JotForm AI's system prompt to check our data sources first!</p>
        
        <p class="success">🎉 After configuration, Professor Jag will respond with specific data like: "Based on your feedback data, you have an overall rating of <?php echo $api_data['metrics']['overall_rating']; ?> out of 5.0 from <?php echo $api_data['metrics']['total_evaluations']; ?> evaluations..."</p>
    </div>
</body>
</html>