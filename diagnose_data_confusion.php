<?php
include 'includes/tab_session.php';
require_once 'config/db.php';

echo "<pre>";
echo "=== DIAGNOSTIC REPORT: DATA CONFUSION ===\n\n";

// Get user IDs
$stmt = $pdo->query("SELECT id, name, role FROM users WHERE name LIKE '%Farhiya%' OR name LIKE '%Pedro%'");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "1. RELEVANT USERS:\n";
foreach ($users as $u) {
    echo "  ID: {$u['id']}, Name: {$u['name']}, Role: {$u['role']}\n";
}
echo "\n";

$farhiya_id = null;
$pedro_id = null;
foreach ($users as $u) {
    if (strpos($u['name'], 'Farhiya') !== false) $farhiya_id = $u['id'];
    if (strpos($u['name'], 'Pedro') !== false) $pedro_id = $u['id'];
}

echo "2. EVALUATIONS TABLE DATA:\n";
echo "---\n";

// Show all evaluations for Dr. Pedro
if ($pedro_id) {
    echo "\nEvaluations WHERE teacher_id = $pedro_id (Dr. Pedro being evaluated):\n";
    $stmt = $pdo->prepare("
        SELECT 
            evaluation_id, 
            teacher_id, 
            student_id, 
            evaluator_id,
            evaluator_role,
            subject_id,
            rating,
            feedback,
            date_submitted
        FROM evaluations 
        WHERE teacher_id = ?
    ");
    $stmt->execute([$pedro_id]);
    $evals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Count: " . count($evals) . "\n";
    foreach ($evals as $e) {
        echo json_encode($e, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
}

// Show all evaluations BY Farhiya
if ($farhiya_id) {
    echo "\nEvaluations WHERE evaluator_id = $farhiya_id (Farhiya evaluating):\n";
    $stmt = $pdo->prepare("
        SELECT 
            evaluation_id, 
            teacher_id, 
            student_id, 
            evaluator_id,
            evaluator_role,
            subject_id,
            rating,
            feedback,
            date_submitted,
            (SELECT name FROM users WHERE id = teacher_id) as teacher_name
        FROM evaluations 
        WHERE evaluator_id = ?
    ");
    $stmt->execute([$farhiya_id]);
    $evals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Count: " . count($evals) . "\n";
    foreach ($evals as $e) {
        echo json_encode($e, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
}

// Show all evaluations BY Dr. Pedro (as evaluator)
if ($pedro_id) {
    echo "\nEvaluations WHERE evaluator_id = $pedro_id (Dr. Pedro evaluating others):\n";
    $stmt = $pdo->prepare("
        SELECT 
            evaluation_id, 
            teacher_id, 
            student_id, 
            evaluator_id,
            evaluator_role,
            subject_id,
            rating,
            feedback,
            date_submitted,
            (SELECT name FROM users WHERE id = teacher_id) as teacher_name
        FROM evaluations 
        WHERE evaluator_id = ?
    ");
    $stmt->execute([$pedro_id]);
    $evals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Count: " . count($evals) . "\n";
    foreach ($evals as $e) {
        echo json_encode($e, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
}

echo "\n3. USERS TABLE:\n";
echo "---\n";
$stmt = $pdo->query("SELECT id, name, role FROM users ORDER BY id");
$all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($all_users as $u) {
    echo "{$u['id']}: {$u['name']} ({$u['role']})\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
echo "</pre>";
?>
