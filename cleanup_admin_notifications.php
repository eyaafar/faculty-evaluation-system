<?php
require_once __DIR__ . '/config/db.php';

echo "=== ADMIN NOTIFICATIONS AUDIT ===\n\n";

// Get all admin notifications
$stmt = $pdo->prepare('SELECT id, title, message, notification_type, is_read, created_at FROM notifications WHERE user_id = 1 ORDER BY created_at DESC');
$stmt->execute();
$notifs = $stmt->fetchAll();

echo "Total notifications for admin (user_id=1): " . count($notifs) . "\n\n";

$valid_types = ['low_completion_rate', 'system_error'];
$invalid_notifs = [];

foreach ($notifs as $n) {
    echo "ID: {$n['id']} | Type: {$n['notification_type']}\n";
    echo "  Title: {$n['title']}\n";
    echo "  Created: {$n['created_at']}\n";
    
    // Check if this notification type is valid for admin
    if (!in_array($n['notification_type'], $valid_types)) {
        echo "  ❌ INVALID FOR ADMIN - Should be deleted\n";
        $invalid_notifs[] = $n['id'];
    } else {
        echo "  ✓ Valid for admin\n";
    }
    echo "\n";
}

if (!empty($invalid_notifs)) {
    echo "\n=== CLEANING UP INVALID NOTIFICATIONS ===\n";
    echo "Deleting notification IDs: " . implode(', ', $invalid_notifs) . "\n";
    
    foreach ($invalid_notifs as $id) {
        $stmt = $pdo->prepare('DELETE FROM notifications WHERE id = ?');
        $stmt->execute([$id]);
        echo "  ✓ Deleted notification ID: $id\n";
    }
    
    // Also delete related SMS queue entries
    echo "\nCleaning up related SMS queue entries...\n";
    foreach ($invalid_notifs as $id) {
        $stmt = $pdo->prepare('DELETE FROM sms_queue WHERE notification_id = ?');
        $stmt->execute([$id]);
    }
    
    echo "\n✅ Cleanup complete!\n";
}

echo "\n=== VALID NOTIFICATION TYPES FOR EACH ROLE ===\n";
echo "STUDENTS should receive:\n";
echo "  • evaluation_assigned\n";
echo "  • deadline_approaching\n\n";

echo "TEACHERS should receive:\n";
echo "  • feedback_received\n";
echo "  • peer_review_request\n";
echo "  • peer_review_deadline\n";
echo "  • rating_summary_available\n\n";

echo "ADMINS should receive:\n";
echo "  • low_completion_rate\n";
echo "  • system_error\n";
?>
