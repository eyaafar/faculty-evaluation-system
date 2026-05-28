<?php
require_once 'includes/db.php';

try {
    echo "=== ADMIN NOTIFICATIONS ANALYSIS ===\n\n";
    
    // Get admin user ID
    $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch();
    
    if (!$admin) {
        echo "No admin found!\n";
        exit;
    }
    
    echo "Admin User: " . $admin['name'] . " (ID: " . $admin['id'] . ")\n\n";
    
    // Get all notifications for admin
    echo "NOTIFICATIONS FOR ADMIN (User ID: " . $admin['id'] . "):\n";
    echo str_repeat("=", 80) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT id, title, message, notification_type, is_read, created_at 
        FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$admin['id']]);
    $admin_notifs = $stmt->fetchAll();
    
    echo "Total unread for admin: " . count(array_filter($admin_notifs, fn($n) => !$n['is_read'])) . "\n";
    echo "Total notifications: " . count($admin_notifs) . "\n\n";
    
    foreach ($admin_notifs as $n) {
        echo "- Type: {$n['notification_type']}\n";
        echo "  Title: {$n['title']}\n";
        echo "  Message: " . substr($n['message'], 0, 60) . "...\n";
        echo "  Read: " . ($n['is_read'] ? 'Yes' : 'No') . "\n";
        echo "  Date: {$n['created_at']}\n\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "PROBLEM IDENTIFIED:\n";
    echo "Admin is receiving student/teacher notifications!\n\n";
    
    echo "WHERE SHOULD THESE NOTIFICATIONS GO?\n";
    echo "- 'New Evaluation Assigned' → Should go to the TEACHER or STUDENT, not ADMIN\n";
    echo "- 'Deadline Approaching' → Should go to the EVALUATOR (teacher/student), not ADMIN\n\n";
    
    echo "QUESTION:\n";
    echo "Should the admin see:\n";
    echo "  A) ONLY admin-level notifications (low completion rate, system errors, etc)?\n";
    echo "  B) A summary of all activity from all users?\n";
    echo "  C) Filtered notifications from specific roles?\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
