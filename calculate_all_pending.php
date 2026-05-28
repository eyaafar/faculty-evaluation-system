<?php
require_once 'includes/db.php';

try {
    echo "=== PENDING EVALUATIONS: STUDENTS + TEACHERS ===\n\n";
    
    // Part 1: Student evaluations
    echo "PART 1: STUDENT EVALUATIONS (Students → Teachers)\n";
    echo str_repeat("-", 80) . "\n";
    
    // Get all student-subject enrollments (potential student evaluations)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM student_subjects");
    $potential_student_evals = $stmt->fetch()['count'];
    echo "Potential student evaluations needed: " . $potential_student_evals . "\n";
    
    // Get completed student evaluations
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM evaluations WHERE evaluator_role = 'student'");
    $completed_student_evals = $stmt->fetch()['count'];
    echo "Completed student evaluations: " . $completed_student_evals . "\n";
    
    $pending_student_evals = max(0, $potential_student_evals - $completed_student_evals);
    echo "Pending student evaluations: " . $pending_student_evals . "\n";
    
    // Part 2: Teacher evaluations (co-teachers)
    echo "\n\nPART 2: TEACHER EVALUATIONS (Teachers → Co-Teachers)\n";
    echo str_repeat("-", 80) . "\n";
    
    // Get all teachers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'teacher'");
    $total_teachers = $stmt->fetch()['count'];
    echo "Total teachers: " . $total_teachers . "\n";
    
    // Assuming each teacher should evaluate other teachers (excluding self)
    // Potential teacher evals = teachers * (teachers - 1) for each teacher evaluating all others
    // OR just teachers * teachers if some evaluate all
    $potential_teacher_evals = $total_teachers * ($total_teachers - 1); // Each teacher evaluates others
    echo "Potential teacher evaluations (each teacher → all others): " . $potential_teacher_evals . "\n";
    
    // Get completed teacher evaluations
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM evaluations WHERE evaluator_role = 'teacher'");
    $completed_teacher_evals = $stmt->fetch()['count'];
    echo "Completed teacher evaluations: " . $completed_teacher_evals . "\n";
    
    $pending_teacher_evals = max(0, $potential_teacher_evals - $completed_teacher_evals);
    echo "Pending teacher evaluations: " . $pending_teacher_evals . "\n";
    
    // Total
    echo "\n\n" . str_repeat("=", 80) . "\n";
    echo "TOTAL PENDING EVALUATIONS (COMBINED):\n";
    echo "  Student evaluations pending: " . $pending_student_evals . "\n";
    echo "  Teacher evaluations pending: " . $pending_teacher_evals . "\n";
    echo "  TOTAL PENDING: " . ($pending_student_evals + $pending_teacher_evals) . "\n";
    
    echo "\nBREAKDOWN:\n";
    echo "  Potential evaluations = {$potential_student_evals} (student) + {$potential_teacher_evals} (teacher) = " . ($potential_student_evals + $potential_teacher_evals) . "\n";
    echo "  Completed evaluations = {$completed_student_evals} (student) + {$completed_teacher_evals} (teacher) = " . ($completed_student_evals + $completed_teacher_evals) . "\n";
    echo "  Pending = " . ($potential_student_evals + $potential_teacher_evals) . " - " . ($completed_student_evals + $completed_teacher_evals) . " = " . ($pending_student_evals + $pending_teacher_evals) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
