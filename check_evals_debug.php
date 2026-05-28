<?php
require_once 'config/db.php';

$teacher_id = 2; // Mr. Juan Dela Cruz

// Check evaluations for this teacher
$stmt = $pdo->prepare("SELECT * FROM evaluations WHERE teacher_id = ? LIMIT 5");
$stmt->execute([$teacher_id]);
$evals = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total evaluations for teacher ID {$teacher_id}: " . count($evals) . "\n\n";

foreach ($evals as $e) {
    echo json_encode($e, JSON_PRETTY_PRINT) . "\n";
}

// Also check all evaluations
$stmt = $pdo->query("SELECT COUNT(*) as cnt FROM evaluations");
$total = $stmt->fetch();
echo "\nTotal evaluations in database: " . $total['cnt'] . "\n";
?>
