<?php
require_once 'includes/db.php';

try {
    echo "=== CHECKING TABLE STRUCTURE ===\n\n";
    
    // Check student_subjects table
    $stmt = $pdo->query("DESCRIBE student_subjects");
    echo "student_subjects columns:\n";
    $cols = $stmt->fetchAll();
    foreach ($cols as $c) {
        echo "  - {$c['Field']}\n";
    }
    
    echo "\nSample student_subjects data:\n";
    $stmt = $pdo->query("SELECT * FROM student_subjects LIMIT 3");
    $data = $stmt->fetchAll();
    foreach ($data as $row) {
        print_r($row);
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "BETTER CALCULATION:\n\n";
    
    // Count potential evaluations (each student-subject pairing could need an eval)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM student_subjects");
    $potential = $stmt->fetch()['total'];
    echo "Potential evaluations needed (student_subjects count): " . $potential . "\n";
    
    // Count completed evaluations
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM evaluations");
    $completed = $stmt->fetch()['total'];
    echo "Completed evaluations: " . $completed . "\n";
    
    $pending = max(0, $potential - $completed);
    echo "Real pending: " . $pending . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
