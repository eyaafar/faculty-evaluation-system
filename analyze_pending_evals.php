<?php
require_once 'includes/db.php';

try {
    echo "=== PENDING EVALUATIONS ANALYSIS ===\n\n";
    
    // Method 1: Total enrollments vs completed evaluations
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM student_subjects");
    $total_enrollments = $stmt->fetch()['total'];
    echo "Total student-subject enrollments (potential evaluations): " . $total_enrollments . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM evaluations");
    $completed_evals = $stmt->fetch()['total'];
    echo "Completed evaluations: " . $completed_evals . "\n";
    
    $real_pending = max(0, $total_enrollments - $completed_evals);
    echo "Real pending evaluations: " . $real_pending . "\n";
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "CURRENT WRONG CALCULATION:\n";
    echo "  pending_evals = max(0, 50 - {$completed_evals}) = " . max(0, 50 - $completed_evals) . "\n";
    
    echo "\nCORRECT CALCULATION SHOULD BE:\n";
    echo "  pending_evals = max(0, {$total_enrollments} - {$completed_evals}) = " . $real_pending . "\n";
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "BREAKDOWN:\n";
    
    // Show pending by teacher
    $stmt = $pdo->query("
        SELECT 
            t.id, 
            t.name, 
            COUNT(ss.id) as enrollments,
            COALESCE(COUNT(e.id), 0) as evaluated
        FROM users t
        LEFT JOIN student_subjects ss ON ss.teacher_id = t.id
        LEFT JOIN evaluations e ON e.teacher_id = t.id
        WHERE t.role = 'teacher'
        GROUP BY t.id, t.name
        ORDER BY t.name
    ");
    $teachers = $stmt->fetchAll();
    
    foreach ($teachers as $t) {
        $pending_for_teacher = $t['enrollments'] - $t['evaluated'];
        echo "{$t['name']}: {$t['enrollments']} enrollments, {$t['evaluated']} evaluated, " . 
             max(0, $pending_for_teacher) . " pending\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
