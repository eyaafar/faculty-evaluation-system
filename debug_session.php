<?php
// Simple debugger to check session and chat data

// Start a standard session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>Session Debug Information</h1>";
echo "<h2>Session Data:</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

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
    echo "<h2>Database Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    exit;
}

if (!isset($pdo)) {
    echo "<h2>Database Error:</h2>";
    echo "<p>Database object not initialized</p>";
    exit;
}

echo "<h2>Chat Messages in Database:</h2>";
try {
    $stmt = $pdo->query("SELECT id, user_id, message, response, created_at FROM chat_messages ORDER BY created_at DESC LIMIT 20");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($messages) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>ID</th><th>User ID</th><th>Message</th><th>Response</th><th>Created At</th></tr>";
        foreach ($messages as $msg) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($msg['id']) . "</td>";
            echo "<td>" . htmlspecialchars($msg['user_id']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($msg['message'], 0, 50)) . "...</td>";
            echo "<td>" . htmlspecialchars(substr($msg['response'], 0, 50)) . "...</td>";
            echo "<td>" . htmlspecialchars($msg['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No chat messages found in the database.</p>";
    }
} catch (Exception $e) {
    echo "<p>Error fetching chat messages: " . $e->getMessage() . "</p>";
}

echo "<h2>Teachers in Database:</h2>";
try {
    $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'teacher' ORDER BY name");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($teachers) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>ID</th><th>Name</th></tr>";
        foreach ($teachers as $teacher) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($teacher['id']) . "</td>";
            echo "<td>" . htmlspecialchars($teacher['name']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No teachers found in the database.</p>";
    }
} catch (Exception $e) {
    echo "<p>Error fetching teachers: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Session ID:</strong> " . session_id() . "</p>";
?>