<?php
/**
 * Generate notifications for students based on their pending evaluations
 * This script creates evaluation_assigned notifications for students with pending evaluations
 */

require_once 'includes/db.php';

echo "=== GENERATING STUDENT EVALUATION NOTIFICATIONS ===\n\n";

try {
    // Get all students
    $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'student' ORDER BY id");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $created_count = 0;
    $skipped_count = 0;
    
    foreach ($students as $student) {
        $student_id = $student['id'];
        $student_name = $student['name'];
        
        // Check if student already has a recent notification
        $check_stmt = $pdo->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE user_id = ? 
            AND notification_type = 'evaluation_assigned'
            AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $check_stmt->execute([$student_id]);
        $recent_notif_count = (int)$check_stmt->fetchColumn();
        
        if ($recent_notif_count > 0) {
            $skipped_count++;
            continue;
        }
        
        // Get student's pending evaluations
        $eval_stmt = $pdo->prepare("
            SELECT ca.id, s.subject_name, ca.subject_id, ca.teacher_id, u.name AS teacher_name, u.first_name
            FROM   class_assignments ca
            JOIN   subjects s  ON ca.subject_id  = s.subject_id
            JOIN   users    u  ON ca.teacher_id  = u.id
            LEFT JOIN evaluations e
                   ON e.student_id  = ? AND e.teacher_id = ca.teacher_id AND e.subject_id = ca.subject_id
            WHERE  e.id IS NULL
            LIMIT 1
        ");
        $eval_stmt->execute([$student_id]);
        $pending = $eval_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pending) {
            // Create notification for this student
            $title = "Evaluate " . htmlspecialchars($pending['teacher_name']);
            $message = "You have a pending evaluation for " . htmlspecialchars($pending['subject_name']);
            
            $notif_stmt = $pdo->prepare("
                INSERT INTO notifications 
                (user_id, title, message, notification_type, is_read, created_at) 
                VALUES (?, ?, ?, 'evaluation_assigned', 0, NOW())
            ");
            $result = $notif_stmt->execute([$student_id, $title, $message]);
            
            if ($result) {
                $created_count++;
                echo "✓ Created notification for {$student_name} (ID: {$student_id})\n";
                echo "  Teacher: {$pending['teacher_name']}, Subject: {$pending['subject_name']}\n";
            }
        }
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "Created: {$created_count} notifications\n";
    echo "Skipped: {$skipped_count} students (already have recent notifications)\n";
    echo "Total processed: " . count($students) . " students\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
