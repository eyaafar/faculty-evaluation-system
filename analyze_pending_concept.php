<?php
require_once 'includes/db.php';

try {
    echo "=== INVESTIGATING PENDING EVALUATIONS CONCEPT ===\n\n";
    
    // Check evaluations table structure
    echo "1. EVALUATIONS TABLE STRUCTURE:\n";
    $stmt = $pdo->query("DESCRIBE evaluations");
    $cols = $stmt->fetchAll();
    foreach ($cols as $c) {
        echo "   {$c['Field']} - {$c['Type']}\n";
    }
    
    echo "\n2. CHECK IF THERE'S A 'STATUS' OR 'COMPLETED' FIELD:\n";
    $has_status = false;
    foreach ($cols as $c) {
        if (stripos($c['Field'], 'status') !== false || 
            stripos($c['Field'], 'completed') !== false || 
            stripos($c['Field'], 'submitted') !== false) {
            echo "   Found: " . $c['Field'] . "\n";
            $has_status = true;
        }
    }
    if (!$has_status) {
        echo "   No status field found\n";
    }
    
    echo "\n3. ALL EVALUATIONS IN DATABASE:\n";
    $stmt = $pdo->query("SELECT * FROM evaluations");
    $all_evals = $stmt->fetchAll();
    echo "Total count: " . count($all_evals) . "\n";
    foreach ($all_evals as $e) {
        echo "\n   Eval ID: {$e['id']}\n";
        echo "   Student: {$e['student_id']}, Teacher: {$e['teacher_id']}, Subject: {$e['subject_id']}\n";
        echo "   Evaluator Role: {$e['evaluator_role']}, Evaluator ID: " . ($e['evaluator_id'] ?? 'NULL') . "\n";
        echo "   Date Submitted: {$e['date_submitted']}, Rating: " . ($e['rating'] ?? 'NULL') . "\n";
    }
    
    echo "\n4. WHAT COULD 'PENDING' MEAN?\n";
    echo "   Option A: Evaluations that should exist but don't (potential - actual)\n";
    echo "   Option B: Evaluations with NULL/empty values (incomplete)\n";
    echo "   Option C: Evaluations from teachers that haven't been submitted yet\n";
    echo "   Option D: A specific 'pending' status field in the database\n";
    
    echo "\n5. CHECKING FOR PENDING PATTERN:\n";
    
    // Check if there's a relation between teachers and students that haven't been evaluated
    echo "\n   Teachers enrolled in subjects:\n";
    $stmt = $pdo->query("SELECT DISTINCT t.id, t.name FROM users t WHERE t.role = 'teacher'");
    $teachers = $stmt->fetchAll();
    foreach ($teachers as $t) {
        echo "   - {$t['name']} (ID: {$t['id']})\n";
    }
    
    echo "\n   Students enrolled in subjects:\n";
    $stmt = $pdo->query("SELECT DISTINCT s.id, s.name FROM users s WHERE s.role = 'student'");
    $students = $stmt->fetchAll();
    foreach ($students as $s) {
        echo "   - {$s['name']} (ID: {$s['id']})\n";
    }
    
    echo "\n   Student-Subject enrollments:\n";
    $stmt = $pdo->query("SELECT ss.*, sub.name as subject_name FROM student_subjects ss JOIN subjects sub ON ss.subject_id = sub.id");
    $enrollments = $stmt->fetchAll();
    foreach ($enrollments as $e) {
        echo "   - Student {$e['student_id']} -> Subject {$e['subject_name']}\n";
    }
    
    echo "\n   Evaluations submitted:\n";
    $stmt = $pdo->query("SELECT e.id, e.student_id, e.teacher_id, u.name as teacher_name, e.rating FROM evaluations e JOIN users u ON e.teacher_id = u.id");
    $evals = $stmt->fetchAll();
    if (!empty($evals)) {
        foreach ($evals as $e) {
            echo "   - Student {$e['student_id']} evaluated {$e['teacher_name']} (Rating: {$e['rating']})\n";
        }
    } else {
        echo "   - None\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
