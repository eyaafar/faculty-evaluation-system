<?php
// Comprehensive test for session behavior and chat isolation

// Use standard session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>Comprehensive Session & Chat Test</h1>";

// Get database connection
try {
    if (file_exists('config/db.php')) {
        require_once 'config/db.php';
    } else if (file_exists('../config/db.php')) {
        require_once '../config/db.php';
    } else {
        throw new Exception('Database config not found');
    }
} catch (Exception $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
    exit;
}

// Test 1: Check session state
echo "<h2>Test 1: Session State</h2>";
if (isset($_SESSION['user_id'])) {
    echo "<p><strong>✓ Session is active</strong></p>";
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>Username: " . htmlspecialchars($_SESSION['username'] ?? 'N/A') . "</p>";
    echo "<p>Role: " . htmlspecialchars($_SESSION['role'] ?? 'N/A') . "</p>";
} else {
    echo "<p><strong>✗ Session is not active</strong></p>";
    echo "<p>No user logged in</p>";
}

// Test 2: Check chat messages table
echo "<h2>Test 2: Chat Messages Table</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM chat_messages");
    $count = $stmt->fetchColumn();
    echo "<p>Total chat messages: " . $count . "</p>";
    
    if ($count > 0) {
        $stmt = $pdo->query("SELECT DISTINCT user_id FROM chat_messages");
        $user_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>User IDs with messages: " . implode(', ', $user_ids) . "</p>";
    }
} catch (Exception $e) {
    echo "<p>Error checking chat messages: " . $e->getMessage() . "</p>";
}

// Test 3: Check teachers in database
echo "<h2>Test 3: Teachers in Database</h2>";
try {
    $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'teacher' ORDER BY name");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th></tr>";
    foreach ($teachers as $teacher) {
        echo "<tr>";
        echo "<td>" . $teacher['id'] . "</td>";
        echo "<td>" . htmlspecialchars($teacher['name']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p>Error fetching teachers: " . $e->getMessage() . "</p>";
}

// Test 4: Simulate chat API call
echo "<h2>Test 4: Chat API Simulation</h2>";
if (isset($_SESSION['user_id'])) {
    $current_user_id = $_SESSION['user_id'];
    
    // Test the chatbot.php logic
    $teacher_id = $current_user_id; // This is what chatbot.php does
    
    echo "<p>Current teacher_id (from session): " . $teacher_id . "</p>";
    
    // Test loading history
    try {
        $stmt = $pdo->prepare("SELECT message, response, created_at FROM chat_messages WHERE user_id = ? ORDER BY created_at ASC");
        $stmt->execute([$teacher_id]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Messages for this teacher: " . count($history) . "</p>";
        
        if ($history) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Message</th><th>Response</th><th>Time</th></tr>";
            foreach ($history as $msg) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars(substr($msg['message'], 0, 50)) . "...</td>";
                echo "<td>" . htmlspecialchars(substr($msg['response'], 0, 50)) . "...</td>";
                echo "<td>" . htmlspecialchars($msg['created_at']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p>Error loading history: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>No user logged in - cannot test chat functionality</p>";
}

// Test 5: Session isolation check
echo "<h2>Test 5: Session Isolation</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";
echo "<p><strong>Cookie Name:</strong> " . session_name() . "</p>";

// Test 6: Check for tab_id interference
echo "<h2>Test 6: Tab ID Check</h2>";
if (isset($_COOKIE['tab_id'])) {
    echo "<p><strong>⚠ Warning:</strong> tab_id cookie found: " . $_COOKIE['tab_id'] . "</p>";
    echo "<p>This might cause session issues. Consider clearing this cookie.</p>";
} else {
    echo "<p><strong>✓ No tab_id cookie found</strong></p>";
}

echo "<hr>";
echo "<h2>Recommendations:</h2>";
echo "<ol>";
echo "<li>Clear all cookies and browser cache</li>";
echo "<li>Test in incognito/private browsing mode</li>";
echo "<li>Log in as different teachers and check this page</li>";
echo "<li>Verify that session ID changes between different teacher logins</li>";
echo "</ol>";

echo "<p><a href='test_session_debug.php'>Go to Session Test Page</a></p>";
?>