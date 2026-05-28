<?php
// Automated test script to debug the shared chat issue
// This script will simulate the login/logout process and check session behavior

header('Content-Type: text/html; charset=utf-8');
echo "<h1>Automated Session Testing</h1>";
echo "<h2>Starting comprehensive session debugging...</h2>";

// Function to log debug messages
function log_debug($message) {
    echo "<p><strong>" . date('H:i:s') . ":</strong> " . htmlspecialchars($message) . "</p>";
    error_log("AUTOMATED_TEST: " . $message);
}

// Function to check current session state
function check_session_state() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $state = [
        'session_id' => session_id(),
        'user_id' => $_SESSION['user_id'] ?? 'NOT SET',
        'username' => $_SESSION['username'] ?? 'NOT SET',
        'name' => $_SESSION['name'] ?? 'NOT SET',
        'role' => $_SESSION['role'] ?? 'NOT SET'
    ];
    
    return $state;
}

// Function to simulate login
function simulate_login($username, $password) {
    log_debug("Attempting login for: $username");
    
    // Include database connection
    require_once 'config/db.php';
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('SELECT id, name, username, password, role FROM users WHERE id_number = ? OR username = ? LIMIT 1');
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Clear any existing session
            session_destroy();
            session_start();
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            session_regenerate_id(true);
            
            log_debug("Login successful for: " . $user['username'] . " (ID: " . $user['id'] . ")");
            return true;
        } else {
            log_debug("Login failed for: $username");
            return false;
        }
    } catch (Exception $e) {
        log_debug("Login error: " . $e->getMessage());
        return false;
    }
}

// Function to simulate logout
function simulate_logout() {
    log_debug("Performing logout...");
    session_destroy();
    log_debug("Session destroyed");
}

// Function to check chat history
function check_chat_history($user_id) {
    log_debug("Checking chat history for user_id: $user_id");
    
    require_once 'config/db.php';
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as message_count FROM chat_messages WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        log_debug("Found " . $result['message_count'] . " messages for user_id: $user_id");
        
        // Get actual messages
        $stmt = $pdo->prepare("SELECT message, response, created_at FROM chat_messages WHERE user_id = ? ORDER BY created_at ASC LIMIT 3");
        $stmt->execute([$user_id]);
        $messages = $stmt->fetchAll();
        
        if (count($messages) > 0) {
            log_debug("Sample messages:");
            foreach ($messages as $msg) {
                log_debug("- " . substr($msg['message'], 0, 50) . "... (" . $msg['created_at'] . ")");
            }
        }
        
        return $result['message_count'];
    } catch (Exception $e) {
        log_debug("Error checking chat history: " . $e->getMessage());
        return 0;
    }
}

// Function to simulate a chat message
function simulate_chat($message) {
    log_debug("Simulating chat message: $message");
    
    require_once 'config/db.php';
    global $pdo;
    
    $teacher_id = $_SESSION['user_id'] ?? 0;
    if (!$teacher_id) {
        log_debug("ERROR: No teacher_id in session for chat simulation");
        return false;
    }
    
    try {
        // Generate a simple response
        $response = "This is a test response to: " . $message;
        
        // Save the conversation
        $stmt = $pdo->prepare("INSERT INTO chat_messages (user_id, message, response) VALUES (?, ?, ?)");
        $stmt->execute([$teacher_id, $message, $response]);
        
        log_debug("Chat message saved for user_id: $teacher_id");
        return true;
    } catch (Exception $e) {
        log_debug("Error saving chat: " . $e->getMessage());
        return false;
    }
}

// Start the test sequence
echo "<hr>";

// Test 1: Initial state
log_debug("=== TEST 1: Initial Session State ===");
$initial_state = check_session_state();
log_debug("Initial session: " . json_encode($initial_state));

// Test 2: Login as Dr. Pedro Reyes (assuming username: dr_reyes, password: test123)
log_debug("=== TEST 2: Login as Dr. Pedro Reyes ===");
$login1_success = simulate_login('dr_reyes', 'test123');
if ($login1_success) {
    $state_after_login1 = check_session_state();
    log_debug("Session after login: " . json_encode($state_after_login1));
    
    // Test 3: Send a chat message as Dr. Reyes
    log_debug("=== TEST 3: Send chat message as Dr. Reyes ===");
    simulate_chat("Hello Professor Jag, this is Dr. Pedro Reyes");
    
    // Check chat history
    check_chat_history($_SESSION['user_id']);
} else {
    log_debug("Failed to login as Dr. Reyes - trying alternative credentials");
    // Try with different username format
    $login1_success = simulate_login('pedro.reyes', 'test123');
    if ($login1_success) {
        $state_after_login1 = check_session_state();
        log_debug("Session after login: " . json_encode($state_after_login1));
        
        // Test 3: Send a chat message as Dr. Reyes
        log_debug("=== TEST 3: Send chat message as Dr. Reyes ===");
        simulate_chat("Hello Professor Jag, this is Dr. Pedro Reyes");
        
        // Check chat history
        check_chat_history($_SESSION['user_id']);
    }
}

// Test 4: Logout
log_debug("=== TEST 4: Logout ===");
simulate_logout();
$state_after_logout = check_session_state();
log_debug("Session after logout: " . json_encode($state_after_logout));

// Test 5: Login as Mr. Juan Dela Cruz (assuming username: juan.delacruz, password: test123)
log_debug("=== TEST 5: Login as Mr. Juan Dela Cruz ===");
$login2_success = simulate_login('juan.delacruz', 'test123');
if (!$login2_success) {
    $login2_success = simulate_login('mr.delacruz', 'test123');
}
if (!$login2_success) {
    $login2_success = simulate_login('jdelacruz', 'test123');
}

if ($login2_success) {
    $state_after_login2 = check_session_state();
    log_debug("Session after second login: " . json_encode($state_after_login2));
    
    // Test 6: Check if Mr. Dela Cruz sees Dr. Reyes' messages
    log_debug("=== TEST 6: Check if Mr. Dela Cruz sees previous messages ===");
    $message_count = check_chat_history($_SESSION['user_id']);
    
    if ($message_count > 0) {
        log_debug("ERROR: Mr. Dela Cruz can see messages from previous user!");
    } else {
        log_debug("SUCCESS: Mr. Dela Cruz sees only his own messages (or none)");
    }
    
    // Test 7: Send a message as Mr. Dela Cruz
    log_debug("=== TEST 7: Send chat message as Mr. Dela Cruz ===");
    simulate_chat("Hello Professor Jag, this is Mr. Juan Dela Cruz");
    
    // Final check
    check_chat_history($_SESSION['user_id']);
} else {
    log_debug("Failed to login as Mr. Dela Cruz");
}

// Summary
echo "<hr>";
echo "<h2>Test Summary</h2>";
echo "<p>Check the PHP error log for detailed debug information.</p>";
echo "<p><a href='check_error_log.php'>View Error Log</a></p>";
echo "<p><a href='check_session.php'>Check Current Session</a></p>";

log_debug("=== AUTOMATED TEST COMPLETED ===");
?>