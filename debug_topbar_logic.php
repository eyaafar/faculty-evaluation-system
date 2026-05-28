<?php
/**
 * Debug topbar.php notification logic
 * Simulates what happens when topbar.php runs
 */

// Start session
if (!isset($_SESSION)) {
    @session_start();
}

// Simulate logging in as student (ID 2)
$_SESSION['user_id'] = 2;
$_SESSION['name'] = 'Mukramin';
$_SESSION['role'] = 'student';

require_once 'includes/db.php';

// Simulate what topbar.php does
echo "=== SIMULATING TOPBAR.PHP LOGIC ===\n\n";

$user_name = $_SESSION['name'] ?? 'Admin';
$user_id   = $_SESSION['user_id'] ?? 0;
$_topbar_role = $_SESSION['role'] ?? 'admin';

echo "User ID: $user_id\n";
echo "User Name: $user_name\n";
echo "Role: $_topbar_role\n\n";

// Count unread
echo "=== CHECKING UNREAD COUNT ===\n";
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $unread_count = (int) $stmt->fetchColumn();
    echo "Unread count query executed\n";
    echo "Result: $unread_count\n";
} catch (Exception $e) {
    echo "ERROR in count query: " . $e->getMessage() . "\n";
    $unread_count = 0;
}

// Show/hide badge
$show_badge = $unread_count > 0;
$badge_text = $unread_count > 99 ? '99+' : (string) $unread_count;
echo "Badge should show: " . ($show_badge ? "YES" : "NO") . "\n";
echo "Badge text: $badge_text\n\n";

// Get notifications
echo "=== CHECKING NOTIFICATION LIST ===\n";
$loaded = false;
if (isset($pdo)) {
    echo "PDO is set: OK\n";
    try {
        echo "Executing query for user_id = $user_id\n";
        $stmt = $pdo->prepare("SELECT title, message, created_at, notification_type FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 6");
        $stmt->execute([$user_id]);
        $notes = $stmt->fetchAll();
        echo "Query executed\n";
        echo "Rows returned: " . count($notes) . "\n";
        
        if ($notes && count($notes) > 0) {
            $loaded = true;
            echo "Setting loaded = TRUE\n";
            foreach ($notes as $n) {
                echo "  - {$n['notification_type']}: {$n['title']}\n";
            }
        } else {
            echo "No rows or empty result\n";
        }
    } catch (Exception $e) {
        echo "ERROR in notification query: " . $e->getMessage() . "\n";
        $loaded = false;
    }
} else {
    echo "PDO is NOT set!\n";
}

echo "\n=== FALLBACK LOGIC ===\n";
echo "loaded: " . ($loaded ? "true" : "false") . "\n";
echo "unread_count: $unread_count\n";
echo "Condition: if (!loaded && unread_count === 0)\n";

if (!$loaded && $unread_count === 0) {
    echo "Result: SHOW FALLBACK (Welcome back message)\n";
} else {
    echo "Result: HIDE FALLBACK (show real notifications)\n";
}

echo "\n=== SUMMARY ===\n";
if (!$loaded && $unread_count > 0) {
    echo "⚠️ PROBLEM: Loaded is false but unread_count > 0\n";
    echo "   The notification list query returned nothing!\n";
    echo "   Check database connection or query syntax.\n";
} elseif (!$loaded && $unread_count === 0) {
    echo "✓ OK: Showing fallback (no notifications)\n";
} elseif ($loaded && $unread_count > 0) {
    echo "✓ OK: Real notifications showing\n";
} else {
    echo "? Unexpected state\n";
}
?>
