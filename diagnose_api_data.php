<?php
/**
 * Diagnose API data accuracy for teacher_id=2
 */

require_once 'config/db.php';

echo "<h2>Database Diagnostic for Teacher ID 2</h2>";

// Check if teacher exists
$stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = 2");
$stmt->execute();
$teacher = $stmt->fetch();
echo "<h3>Teacher Info:</h3>";
echo "<pre>" . print_r($teacher, true) . "</pre>";

// Get all evaluations for teacher_id=2
echo "<h3>All Evaluations for Teacher ID 2:</h3>";
$stmt = $pdo->prepare("SELECT * FROM evaluations WHERE teacher_id = 2 ORDER BY created_at DESC");
$stmt->execute();
$evals = $stmt->fetchAll();
echo "Count: " . count($evals) . "<br>";
echo "<pre>" . print_r($evals, true) . "</pre>";

// Get distinct evaluator IDs (students who evaluated this teacher)
echo "<h3>Students who evaluated teacher 2:</h3>";
$stmt = $pdo->prepare("SELECT DISTINCT evaluator_id FROM evaluations WHERE teacher_id = 2");
$stmt->execute();
$evaluators = $stmt->fetchAll();
foreach ($evaluators as $e) {
    $uid = $e['evaluator_id'];
    if ($uid) {
        $ustmt = $pdo->prepare("SELECT id, name FROM users WHERE id = ?");
        $ustmt->execute([$uid]);
        $u = $ustmt->fetch();
        echo "ID: $uid - " . ($u ? $u['name'] : 'Not found') . "<br>";
    }
}

// Get all questions for students
echo "<h3>All Questions (target_role=student):</h3>";
$stmt = $pdo->prepare("SELECT id, question_text, question_type FROM questions WHERE target_role = 'student' ORDER BY id");
$stmt->execute();
$questions = $stmt->fetchAll();
echo "<pre>" . print_r($questions, true) . "</pre>";

// Show the actual feedback JSON for each evaluation
echo "<h3>Evaluation Feedback Details:</h3>";
$stmt = $pdo->prepare("SELECT id, evaluator_id, rating, feedback, created_at FROM evaluations WHERE teacher_id = 2");
$stmt->execute();
$evals = $stmt->fetchAll();
foreach ($evals as $eval) {
    echo "<hr>";
    echo "Evaluation ID: " . $eval['id'] . " | Evaluator: " . $eval['evaluator_id'] . " | Rating: " . $eval['rating'] . "<br>";
    $feedback = json_decode($eval['feedback'], true);
    if ($feedback) {
        echo "Feedback Breakdown:<br>";
        foreach ($feedback as $qid => $rating) {
            echo "&nbsp;&nbsp;Question $qid: $rating<br>";
        }
    }
}

?>
