<?php
require_once 'config/db.php';

$teacher_id = 2; // Mr. Juan Dela Cruz

// Insert sample evaluations with various ratings
$evaluations = [
    ['rating' => 5, 'feedback' => json_encode(['17' => '5', '18' => '5', '19' => '5', '22' => 'Excellent teaching!', '23' => 'Very engaging class']), 'evaluator_role' => 'student'],
    ['rating' => 4, 'feedback' => json_encode(['17' => '4', '18' => '4', '19' => '4', '22' => 'Good content', '23' => 'Could use more examples']), 'evaluator_role' => 'student'],
    ['rating' => 3, 'feedback' => json_encode(['17' => '3', '18' => '3', '19' => '4', '22' => 'Average', '23' => 'Needs improvement']), 'evaluator_role' => 'student'],
    ['rating' => 5, 'feedback' => json_encode(['17' => '5', '18' => '5', '19' => '5', '22' => 'Outstanding performance', '23' => 'Well organized lessons']), 'evaluator_role' => 'student'],
    ['rating' => 4, 'feedback' => json_encode(['17' => '4', '18' => '5', '19' => '4', '22' => 'Professional', '23' => 'Knowledgeable instructor']), 'evaluator_role' => 'student'],
];

try {
    $stmt = $pdo->prepare("INSERT INTO evaluations (teacher_id, rating, feedback, evaluator_role, subject_id) VALUES (?, ?, ?, ?, 1)");
    
    foreach ($evaluations as $eval) {
        $stmt->execute([$teacher_id, $eval['rating'], $eval['feedback'], $eval['evaluator_role']]);
    }
    
    echo "Successfully inserted " . count($evaluations) . " test evaluations for teacher ID {$teacher_id}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
