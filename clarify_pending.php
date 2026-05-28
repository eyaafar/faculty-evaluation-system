<?php
require_once 'includes/db.php';

try {
    echo "=== PENDING EVALUATIONS ANALYSIS ===\n\n";
    
    // Get all student-subject pairings
    echo "STUDENT-SUBJECT ENROLLMENTS:\n";
    $stmt = $pdo->query("SELECT * FROM student_subjects");
    $enrollments = $stmt->fetchAll();
    echo "Total enrollments: " . count($enrollments) . "\n";
    foreach ($enrollments as $e) {
        echo "  - Student {$e['student_id']} -> Subject {$e['subject_id']}\n";
    }
    
    echo "\nEVALUATIONS SUBMITTED:\n";
    $stmt = $pdo->query("SELECT student_id, teacher_id, subject_id, evaluator_role, rating FROM evaluations");
    $evals = $stmt->fetchAll();
    echo "Total evaluations: " . count($evals) . "\n";
    foreach ($evals as $e) {
        echo "  - Student {$e['student_id']} -> Teacher {$e['teacher_id']} (Role: {$e['evaluator_role']}, Rating: {$e['rating']})\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "INTERPRETATION: If pending = total_enrollments - completed_evaluations:\n";
    echo "  Pending = " . count($enrollments) . " - " . count($evals) . " = " . (count($enrollments) - count($evals)) . "\n";
    
    echo "\nBUT WAIT - Do you want to include TEACHER evaluations too?\n";
    echo "Let me check if the system tracks teacher-to-teacher evaluations or teacher-to-student...\n";
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "CHECKING EVALUATOR_ROLE FIELD:\n";
    
    $stmt = $pdo->query("SELECT DISTINCT evaluator_role FROM evaluations");
    $roles = $stmt->fetchAll();
    echo "Evaluator roles in system: \n";
    foreach ($roles as $r) {
        echo "  - " . ($r['evaluator_role'] ?? 'NULL') . "\n";
    }
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM evaluations WHERE evaluator_role = 'teacher'");
    $teacher_evals = $stmt->fetch()['count'];
    echo "\nTeacher evaluations: " . $teacher_evals . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM evaluations WHERE evaluator_role = 'student'");
    $student_evals = $stmt->fetch()['count'];
    echo "Student evaluations: " . $student_evals . "\n";
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "QUESTION: Is 'pending' based on:\n";
    echo "  A) Student evaluations only? (" . (count($enrollments) - $student_evals) . " pending)\n";
    echo "  B) All evaluations (student + teacher)? (" . (count($enrollments) - count($evals)) . " pending)\n";
    echo "  C) Something else? (Need clarification)\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
