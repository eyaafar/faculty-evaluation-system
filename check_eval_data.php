<?php
require 'config/db.php';

echo "=== Teacher 2 Evaluations ===\n\n";

$stmt = $pdo->prepare("SELECT id, teacher_id, evaluator_id, rating, feedback, created_at FROM evaluations WHERE teacher_id = 2 LIMIT 5");
$stmt->execute();
$evals = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($evals as $i => $eval) {
    echo "Eval " . ($i+1) . ":\n";
    echo "  ID: " . $eval['id'] . "\n";
    echo "  Evaluator ID: " . ($eval['evaluator_id'] ?? 'NULL') . "\n";
    echo "  Rating: " . $eval['rating'] . "\n";
    echo "  Feedback JSON: " . substr($eval['feedback'], 0, 50) . "...\n";
    echo "\n";
}

echo "=== Check User Names ===\n";
$stmt2 = $pdo->prepare("SELECT id, name FROM users WHERE id IN (SELECT evaluator_id FROM evaluations WHERE teacher_id = 2 AND evaluator_id IS NOT NULL)");
$stmt2->execute();
$users = $stmt2->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    echo "No user names found for evaluator IDs\n";
} else {
    foreach ($users as $user) {
        echo "ID " . $user['id'] . ": " . $user['name'] . "\n";
    }
}
