<?php
include 'includes/tab_session.php';
require_once 'config/db.php';

echo "<pre>";
echo "=== CLEANUP SCRIPT: DELETE DR. PEDRO FEEDBACK DATA ===\n\n";

try {
    // Find Dr. Pedro's ID
    $stmt = $pdo->query("SELECT id, name FROM users WHERE name LIKE '%Pedro%' OR username = 'preyes'");
    $pedro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedro) {
        echo "ERROR: Dr. Pedro not found in database\n";
        exit;
    }
    
    $pedro_id = $pedro['id'];
    echo "Found Dr. Pedro Reyes (ID: $pedro_id)\n\n";
    
    // Count evaluations where Dr. Pedro is the teacher being evaluated
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM evaluations WHERE teacher_id = ?");
    $stmt->execute([$pedro_id]);
    $count = $stmt->fetchColumn();
    echo "Evaluations where Dr. Pedro is teacher: $count\n";
    
    // Show what will be deleted
    $stmt = $pdo->prepare("
        SELECT e.evaluation_id, e.evaluator_id, u.name as evaluator_name, e.evaluator_role, e.feedback, e.date_submitted
        FROM evaluations e
        LEFT JOIN users u ON u.id = e.evaluator_id
        WHERE e.teacher_id = ?
    ");
    $stmt->execute([$pedro_id]);
    $evals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($count > 0) {
        echo "\nData to be deleted:\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($evals as $e) {
            echo "ID: {$e['evaluation_id']}, Role: {$e['evaluator_role']}, Evaluator: {$e['evaluator_name']}\n";
            echo "Feedback: {$e['feedback']}\n";
            echo "Date: {$e['date_submitted']}\n";
            echo str_repeat("-", 80) . "\n";
        }
    }
    
    // Delete the evaluations
    if ($count > 0) {
        $stmt = $pdo->prepare("DELETE FROM evaluations WHERE teacher_id = ?");
        $stmt->execute([$pedro_id]);
        $deleted = $stmt->rowCount();
        echo "\n✓ DELETED $deleted evaluation records\n";
    } else {
        echo "\nNo evaluations to delete\n";
    }
    
    // Verify deletion
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM evaluations WHERE teacher_id = ?");
    $stmt->execute([$pedro_id]);
    $verify = $stmt->fetchColumn();
    echo "\nVerification - Evaluations remaining for Dr. Pedro: $verify\n";
    
    if ($verify === 0) {
        echo "\n✓ SUCCESS: All Dr. Pedro feedback data has been deleted\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== END OF CLEANUP ===\n";
echo "</pre>";
?>
