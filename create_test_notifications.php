<?php
/**
 * Create test notifications to verify the improved display
 */

require_once 'includes/db.php';
require_once 'notifications/notification_factory.php';

echo "=== CREATING TEST NOTIFICATIONS ===\n\n";

$notificationFactory = new NotificationFactory($pdo);

// Create test notifications for different users and types
$tests = [
    // Student notifications
    [
        'user_id' => 2,
        'type' => 'evaluation_assigned',
        'title' => 'New Evaluation Assigned',
        'message' => 'You have been assigned to evaluate Dr. Johnson for Physics 101',
        'label' => 'Student Test'
    ],
    [
        'user_id' => 2,
        'type' => 'deadline_approaching',
        'title' => 'Evaluation Deadline Approaching',
        'message' => 'Complete your evaluation for Dr. Smith in 24 hours',
        'label' => 'Student Test'
    ],
    // Teacher notifications
    [
        'user_id' => 3,
        'type' => 'feedback_received',
        'title' => 'New Student Feedback',
        'message' => '2 new feedback submissions received from your students',
        'label' => 'Teacher Test'
    ],
    [
        'user_id' => 3,
        'type' => 'rating_summary_available',
        'title' => 'Rating Summary Available',
        'message' => 'Your evaluation summary for Spring 2026 is now available',
        'label' => 'Teacher Test'
    ],
    // Admin notifications
    [
        'user_id' => 1,
        'type' => 'low_completion_rate',
        'title' => 'Low Completion Alert',
        'message' => 'Only 45% of students have completed their evaluations',
        'label' => 'Admin Test'
    ],
];

foreach ($tests as $test) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications 
            (user_id, title, message, notification_type, is_read, created_at) 
            VALUES (?, ?, ?, ?, 0, NOW())
        ");
        $result = $stmt->execute([
            $test['user_id'],
            $test['title'],
            $test['message'],
            $test['type']
        ]);
        
        if ($result) {
            $id = $pdo->lastInsertId();
            echo "✓ Created {$test['label']} notification:\n";
            echo "  ID: $id | Type: {$test['type']}\n";
            echo "  Title: {$test['title']}\n";
            echo "  User: {$test['user_id']}\n\n";
        }
    } catch (Exception $e) {
        echo "✗ Failed to create notification: " . $e->getMessage() . "\n\n";
    }
}

// Verify creation
echo "=== VERIFICATION ===\n\n";

// Student count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = 2 AND is_read = 0");
$stmt->execute();
$student_count = (int) $stmt->fetchColumn();
echo "✓ Student (user_id=2) unread: $student_count\n";

// Teacher count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = 3 AND is_read = 0");
$stmt->execute();
$teacher_count = (int) $stmt->fetchColumn();
echo "✓ Teacher (user_id=3) unread: $teacher_count\n";

// Admin count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = 1 AND is_read = 0");
$stmt->execute();
$admin_count = (int) $stmt->fetchColumn();
echo "✓ Admin (user_id=1) unread: $admin_count\n\n";

echo "=== WHAT TO TEST ===\n";
echo "1. Log in as STUDENT (user_id=2)\n";
echo "   - Bell icon should show badge with '2'\n";
echo "   - Notifications should show with icons:\n";
echo "     • Assignment icon (blue) - 'New Evaluation Assigned'\n";
echo "     • Deadline icon (orange) - 'Evaluation Deadline Approaching'\n";
echo "   - Clicking should mark as read and count should decrease\n\n";

echo "2. Log in as TEACHER (user_id=3)\n";
echo "   - Bell icon should show badge with '2'\n";
echo "   - Notifications should show with icons:\n";
echo "     • Comment icon (green) - 'New Student Feedback'\n";
echo "     • Chart icon (purple) - 'Rating Summary Available'\n\n";

echo "3. Log in as ADMIN (user_id=1)\n";
echo "   - Bell icon should show badge with '1'\n";
echo "   - Should show Alert icon (red) - 'Low Completion Alert'\n\n";

echo "4. Each notification should have:\n";
echo "   ✓ Type label (ASSIGNMENT, DEADLINE, FEEDBACK, etc.)\n";
echo "   ✓ Colored icon box\n";
echo "   ✓ Title and message\n";
echo "   ✓ Time stamp\n";
echo "   ✓ Hover effect highlighting\n";
?>
