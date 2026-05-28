<?php
include 'includes/tab_session.php';
require_once 'config/db.php';

echo "<pre>";
echo "=== DETAILED DIAGNOSTIC: DR. PEDRO FEEDBACK vs FARHIYA PENDING ===\n\n";

try {
    // Get user IDs
    $stmt = $pdo->query("SELECT id, name FROM users WHERE name LIKE '%Pedro%' OR username = 'preyes'");
    $pedro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT id, name FROM users WHERE name LIKE '%Farhiya%' OR username = 'farhiya'");
    $farhiya = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedro || !$farhiya) {
        echo "ERROR: Users not found\n";
        exit;
    }
    
    $pedro_id = $pedro['id'];
    $farhiya_id = $farhiya['id'];
    
    echo "Users:\n";
    echo "  Dr. Pedro ID: $pedro_id\n";
    echo "  Farhiya ID: $farhiya_id\n\n";
    
    // 1. Show ALL evaluations in the system for Dr. Pedro (as teacher being evaluated)
    echo "1. EVALUATIONS WHERE DR. PEDRO IS TEACHER (showing in his feedback.php):\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            e.evaluation_id,
            e.teacher_id,
            e.evaluator_id,
            u_eval.name as evaluator_name,
            e.evaluator_role,
            e.subject_id,
            e.rating,
            e.feedback,
            e.date_submitted
        FROM evaluations e
        LEFT JOIN users u_eval ON u_eval.id = e.evaluator_id
        WHERE e.teacher_id = ?
        ORDER BY e.date_submitted DESC
    ");
    $stmt->execute([$pedro_id]);
    $pedro_evals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Count: " . count($pedro_evals) . "\n";
    if ($pedro_evals) {
        foreach ($pedro_evals as $e) {
            echo "  ID: {$e['evaluation_id']} | Evaluator: {$e['evaluator_name']} | Role: {$e['evaluator_role']} | Rating: {$e['rating']}\n";
            echo "    Feedback: " . substr($e['feedback'], 0, 80) . "...\n";
        }
    } else {
        echo "  ✓ No evaluations found\n";
    }
    echo "\n";
    
    // 2. Check if Farhiya has already evaluated Dr. Pedro
    echo "2. HAS FARHIYA ALREADY EVALUATED DR. PEDRO?\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM evaluations 
        WHERE teacher_id = ? AND evaluator_id = ?
    ");
    $stmt->execute([$pedro_id, $farhiya_id]);
    $farhiya_eval_pedro = $stmt->fetchColumn();
    
    echo "Result: ";
    if ($farhiya_eval_pedro > 0) {
        echo "YES - Farhiya has already evaluated Dr. Pedro ($farhiya_eval_pedro records)\n";
        echo "  ⚠️ This is the problem! If she hasn't completed the form, data shouldn't exist.\n";
    } else {
        echo "NO - Farhiya has NOT evaluated Dr. Pedro\n";
        echo "  ✓ Correct state\n";
    }
    echo "\n";
    
    // 3. Check what shows in Farhiya's pending evaluations
    echo "3. FARHIYA'S PENDING EVALUATIONS (from evaluate.php query):\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT ca.subject_id, s.subject_name, u.id as teacher_id, u.name as teacher_name
        FROM class_assignments ca
        JOIN subjects s ON ca.subject_id = s.subject_id
        JOIN users u ON ca.teacher_id = u.id
        LEFT JOIN evaluations e ON e.teacher_id = u.id AND e.evaluator_role = 'student' AND e.evaluator_id = ? AND e.subject_id = ca.subject_id
        WHERE ca.teacher_id != ? AND e.evaluation_id IS NULL 
        ORDER BY s.subject_name, u.name
    ");
    $stmt->execute([$farhiya_id, $farhiya_id]);
    $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Count: " . count($pending) . "\n";
    $pedro_is_pending = false;
    foreach ($pending as $p) {
        echo "  Subject: {$p['subject_name']} | Teacher: {$p['teacher_name']} (ID: {$p['teacher_id']})\n";
        if ($p['teacher_id'] == $pedro_id) {
            $pedro_is_pending = true;
            echo "    ✓ Dr. Pedro is pending\n";
        }
    }
    echo "\n";
    
    // 4. Analysis
    echo "4. ANALYSIS:\n";
    echo str_repeat("-", 100) . "\n";
    
    if ($farhiya_eval_pedro > 0 && $pedro_is_pending) {
        echo "⚠️ PROBLEM: Contradictory state!\n";
        echo "  - Farhiya HAS evaluation data for Dr. Pedro (shouldn't exist)\n";
        echo "  - Dr. Pedro IS showing as pending for Farhiya (shouldn't if she evaluated)\n";
        echo "\n  SOLUTION: Delete the evaluation records\n";
    } elseif ($farhiya_eval_pedro > 0 && !$pedro_is_pending) {
        echo "✓ CORRECT: Farhiya has evaluated Dr. Pedro (pending removed correctly)\n";
    } elseif ($farhiya_eval_pedro == 0 && $pedro_is_pending) {
        echo "✓ CORRECT: Farhiya has NOT evaluated Dr. Pedro (shows as pending)\n";
    } else {
        echo "⚠️ ISSUE: Neither evaluation data nor pending status\n";
    }
    echo "\n";
    
    // 5. Recommendation
    echo "5. RECOMMENDATION:\n";
    echo str_repeat("-", 100) . "\n";
    
    if ($farhiya_eval_pedro > 0) {
        echo "Run cleanup to remove Dr. Pedro evaluation data:\n\n";
        echo "DELETE FROM evaluations WHERE teacher_id = $pedro_id;\n\n";
        echo "Or visit: http://localhost/FEFS/fe-system/quick_cleanup_pedro.php\n";
    } else {
        echo "No cleanup needed. System is in correct state.\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
echo "</pre>";
?>
