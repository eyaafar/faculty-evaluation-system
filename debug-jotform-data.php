<?php
/**
 * Debug Tool: Check JotForm Data Availability for Professor Jag
 * This script simulates being logged in as Dr. Pedro Reyes and shows what data is available
 */

// Simulate being logged in as Dr. Pedro Reyes
session_start();
$_SESSION['user_id'] = 4;
$_SESSION['name'] = 'Dr. Pedro Reyes';
$_SESSION['role'] = 'teacher';

$teacher_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['name'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Debug: JotForm Data for Dr. Pedro Reyes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-section { background: #f5f5f5; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .data-box { background: #e8f5e8; padding: 10px; border: 1px solid #ccc; margin: 10px 0; }
        pre { background: #f0f0f0; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Debug: JotForm Data Availability for Dr. Pedro Reyes</h1>
    
    <div class="debug-section">
        <h2>Current Session Info</h2>
        <p><strong>Teacher ID:</strong> <?php echo $teacher_id; ?></p>
        <p><strong>Teacher Name:</strong> <?php echo htmlspecialchars($teacher_name); ?></p>
        <p><strong>Role:</strong> <?php echo $_SESSION['role']; ?></p>
    </div>

    <!-- Same setup as feedback.php -->
    <script>
        // Global teacher context for JotForm AI
        window.FEFS_CURRENT_TEACHER = {
            id: <?php echo (int)$teacher_id; ?>,
            name: <?php echo json_encode($teacher_name); ?>,
            role: 'teacher'
        };
        
        // Add teacher ID to body for easy access
        document.body.setAttribute('data-teacher-id', <?php echo (int)$teacher_id; ?>);
        
        // FEFS API configuration (updated to use hybrid endpoint)
        window.FEFS_API = {
            endpoint: 'api/teacher-specific-data-hybrid.php',
            teacher_id: <?php echo (int)$teacher_id; ?>,
            api_key: '1b2423e7b7cba8c0d2105b08a7d57a49',
            useSession: true
        };
    </script>
    
    <!-- JotForm Data Bridge -->
    <script src="assets/js/jotform-data-bridge.js"></script>
    
    <!-- JotForm AI Agent -->
    <script src='https://cdn.jotfor.ms/agent/embedjs/019de6a909f77a669d05a179ad6383a91272/embed.js'></script>
    
    <div class="debug-section">
        <h2>🧪 Data Availability Check</h2>
        <p>Checking what data is available for Professor Jag...</p>
        
        <div id="data-check-results"></div>
    </div>
    
    <div class="debug-section">
        <h2>📋 Manual API Test</h2>
        <p>Testing the hybrid API endpoint directly...</p>
        <div id="api-test-results"></div>
    </div>
    
    <div class="debug-section">
        <h2>🔍 DOM Elements Check</h2>
        <p>Checking what DOM elements Professor Jag can access...</p>
        <div id="dom-check-results"></div>
    </div>

    <script>
        // Wait for everything to load
        window.addEventListener('load', function() {
            setTimeout(checkDataAvailability, 2000); // Wait 2 seconds for JotForm to initialize
        });
        
        function checkDataAvailability() {
            const resultsDiv = document.getElementById('data-check-results');
            let html = '';
            
            // Check 1: Global objects
            html += '<h3>Global Objects Check:</h3>';
            if (window.FEFS_CURRENT_TEACHER) {
                html += '<p class="success">✅ FEFS_CURRENT_TEACHER available</p>';
                html += '<pre>' + JSON.stringify(window.FEFS_CURRENT_TEACHER, null, 2) + '</pre>';
            } else {
                html += '<p class="error">❌ FEFS_CURRENT_TEACHER not found</p>';
            }
            
            if (window.FEFS_API) {
                html += '<p class="success">✅ FEFS_API available</p>';
                html += '<pre>' + JSON.stringify(window.FEFS_API, null, 2) + '</pre>';
            } else {
                html += '<p class="error">❌ FEFS_API not found</p>';
            }
            
            if (window.FEFSSystemData) {
                html += '<p class="success">✅ FEFSSystemData available</p>';
                html += '<pre>' + JSON.stringify(window.FEFSSystemData, null, 2) + '</pre>';
            } else {
                html += '<p class="error">❌ FEFSSystemData not found</p>';
            }
            
            // Check 2: Body attributes
            html += '<h3>Body Attributes Check:</h3>';
            const teacherIdAttr = document.body.getAttribute('data-teacher-id');
            if (teacherIdAttr) {
                html += '<p class="success">✅ data-teacher-id: ' + teacherIdAttr + '</p>';
            } else {
                html += '<p class="error">❌ data-teacher-id not found</p>';
            }
            
            const fefsReadyAttr = document.body.getAttribute('data-fefs-ready');
            if (fefsReadyAttr) {
                html += '<p class="success">✅ data-fefs-ready: ' + fefsReadyAttr + '</p>';
            } else {
                html += '<p class="error">❌ data-fefs-ready not found</p>';
            }
            
            // Check 3: DOM elements
            html += '<h3>DOM Elements Check:</h3>';
            const dataDiv = document.getElementById('fefs-jotform-data-ready');
            if (dataDiv) {
                html += '<p class="success">✅ fefs-jotform-data-ready element found</p>';
                html += '<pre>Attributes: ' + JSON.stringify(dataDiv.dataset, null, 2) + '</pre>';
            } else {
                html += '<p class="error">❌ fefs-jotform-data-ready element not found</p>';
            }
            
            const contextDiv = document.getElementById('fefs-ai-context');
            if (contextDiv) {
                html += '<p class="success">✅ fefs-ai-context element found</p>';
                html += '<pre>Content: ' + contextDiv.textContent.substring(0, 500) + '...</pre>';
            } else {
                html += '<p class="error">❌ fefs-ai-context element not found</p>';
            }
            
            // Check 4: Meta tags
            html += '<h3>Meta Tags Check:</h3>';
            const metaTags = document.querySelectorAll('meta[name^="fefs:"]');
            if (metaTags.length > 0) {
                html += '<p class="success">✅ Found ' + metaTags.length + ' FEFS meta tags</p>';
                metaTags.forEach(tag => {
                    html += '<p>' + tag.getAttribute('name') + ': ' + tag.getAttribute('content') + '</p>';
                });
            } else {
                html += '<p class="error">❌ No FEFS meta tags found</p>';
            }
            
            resultsDiv.innerHTML = html;
            
            // Now test the API directly
            testAPIEndpoint();
        }
        
        function testAPIEndpoint() {
            const apiDiv = document.getElementById('api-test-results');
            const teacherId = window.FEFS_CURRENT_TEACHER.id;
            const apiUrl = `api/teacher-specific-data-hybrid.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id=${teacherId}&format=ai`;
            
            apiDiv.innerHTML = '<p>Testing API: <code>' + apiUrl + '</code></p>';
            
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        apiDiv.innerHTML += '<p class="success">✅ API Success!</p>';
                        apiDiv.innerHTML += '<div class="data-box">';
                        apiDiv.innerHTML += '<p><strong>Teacher:</strong> ' + data.teacher + '</p>';
                        apiDiv.innerHTML += '<p><strong>Overall Rating:</strong> ' + data.metrics.overall_rating + '/5.0</p>';
                        apiDiv.innerHTML += '<p><strong>Total Evaluations:</strong> ' + data.metrics.total_evaluations + '</p>';
                        apiDiv.innerHTML += '<p><strong>AI Summary:</strong></p>';
                        apiDiv.innerHTML += '<pre>' + data.ai_summary + '</pre>';
                        apiDiv.innerHTML += '</div>';
                    } else {
                        apiDiv.innerHTML += '<p class="error">❌ API Error: ' + (data.error || 'Unknown') + '</p>';
                        apiDiv.innerHTML += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    }
                })
                .catch(error => {
                    apiDiv.innerHTML += '<p class="error">❌ Fetch Error: ' + error.message + '</p>';
                });
        }
        
        // Also check DOM elements after a delay
        setTimeout(function() {
            const domDiv = document.getElementById('dom-check-results');
            let domHtml = '<h3>Additional DOM Elements:</h3>';
            
            // Check for JSON-LD schema
            const schemaScript = document.querySelector('script[type="application/ld+json"]');
            if (schemaScript) {
                domHtml += '<p class="success">✅ JSON-LD schema found</p>';
                domHtml += '<pre>' + schemaScript.textContent.substring(0, 300) + '...</pre>';
            }
            
            // Check for context script
            const contextScript = document.getElementById('fefs-context-data');
            if (contextScript) {
                domHtml += '<p class="success">✅ Context script found</p>';
            }
            
            domDiv.innerHTML = domHtml;
        }, 3000);
    </script>
</body>
</html>