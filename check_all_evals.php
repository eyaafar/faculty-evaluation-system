<?php
require_once 'config/db.php';

// Check all evaluations with details
$stmt = $pdo->query("SELECT e.*, u.name as teacher_name FROM evaluations e LEFT JOIN users u ON e.teacher_id = u.id");
$evals = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "All evaluations in database:\n\n";
foreach ($evals as $e) {
    echo "ID: {$e['id']}, Teacher ID: {$e['teacher_id']} ({$e['teacher_name']}), Rating: {$e['rating']}, Feedback: {$e['feedback']}\n";
}
?>
