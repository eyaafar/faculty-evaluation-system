<?php
session_start();
require_once 'includes/db.php';

echo "=== NOTIFICATION COUNT CHECK ===\n\n";

// Total notifications
$stmt = $pdo->query('SELECT COUNT(*) as total FROM notifications');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total notifications in DB: " . $result['total'] . "\n";

// Unread notifications by user
$stmt = $pdo->query('SELECT user_id, COUNT(*) as unread_count FROM notifications WHERE is_read = 0 GROUP BY user_id ORDER BY user_id');
$unread_by_user = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nUnread notifications by user:\n";
foreach ($unread_by_user as $row) {
    echo "  User ID " . $row['user_id'] . ": " . $row['unread_count'] . " unread\n";
}

// Sample unread notifications (first 5)
$stmt = $pdo->query('SELECT id, user_id, title, is_read, created_at FROM notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT 5');
$samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nSample unread notifications:\n";
if (count($samples) > 0) {
    foreach ($samples as $notif) {
        echo "  ID: " . $notif['id'] . ", User: " . $notif['user_id'] . ", Title: " . $notif['title'] . "\n";
    }
} else {
    echo "  No unread notifications found!\n";
}

// Check current user
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$uid]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nCurrent user (ID: $uid) unread count: " . $result['count'] . "\n";
} else {
    echo "\nNo active session\n";
}
?>
