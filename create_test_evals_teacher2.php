<?php
require 'config/db.php';

echo "=== Creating Test Evaluation Data for Teacher 2 ===\n\n";

try {
    // First, get available student users
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'student' LIMIT 10");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($students) < 1) {
        echo "No student users found\n";
        exit;
    }

    echo "Using " . count($students) . " student IDs: " . implode(', ', $students) . "\n\n";

    // Create test evaluations with varying ratings
    $evaluations = [
        ['rating' => 4.0, 'feedback' => json_encode(['39' => '4', '40' => '4', '41' => '5', '42' => '4', '43' => '4'])],
        ['rating' => 3.0, 'feedback' => json_encode(['39' => '3', '40' => '2', '41' => '3', '42' => '3', '43' => '4'])],
        ['rating' => 5.0, 'feedback' => json_encode(['39' => '5', '40' => '5', '41' => '5', '42' => '5', '43' => '5'])],
        ['rating' => 4.0, 'feedback' => json_encode(['39' => '4', '40' => '4', '41' => '4', '42' => '5', '43' => '5'])],
        ['rating' => 3.5, 'feedback' => json_encode(['39' => '3', '40' => '3', '41' => '2', '42' => '3', '43' => '3'])],
        ['rating' => 4.5, 'feedback' => json_encode(['39' => '4', '40' => '5', '41' => '4', '42' => '5', '43' => '4'])]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO evaluations (teacher_id, evaluator_id, evaluator_role, subject_id, rating, feedback, created_at, date_submitted)
        VALUES (2, ?, 'student', 1, ?, ?, NOW(), NOW())
    ");

    foreach ($evaluations as $i => $eval) {
        $evaluator_id = $students[$i];
        $stmt->execute([$evaluator_id, $eval['rating'], $eval['feedback']]);
        echo "✓ Created evaluation " . ($i+1) . " (Evaluator: " . $evaluator_id . ", Rating: " . $eval['rating'] . ")\n";
    }

    echo "\n=== Test Data Created Successfully ===\n\n";

    // Verify the data
    $stmt_verify = $pdo->prepare('SELECT COUNT(*) as cnt FROM evaluations WHERE teacher_id = 2');
    $stmt_verify->execute();
    $result = $stmt_verify->fetch();
    echo "✓ Teacher 2 now has " . $result['cnt'] . " evaluations\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
