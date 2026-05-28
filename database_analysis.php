<?php
// Direct database test to check chat message isolation
require_once 'config/db.php';

echo "<h1>Database Chat Message Analysis</h1>";

try {
    // Get all teachers
    $stmt = $pdo->query("SELECT id, name, username FROM users WHERE role = 'teacher' ORDER BY id");
    $teachers = $stmt->fetchAll();
    
    echo "<h2>Available Teachers:</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Username</th><th>Chat Messages</th></tr>";
    
    foreach ($teachers as $teacher) {
        // Count messages for this teacher
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM chat_messages WHERE user_id = ?");
        $stmt->execute([$teacher['id']]);
        $result = $stmt->fetch();
        $message_count = $result['count'];
        
        echo "<tr>";
        echo "<td>" . $teacher['id'] . "</td>";
        echo "<td>" . htmlspecialchars($teacher['name']) . "</td>";
        echo "<td>" . htmlspecialchars($teacher['username']) . "</td>";
        echo "<td>" . $message_count . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Show actual messages if there are any
    echo "<h2>Recent Chat Messages:</h2>";
    $stmt = $pdo->query("
        SELECT cm.*, u.name as teacher_name, u.username 
        FROM chat_messages cm 
        JOIN users u ON cm.user_id = u.id 
        ORDER BY cm.created_at DESC 
        LIMIT 10
    ");
    $messages = $stmt->fetchAll();
    
    if (count($messages) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Teacher</th><th>Message</th><th>Response</th><th>Time</th></tr>";
        
        foreach ($messages as $msg) {
            echo "<tr>";
            echo "<td>" . $msg['id'] . "</td>";
            echo "<td>" . htmlspecialchars($msg['teacher_name']) . " (" . $msg['user_id'] . ")</td>";
            echo "<td>" . htmlspecialchars(substr($msg['message'], 0, 50)) . "...</td>";
            echo "<td>" . htmlspecialchars(substr($msg['response'], 0, 50)) . "...</td>";
            echo "<td>" . $msg['created_at'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No chat messages found in database.</p>";
    }
    
    // Check for potential issues
    echo "<h2>Potential Issues Check:</h2>";
    
    // Check if there are messages with user_id 0
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chat_messages WHERE user_id = 0");
    $result = $stmt->fetch();
    if ($result['count'] > 0) {
        echo "<p style='color: red;'>⚠️ Found " . $result['count'] . " messages with user_id = 0 (no user)</p>";
    }
    
    // Check if there are messages with invalid user_ids
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chat_messages WHERE user_id NOT IN (SELECT id FROM users)");
    $result = $stmt->fetch();
    if ($result['count'] > 0) {
        echo "<p style='color: red;'>⚠️ Found " . $result['count'] . " messages with invalid user_ids</p>";
    }
    
    // Check session table structure
    echo "<h2>Database Structure Check:</h2>";
    $stmt = $pdo->query("DESCRIBE chat_messages");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . $col['Default'] . "</td>";
        echo "<td>" . $col['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='check_error_log.php'>Check Error Log</a></p>";
echo "<p><a href='check_session.php'>Check Current Session</a></p>";
?>