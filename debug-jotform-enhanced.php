<?php
/**
 * Enhanced Debug Tool: JotForm AI Data Integration Test
 * This directly tests what JotForm AI should be seeing
 */

// Simulate being logged in as Dr. Pedro Reyes
session_start();
$_SESSION['user_id'] = 4;
$_SESSION['name'] = 'Dr. Pedro Reyes';
$_SESSION['role'] = 'teacher';

$teacher_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['name'];

// Test the API endpoint directly
$api_url = "https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/teacher-specific-data-hybrid.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id=$teacher_id&format=ai";

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
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
    <title>Enhanced Debug: JotForm AI Integration</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-section { background: #f5f5f5; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .data-box { background: #e8f5e8; padding: 10px; border: 1px solid #ccc; margin: 10px 0; }
        pre { background: #f0f0f0; padding: 10px; overflow-x: auto; }
        .jotform-container { border: 2px solid #007cba; padding: 10px; margin: 20px 0; background: #f0f8ff; }
    </style>
</head>
<body>
    <h1>🎯 Enhanced Debug: JotForm AI Integration Test</h1>
    
    <div class="debug-section">
        <h2>Current Session Info</h2>
        <p><strong>Teacher ID:</strong> <?php echo $teacher_id; ?></p>
        <p><strong>Teacher Name:</strong> <?php echo htmlspecialchars($teacher_name); ?></p>
        <p><strong>Role:</strong> <?php echo $_SESSION['role']; ?></p>
    </div>

    <div class="debug-section">
        <h2>📊 Direct API Test Results</h2>
        <?php if ($api_data && isset($api_data['success']) && $api_data['success']): ?>
            <p class="success">✅ API Response Successful!</p>
            <div class="data-box">
                <p><strong>Teacher:</strong> <?php echo htmlspecialchars($api_data['teacher']); ?></p>
                <p><strong>Overall Rating:</strong> <?php echo $api_data['metrics']['overall_rating']; ?>/5.0</p>
                <p><strong>Total Evaluations:</strong> <?php echo $api_data['metrics']['total_evaluations']; ?></p>
                <p><strong>AI Summary Preview:</strong></p>
                <pre><?php echo htmlspecialchars(substr($api_data['ai_summary'], 0, 500)) . '...'; ?></pre>
            </div>
        <?php else: ?>
            <p class="error">❌ API Error</p>
            <pre><?php echo htmlspecialchars($response ?: $error); ?></pre>
        <?php endif; ?>
    </div>

    <!-- Enhanced setup with multiple data injection methods -->
    <script>
        // Method 1: Global teacher context
        window.FEFS_CURRENT_TEACHER = {
            id: <?php echo (int)$teacher_id; ?>,
            name: <?php echo json_encode($teacher_name); ?>,
            role: 'teacher'
        };
        
        // Method 2: Add teacher ID to body
        document.body.setAttribute('data-teacher-id', <?php echo (int)$teacher_id; ?>);
        
        // Method 3: Store API data globally (if available)
        <?php if ($api_data && isset($api_data['success']) && $api_data['success']): ?>
        window.FEFS_TEACHER_DATA = <?php echo json_encode($api_data); ?>;
        <?php endif; ?>
        
        // Method 4: Create a comprehensive data summary that JotForm can easily parse
        <?php if ($api_data && isset($api_data['success']) && $api_data['success']): ?>
        window.FEFS_RATING_SUMMARY = {
            teacher_name: <?php echo json_encode($api_data['teacher']); ?>,
            overall_rating: <?php echo $api_data['metrics']['overall_rating']; ?>,
            total_evaluations: <?php echo $api_data['metrics']['total_evaluations']; ?>,
            rating_text: "<?php echo $api_data['metrics']['overall_rating']; ?> out of 5.0 from <?php echo $api_data['metrics']['total_evaluations']; ?> evaluations",
            has_data: true
        };
        <?php else: ?>
        window.FEFS_RATING_SUMMARY = {
            teacher_name: <?php echo json_encode($teacher_name); ?>,
            overall_rating: 0,
            total_evaluations: 0,
            rating_text: "No evaluation data available yet",
            has_data: false
        };
        <?php endif; ?>
    </script>
    
    <!-- JotForm Data Bridge -->
    <script src="assets/js/jotform-data-bridge.js"></script>
    
    <!-- Enhanced data injection for JotForm -->
    <script>
        // Method 5: Create a simple, easily parsable data structure
        function injectSimpleRatingData() {
            const ratingData = window.FEFS_RATING_SUMMARY;
            
            // Create a simple text element that JotForm can easily find
            const ratingElement = document.createElement('div');
            ratingElement.id = 'fefs-rating-summary';
            ratingElement.setAttribute('data-teacher-name', ratingData.teacher_name);
            ratingElement.setAttribute('data-overall-rating', ratingData.overall_rating);
            ratingElement.setAttribute('data-total-evaluations', ratingData.total_evaluations);
            ratingElement.setAttribute('data-has-data', ratingData.has_data);
            ratingElement.textContent = ratingData.rating_text;
            ratingElement.style.cssText = 'display: block !important; visibility: visible !important;';
            document.body.appendChild(ratingElement);
            
            // Also add as a meta tag for structured data
            const metaRating = document.createElement('meta');
            metaRating.name = 'fefs:overall-rating';
            metaRating.content = ratingData.overall_rating.toString();
            document.head.appendChild(metaRating);
            
            const metaEvaluations = document.createElement('meta');
            metaEvaluations.name = 'fefs:total-evaluations';
            metaEvaluations.content = ratingData.total_evaluations.toString();
            document.head.appendChild(metaEvaluations);
            
            console.log('✅ Injected simple rating data for JotForm:', ratingData);
        }
        
        // Method 6: Create a visible but styled element that JotForm can read
        function createVisibleDataElement() {
            const ratingData = window.FEFS_RATING_SUMMARY;
            
            const visibleElement = document.createElement('div');
            visibleElement.id = 'teacher-rating-display';
            visibleElement.className = 'teacher-metrics';
            visibleElement.innerHTML = `
                <h3>Teaching Performance Summary</h3>
                <p><strong>Teacher:</strong> ${ratingData.teacher_name}</p>
                <p><strong>Overall Rating:</strong> ${ratingData.overall_rating}/5.0</p>
                <p><strong>Total Evaluations:</strong> ${ratingData.total_evaluations}</p>
                <p><strong>Status:</strong> ${ratingData.has_data ? 'Data Available' : 'No Data Yet'}</p>
            `;
            visibleElement.style.cssText = 'padding: 15px; margin: 10px; border: 1px solid #ccc; background: #f9f9f9;';
            document.body.appendChild(visibleElement);
            
            console.log('✅ Created visible data element for JotForm');
        }
        
        // Inject data when page loads
        window.addEventListener('load', function() {
            setTimeout(function() {
                injectSimpleRatingData();
                createVisibleDataElement();
                
                // Also ensure the data bridge loads
                if (window.loadSystemData) {
                    window.loadSystemData();
                }
                
                console.log('✅ All data injection methods completed');
            }, 1000);
        });
    </script>
    
    <!-- JotForm AI Agent -->
    <script src='https://cdn.jotfor.ms/agent/embedjs/019de6a909f77a669d05a179ad6383a91272/embed.js'></script>
    
    <div class="debug-section">
        <h2>🔍 Data Injection Check</h2>
        <div id="injection-results"></div>
    </div>
    
    <div class="jotform-container">
        <h2>🤖 Professor Jag Chat Interface</h2>
        <p><strong>Test Question:</strong> "What's my overall teaching rating?"</p>
        <p><em>Professor Jag should now see: <?php echo htmlspecialchars($api_data['teacher'] ?? $teacher_name); ?> has a rating of <?php echo $api_data['metrics']['overall_rating'] ?? '0'; ?>/5.0 from <?php echo $api_data['metrics']['total_evaluations'] ?? '0'; ?> evaluations</em></p>
    </div>
    
    <div class="debug-section">
        <h2>📋 Available Data Summary</h2>
        <div id="final-summary"></div>
    </div>

    <script>
        // Final check to show what's available
        window.addEventListener('load', function() {
            setTimeout(function() {
                const resultsDiv = document.getElementById('injection-results');
                let html = '';
                
                // Check all our data sources
                html += '<h3>Data Sources Available:</h3>';
                
                if (window.FEFS_CURRENT_TEACHER) {
                    html += '<p class="success">✅ FEFS_CURRENT_TEACHER: ' + JSON.stringify(window.FEFS_CURRENT_TEACHER) + '</p>';
                }
                
                if (window.FEFS_RATING_SUMMARY) {
                    html += '<p class="success">✅ FEFS_RATING_SUMMARY: ' + JSON.stringify(window.FEFS_RATING_SUMMARY) + '</p>';
                }
                
                if (window.FEFS_TEACHER_DATA) {
                    html += '<p class="success">✅ FEFS_TEACHER_DATA available</p>';
                }
                
                const ratingElement = document.getElementById('fefs-rating-summary');
                if (ratingElement) {
                    html += '<p class="success">✅ Rating element found: ' + ratingElement.textContent + '</p>';
                }
                
                const visibleElement = document.getElementById('teacher-rating-display');
                if (visibleElement) {
                    html += '<p class="success">✅ Visible element found</p>';
                    html += '<div class="data-box">' + visibleElement.innerHTML + '</div>';
                }
                
                resultsDiv.innerHTML = html;
                
                // Final summary
                const summaryDiv = document.getElementById('final-summary');
                summaryDiv.innerHTML = `
                    <p><strong>Teacher Name:</strong> ${window.FEFS_RATING_SUMMARY.teacher_name}</p>
                    <p><strong>Overall Rating:</strong> ${window.FEFS_RATING_SUMMARY.overall_rating}/5.0</p>
                    <p><strong>Total Evaluations:</strong> ${window.FEFS_RATING_SUMMARY.total_evaluations}</p>
                    <p><strong>Rating Text:</strong> ${window.FEFS_RATING_SUMMARY.rating_text}</p>
                    <p class="${window.FEFS_RATING_SUMMARY.has_data ? 'success' : 'warning'}">
                        <strong>Has Data:</strong> ${window.FEFS_RATING_SUMMARY.has_data}
                    </p>
                `;
            }, 3000);
        });
    </script>
</body>
</html>