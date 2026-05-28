<?php
// Simple browser-based session test
session_start();

echo "<h1>Browser Session Test</h1>";
echo "<h2>Current Session State:</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h2>Test Actions:</h2>";

// Simulate login as different teachers
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'login_dr_reyes':
            session_destroy();
            session_start();
            $_SESSION['user_id'] = 1; // Assuming Dr. Reyes has ID 1
            $_SESSION['username'] = 'dr_reyes';
            $_SESSION['name'] = 'Dr. Pedro Reyes';
            $_SESSION['role'] = 'teacher';
            echo "<p style='color: green;'>✓ Logged in as Dr. Pedro Reyes</p>";
            break;
            
        case 'login_mr_delacruz':
            session_destroy();
            session_start();
            $_SESSION['user_id'] = 2; // Assuming Mr. Dela Cruz has ID 2
            $_SESSION['username'] = 'juan.delacruz';
            $_SESSION['name'] = 'Mr. Juan Dela Cruz';
            $_SESSION['role'] = 'teacher';
            echo "<p style='color: green;'>✓ Logged in as Mr. Juan Dela Cruz</p>";
            break;
            
        case 'logout':
            session_destroy();
            session_start();
            echo "<p style='color: orange;'>✓ Logged out</p>";
            break;
            
        case 'test_chat':
            // Test the chat API directly
            $teacher_id = $_SESSION['user_id'] ?? 0;
            if ($teacher_id) {
                echo "<p>Testing chat API for user_id: $teacher_id</p>";
                
                // Save a test message
                require_once 'config/db.php';
                $stmt = $pdo->prepare("INSERT INTO chat_messages (user_id, message, response) VALUES (?, ?, ?)");
                $stmt->execute([$teacher_id, "Test message from " . $_SESSION['name'], "Test response"]);
                echo "<p style='color: green;'>✓ Test message saved</p>";
            } else {
                echo "<p style='color: red;'>✗ No user logged in</p>";
            }
            break;
            
        case 'check_history':
            // Check chat history
            $teacher_id = $_SESSION['user_id'] ?? 0;
            if ($teacher_id) {
                require_once 'config/db.php';
                $stmt = $pdo->prepare("SELECT message, response, created_at FROM chat_messages WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                $stmt->execute([$teacher_id]);
                $messages = $stmt->fetchAll();
                
                echo "<h3>Chat History for " . $_SESSION['name'] . " (ID: $teacher_id):</h3>";
                if (count($messages) > 0) {
                    echo "<table border='1' cellpadding='5'>";
                    echo "<tr><th>Message</th><th>Response</th><th>Time</th></tr>";
                    foreach ($messages as $msg) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($msg['message']) . "</td>";
                        echo "<td>" . htmlspecialchars($msg['response']) . "</td>";
                        echo "<td>" . $msg['created_at'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No messages found</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ No user logged in</p>";
            }
            break;
    }
    
    echo "<hr>";
}

echo "<div style='margin: 20px 0;'>";
echo "<a href='?action=login_dr_reyes' style='margin: 5px; padding: 10px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Login as Dr. Reyes</a>";
echo "<a href='?action=login_mr_delacruz' style='margin: 5px; padding: 10px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Login as Mr. Dela Cruz</a>";
echo "<a href='?action=logout' style='margin: 5px; padding: 10px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px;'>Logout</a>";
echo "<a href='?action=test_chat' style='margin: 5px; padding: 10px; background: #ffc107; color: black; text-decoration: none; border-radius: 5px;'>Test Chat</a>";
echo "<a href='?action=check_history' style='margin: 5px; padding: 10px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px;'>Check History</a>";
echo "</div>";

echo "<h2>Test Sequence:</h2>";
echo "<ol>";
echo "<li>Click 'Login as Dr. Reyes'</li>";
echo "<li>Click 'Test Chat' to save a message</li>";
echo "<li>Click 'Check History' to verify message</li>";
echo "<li>Click 'Logout'</li>";
echo "<li>Click 'Login as Mr. Dela Cruz'</li>";
echo "<li>Click 'Check History' - should show NO messages from Dr. Reyes</li>";
echo "<li>Click 'Test Chat' to save a new message</li>";
echo "<li>Click 'Check History' - should show only Mr. Dela Cruz's messages</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='database_analysis.php'>View Database Analysis</a></p>";
echo "<p><a href='check_error_log.php'>Check Error Log</a></p>";
?>