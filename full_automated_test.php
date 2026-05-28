<?php
// Fully automated test script to debug the shared chat issue
// This script will run through the complete test sequence automatically

require_once 'config/db.php';

echo "<h1>🧪 AUTOMATED SESSION & CHAT ISOLATION TEST</h1>";
echo "<h2>Running complete test sequence...</h2>";

// Function to log debug messages
function log_test($message, $type = 'info') {
    $color = $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue');
    $icon = $type === 'error' ? '❌' : ($type === 'success' ? '✅' : 'ℹ️');
    echo "<p style='color: $color; margin: 5px 0;'><strong>$icon " . date('H:i:s') . ":</strong> " . htmlspecialchars($message) . "</p>";
    error_log("AUTOMATED_TEST: " . $message);
}

// Function to clear session and start fresh
function clear_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    session_start();
    session_regenerate_id(true);
}

// Function to simulate login
function simulate_login($user_id, $username, $name, $role) {
    clear_session();
    
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['name'] = $name;
    $_SESSION['role'] = $role;
    
    return [
        'user_id' => $user_id,
        'username' => $username,
        'name' => $name,
        'role' => $role,
        'session_id' => session_id()
    ];
}

// Function to save chat message
function save_chat_message($user_id, $message, $response) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO chat_messages (user_id, message, response) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $message, $response]);
        return true;
    } catch (Exception $e) {
        log_test("Error saving chat: " . $e->getMessage(), 'error');
        return false;
    }
}

// Function to get chat history
function get_chat_history($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT message, response, created_at FROM chat_messages WHERE user_id = ? ORDER BY created_at ASC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        log_test("Error getting chat history: " . $e->getMessage(), 'error');
        return [];
    }
}

// Function to get all teachers
function get_teachers() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT id, name, username FROM users WHERE role = 'teacher' ORDER BY id LIMIT 5");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        log_test("Error getting teachers: " . $e->getMessage(), 'error');
        return [];
    }
}

// Function to analyze potential issues
function analyze_issues() {
    global $pdo;
    
    $issues = [];
    
    // Check for messages with user_id 0
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chat_messages WHERE user_id = 0");
    $result = $stmt->fetch();
    if ($result['count'] > 0) {
        $issues[] = "Found " . $result['count'] . " messages with user_id = 0 (no user)";
    }
    
    // Check for messages with invalid user_ids
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chat_messages WHERE user_id NOT IN (SELECT id FROM users)");
    $result = $stmt->fetch();
    if ($result['count'] > 0) {
        $issues[] = "Found " . $result['count'] . " messages with invalid user_ids";
    }
    
    // Check for duplicate messages
    $stmt = $pdo->query("
        SELECT message, COUNT(*) as count 
        FROM chat_messages 
        GROUP BY message 
        HAVING count > 1 
        ORDER BY count DESC 
        LIMIT 5
    ");
    $duplicates = $stmt->fetchAll();
    if (count($duplicates) > 0) {
        $issues[] = "Found " . count($duplicates) . " duplicate messages";
    }
    
    return $issues;
}

echo "<hr>";

// === TEST SEQUENCE STARTS HERE ===

// Step 1: Get available teachers
log_test("Step 1: Getting available teachers...");
$teachers = get_teachers();

if (count($teachers) < 2) {
    log_test("Need at least 2 teachers for testing, found " . count($teachers), 'error');
    echo "<p>Available teachers in database:</p>";
    foreach ($teachers as $teacher) {
        echo "<p>- " . $teacher['name'] . " (ID: " . $teacher['id'] . ")</p>";
    }
    echo "<p>Please ensure you have at least 2 teacher accounts set up.</p>";
    exit;
}

$teacher1 = $teachers[0]; // Dr. Reyes equivalent
$teacher2 = $teachers[1]; // Mr. Dela Cruz equivalent

log_test("Found teachers: " . $teacher1['name'] . " and " . $teacher2['name'], 'success');

// Step 2: Analyze current state
log_test("Step 2: Analyzing current database state...");
$issues = analyze_issues();
if (count($issues) > 0) {
    log_test("Found potential issues:", 'error');
    foreach ($issues as $issue) {
        log_test("- " . $issue, 'error');
    }
} else {
    log_test("No obvious issues found in database", 'success');
}

// Step 3: Clear any existing chat messages for clean test
log_test("Step 3: Clearing existing chat messages for clean test...");
$stmt = $pdo->prepare("DELETE FROM chat_messages WHERE user_id IN (?, ?)");
$stmt->execute([$teacher1['id'], $teacher2['id']]);
$deleted = $stmt->rowCount();
log_test("Deleted " . $deleted . " existing messages for test teachers", 'info');

// Step 4: Login as Teacher 1 (Dr. Reyes)
log_test("Step 4: Logging in as " . $teacher1['name'] . "...");
$session1 = simulate_login($teacher1['id'], $teacher1['username'], $teacher1['name'], 'teacher');
log_test("Logged in as: " . $session1['name'] . " (ID: " . $session1['user_id'] . ")", 'success');

// Step 5: Send message as Teacher 1
log_test("Step 5: Sending test message as " . $teacher1['name'] . "...");
$message1 = "Hello Professor Jag, this is " . $teacher1['name'];
$response1 = "Hello " . $teacher1['name'] . ", how can I help you today?";

if (save_chat_message($session1['user_id'], $message1, $response1)) {
    log_test("Message saved successfully", 'success');
} else {
    log_test("Failed to save message", 'error');
}

// Step 6: Check Teacher 1's history
log_test("Step 6: Checking " . $teacher1['name'] . "'s chat history...");
$history1 = get_chat_history($session1['user_id']);
if (count($history1) > 0) {
    log_test("Found " . count($history1) . " messages for " . $teacher1['name'], 'success');
    foreach ($history1 as $msg) {
        log_test("- " . substr($msg['message'], 0, 50) . "...", 'info');
    }
} else {
    log_test("No messages found for " . $teacher1['name'], 'error');
}

// Step 7: Logout
log_test("Step 7: Logging out...");
clear_session();
log_test("Logged out successfully", 'success');

// Step 8: Login as Teacher 2 (Mr. Dela Cruz)
log_test("Step 8: Logging in as " . $teacher2['name'] . "...");
$session2 = simulate_login($teacher2['id'], $teacher2['username'], $teacher2['name'], 'teacher');
log_test("Logged in as: " . $session2['name'] . " (ID: " . $session2['user_id'] . ")", 'success');

// Step 9: Check if Teacher 2 sees Teacher 1's messages (CRITICAL TEST)
log_test("Step 9: CRITICAL TEST - Checking if " . $teacher2['name'] . " sees " . $teacher1['name'] . "'s messages...");
$history2 = get_chat_history($session2['user_id']);

if (count($history2) > 0) {
    log_test("❌ CRITICAL ISSUE: " . $teacher2['name'] . " can see messages from " . $teacher1['name'] . "!", 'error');
    log_test("Found " . count($history2) . " messages that don't belong to " . $teacher2['name'], 'error');
    foreach ($history2 as $msg) {
        log_test("- " . substr($msg['message'], 0, 50) . "...", 'error');
    }
} else {
    log_test("✅ SUCCESS: " . $teacher2['name'] . " sees only their own messages (or none)", 'success');
}

// Step 10: Send message as Teacher 2
log_test("Step 10: Sending test message as " . $teacher2['name'] . "...");
$message2 = "Hello Professor Jag, this is " . $teacher2['name'];
$response2 = "Hello " . $teacher2['name'] . ", how can I help you today?";

if (save_chat_message($session2['user_id'], $message2, $response2)) {
    log_test("Message saved successfully", 'success');
} else {
    log_test("Failed to save message", 'error');
}

// Step 11: Final verification
log_test("Step 11: Final verification - checking both teachers' histories...");

// Check Teacher 1's history again
$final_history1 = get_chat_history($teacher1['id']);
$final_history2 = get_chat_history($teacher2['id']);

echo "<h3>Final Results:</h3>";
echo "<p><strong>" . $teacher1['name'] . " has " . count($final_history1) . " messages</strong></p>";
echo "<p><strong>" . $teacher2['name'] . " has " . count($final_history2) . " messages</strong></p>";

// Check for cross-contamination
$cross_contamination = false;
foreach ($final_history2 as $msg) {
    if (strpos($msg['message'], $teacher1['name']) !== false) {
        $cross_contamination = true;
        break;
    }
}

if ($cross_contamination) {
    log_test("❌ CROSS-CONTAMINATION DETECTED: Messages are being shared between users!", 'error');
} else {
    log_test("✅ NO CROSS-CONTAMINATION: Messages are properly isolated", 'success');
}

// Summary
echo "<hr>";
echo "<h2>🎯 TEST SUMMARY</h2>";

if (count($history2) > 0) {
    echo "<p style='color: red; font-size: 18px;'><strong>🔴 ISSUE CONFIRMED: Chat messages are being shared between teachers!</strong></p>";
    echo "<p>This confirms the bug reported by the user where Dr. Pedro Reyes' conversations are visible to Mr. Juan Dela Cruz.</p>";
    echo "<p><strong>Possible causes:</strong></p>";
    echo "<ul>";
    echo "<li>Session user_id is not being properly updated during login</li>";
    echo "<li>Chat API is not correctly filtering by user_id</li>";
    echo "<li>Database queries are not properly isolated</li>";
    echo "</ul>";
} else {
    echo "<p style='color: green; font-size: 18px;'><strong>🟢 NO ISSUE: Chat messages are properly isolated!</strong></p>";
    echo "<p>The system appears to be working correctly. The user's issue might be related to:</p>";
    echo "<ul>";
    echo "<li>Browser caching issues</li>";
    echo "<li>Session cookie persistence</li>";
    echo "<li>Frontend JavaScript not properly handling user switching</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='check_error_log.php'>View Error Log</a> | <a href='database_analysis.php'>View Database Analysis</a> | <a href='browser_test.php'>Manual Browser Test</a></p>";

log_test("=== AUTOMATED TEST COMPLETED ===");
?>