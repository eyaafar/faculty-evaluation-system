<?php
/**
 * Debug notification rendering issues
 */

require_once 'includes/db.php';

echo "=== NOTIFICATION RENDERING DEBUG ===\n\n";

// Test different user IDs
$test_users = [
    1 => 'Admin',
    2 => 'Student',
    3 => 'Teacher'
];

foreach ($test_users as $user_id => $role) {
    echo "═══ USER ID: $user_id ($role) ═══\n";
    
    // Check unread count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $count = (int) $stmt->fetchColumn();
    echo "Unread count: $count\n";
    
    // Check total notifications
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total = (int) $stmt->fetchColumn();
    echo "Total notifications: $total\n";
    
    // Get actual notifications
    $stmt = $pdo->prepare("SELECT id, title, message, notification_type, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$user_id]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Notifications list:\n";
    if (count($notes) > 0) {
        foreach ($notes as $n) {
            $status = $n['is_read'] ? "READ" : "UNREAD";
            echo "  ID:{$n['id']} [$status] Type: {$n['notification_type']}\n";
            echo "    Title: {$n['title']}\n";
            echo "    Created: {$n['created_at']}\n";
        }
    } else {
        echo "  (No notifications)\n";
    }
    
    // Simulate the topbar.php query
    echo "Query test (unread only):\n";
    $stmt = $pdo->prepare("SELECT title, message, created_at, notification_type FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 6");
    $stmt->execute([$user_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "  Found: " . count($result) . " unread notifications\n";
    
    if (count($result) > 0) {
        echo "  These SHOULD display (not fallback):\n";
        foreach ($result as $r) {
            echo "    - {$r['notification_type']}: {$r['title']}\n";
        }
    } else {
        echo "  Query returned empty - FALLBACK would show\n";
    }
    
    echo "\n";
}

echo "=== DATABASE CHECK ===\n";
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications");
$stmt->execute();
$total_all = (int) $stmt->fetchColumn();
echo "Total notifications in database: $total_all\n";

$stmt = $pdo->prepare("SELECT user_id, notification_type, COUNT(*) as cnt FROM notifications GROUP BY user_id, notification_type ORDER BY user_id");
$stmt->execute();
$grouping = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nGrouped by user and type:\n";
foreach ($grouping as $g) {
    echo "  User {$g['user_id']}: {$g['notification_type']} x {$g['cnt']}\n";
}

echo "\n=== CONCLUSION ===\n";
echo "If Student/Teacher have unread count > 0 but fallback still shows,\n";
echo "the issue is in topbar.php query or rendering logic.\n";
echo "If count = 0, the test notifications weren't created for these users.\n";
?>
