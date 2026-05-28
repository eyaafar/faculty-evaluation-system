<?php
require_once 'includes/db.php';

try {
    echo "=== CLEANING UP ADMIN NOTIFICATIONS ===\n\n";
    
    // Get admin ID
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $admin_id = $stmt->fetch()['id'];
    
    // Delete non-admin notifications from admin user
    $stmt = $pdo->prepare("
        DELETE FROM notifications 
        WHERE user_id = ? 
        AND notification_type IN ('evaluation_assigned', 'deadline_approaching')
    ");
    $deleted = $stmt->execute([$admin_id]);
    $rows_affected = $stmt->rowCount();
    
    echo "Removed " . $rows_affected . " test notifications from admin that should belong to teachers/students\n\n";
    
    // Show what admin notifications remain
    $stmt = $pdo->prepare("
        SELECT notification_type, COUNT(*) as count 
        FROM notifications 
        WHERE user_id = ? 
        GROUP BY notification_type
    ");
    $stmt->execute([$admin_id]);
    $remaining = $stmt->fetchAll();
    
    echo "Admin notifications remaining:\n";
    if (!empty($remaining)) {
        foreach ($remaining as $r) {
            echo "  - " . $r['notification_type'] . ": " . $r['count'] . "\n";
        }
    } else {
        echo "  None (this is correct - admin should only see admin-level alerts)\n";
    }
    
    echo "\n✓ Admin notifications are now filtered to show only admin-relevant alerts\n";
    echo "✓ Student/teacher evaluations will no longer appear in admin inbox\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
