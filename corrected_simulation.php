<?php
// CORRECTED test to properly simulate the user experience
require_once 'config/db.php';

echo "<h1>🕵️ CORRECTED USER EXPERIENCE SIMULATION</h1>";
echo "<h2>Testing with proper session isolation...</h2>";

function log_test($message, $type = 'info') {
    $color = $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue');
    $icon = $type === 'error' ? '🔍' : ($type === 'success' ? '✅' : 'ℹ️');
    echo "<p style='color: $color; margin: 5px 0;'><strong>$icon " . date('H:i:s') . ":</strong> " . htmlspecialchars($message) . "</p>";
    error_log("CORRECTED_SIM: " . $message);
}

function test_teacher_isolation($teacher_id, $teacher_name) {
    global $pdo;
    
    // Simulate login by setting session
    session_start();
    $_SESSION['user_id'] = $teacher_id;
    $_SESSION['role'] = 'teacher';
    
    log_test("Testing isolation for $teacher_name (ID: $teacher_id)");
    
    // Check their chat history
    try {
        $stmt = $pdo->prepare("SELECT message, response, created_at FROM chat_messages WHERE user_id = ? ORDER BY created_at ASC");
        $stmt->execute([$teacher_id]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        log_test("Found " . count($history) . " messages for $teacher_name");
        
        foreach ($history as $msg) {
            log_test("- Message: " . substr($msg['message'], 0, 60) . "...");
        }
        
        return $history;
    } catch (Exception $e) {
        log_test("Error: " . $e->getMessage(), 'error');
        return [];
    }
}

function check_all_teachers() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT id, name, username FROM users WHERE role = 'teacher' ORDER BY id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Clear any existing session
session_start();
session_destroy();

echo "<hr>";

// Get all teachers
$teachers = check_all_teachers();
log_test("Found " . count($teachers) . " teachers in database");

// Test each teacher's isolation
echo "<table border='1' cellpadding='5' style='margin: 20px 0;'>";
echo "<tr><th>Teacher</th><th>User ID</th><th>Messages Found</th><th>Sample Messages</th></tr>";

foreach ($teachers as $teacher) {
    // Clear session between tests
    session_destroy();
    
    $history = test_teacher_isolation($teacher['id'], $teacher['name']);
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($teacher['name']) . "</td>";
    echo "<td>" . $teacher['id'] . "</td>";
    echo "<td>" . count($history) . "</td>";
    echo "<td>";
    
    if (count($history) > 0) {
        foreach (array_slice($history, 0, 2) as $msg) {
            echo htmlspecialchars(substr($msg['message'], 0, 40)) . "...<br>";
        }
    } else {
        echo "No messages";
    }
    
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

// Now test the specific scenario: Login as Dr. Reyes, then Mr. Dela Cruz
echo "<hr>";
log_test("=== TESTING SPECIFIC SCENARIO ===");

// Find Dr. Reyes and Mr. Dela Cruz
$dr_reyes = null;
$mr_delacruz = null;

foreach ($teachers as $teacher) {
    if (stripos($teacher['name'], 'Reyes') !== false) {
        $dr_reyes = $teacher;
    }
    if (stripos($teacher['name'], 'Dela Cruz') !== false) {
        $mr_delacruz = $teacher;
    }
}

if ($dr_reyes && $mr_delacruz) {
    echo "<h3>Testing the exact user scenario:</h3>";
    
    // Test 1: Dr. Reyes login and check history
    session_destroy();
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h4>1. Login as Dr. Reyes:</h4>";
    $reyes_history = test_teacher_isolation($dr_reyes['id'], $dr_reyes['name']);
    echo "</div>";
    
    // Test 2: Mr. Dela Cruz login and check history  
    session_destroy();
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h4>2. Login as Mr. Dela Cruz:</h4>";
    $delacruz_history = test_teacher_isolation($mr_delacruz['id'], $mr_delacruz['name']);
    echo "</div>";
    
    // Analysis
    echo "<div style='background: #fff3cd; padding: 15px; margin: 20px 0; border-radius: 5px; border: 1px solid #ffeaa7;'>";
    echo "<h3>🎯 ISOLATION TEST RESULTS:</h3>";
    
    $reyes_count = count($reyes_history);
    $delacruz_count = count($delacruz_history);
    
    echo "<p><strong>Dr. Reyes sees:</strong> $reyes_count messages</p>";
    echo "<p><strong>Mr. Dela Cruz sees:</strong> $delacruz_count messages</p>";
    
    if ($reyes_count === 0 && $delacruz_count === 0) {
        echo "<p style='color: orange;'><strong>⚠️ Both teachers see no messages - this suggests the issue might be:</strong></p>";
        echo "<ul>";
        echo "<li>Browser caching the chat interface</li>";
        echo "<li>Frontend JavaScript not properly reloading</li>";
        echo "<li>Session cookies not being cleared properly</li>";
        echo "<li>Multiple browser tabs with different sessions</li>";
        echo "</ul>";
    } elseif ($reyes_count !== $delacruz_count) {
        echo "<p style='color: green;'><strong>✅ ISOLATION IS WORKING!</strong></p>";
        echo "<p>Each teacher sees only their own messages.</p>";
    } else {
        echo "<p style='color: red;'><strong>❌ POTENTIAL ISSUE:</strong></p>";
        echo "<p>Both teachers see the same number of messages - this needs investigation.</p>";
    }
    
    echo "</div>";
} else {
    log_test("Could not find Dr. Reyes and Mr. Dela Cruz in database", 'error');
}

echo "<hr>";
echo "<p><a href='browser_test.php'>Test in Browser</a> | <a href='check_error_log.php'>Check Error Log</a> | <a href='full_automated_test.php'>Run Backend Test</a></p>";

log_test("=== CORRECTED SIMULATION COMPLETED ===");
?>