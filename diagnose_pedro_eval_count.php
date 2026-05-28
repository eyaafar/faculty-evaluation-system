<?php
/**
 * Diagnostic script to check Dr. Pedro Reyes' evaluation data
 */

require_once 'config/db.php';

echo "<h1>Dr. Pedro Reyes (Teacher ID: 4) - Evaluation Diagnostic</h1>";

// Check teacher info
$stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE id = 4");
$stmt->execute();
$teacher = $stmt->fetch();
echo "<p><strong>Teacher:</strong> " . $teacher['name'] . " (ID: " . $teacher['id'] . ", Role: " . $teacher['role'] . ")</p>";

// Check all evaluations where teacher_id=4
echo "<h2>All Evaluations in Database (teacher_id = 4)</h2>";
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM evaluations WHERE teacher_id = 4");
$stmt->execute();
$result = $stmt->fetch();
echo "<p><strong>Total evaluations with teacher_id=4:</strong> " . $result['count'] . "</p>";

// Get details of each evaluation
$stmt = $pdo->prepare("
    SELECT 
        e.id,
        e.teacher_id,
        e.evaluator_id,
        e.evaluator_role,
        e.rating,
        e.subject_id,
        e.created_at,
        u.name as evaluator_name
    FROM evaluations e
    LEFT JOIN users u ON u.id = e.evaluator_id
    WHERE e.teacher_id = 4
    ORDER BY e.created_at DESC
");
$stmt->execute();
$evals = $stmt->fetchAll();

echo "<table border='1' cellpadding='10' cellspacing='0' style='width:100%; margin:20px 0;'>";
echo "<tr><th>Eval ID</th><th>Evaluator ID</th><th>Evaluator Name</th><th>Role</th><th>Rating</th><th>Subject ID</th><th>Date</th></tr>";
foreach ($evals as $e) {
    echo "<tr>";
    echo "<td>" . $e['id'] . "</td>";
    echo "<td>" . $e['evaluator_id'] . "</td>";
    echo "<td>" . ($e['evaluator_name'] ?? 'Unknown') . "</td>";
    echo "<td>" . $e['evaluator_role'] . "</td>";
    echo "<td>" . $e['rating'] . "</td>";
    echo "<td>" . $e['subject_id'] . "</td>";
    echo "<td>" . $e['created_at'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Now test what the API returns
echo "<h2>API Endpoint Test (Session Auth)</h2>";
echo "<p>Testing what the system-data.php API returns when called with teacher_id=4...</p>";

// Simulate what happens when user is logged in as teacher_id=4
$_SESSION['user_id'] = 4;
$_SESSION['role'] = 'teacher';
$_SESSION['name'] = 'Dr. Pedro Reyes';

// Now include and test the API
ob_start();

// Create a mock request to test the API
$test_teacher_id = 4;
$test_viewer = 'student';

$where = " WHERE e.teacher_id = ? ";
$params = [$test_teacher_id];
$where .= " AND (e.evaluator_role = 'student' OR e.evaluator_role IS NULL)";

$sql = "SELECT
    COUNT(DISTINCT e.id) as total_evaluations,
    COALESCE(AVG(e.rating), 0) as overall_rating,
    COUNT(DISTINCT e.evaluator_id) as total_respondents
FROM evaluations e" . $where;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$metrics = $stmt->fetch();

echo "<p><strong>Total Evaluations (API Query):</strong> " . $metrics['total_evaluations'] . "</p>";
echo "<p><strong>Overall Rating (API Query):</strong> " . round($metrics['overall_rating'], 2) . "</p>";
echo "<p><strong>Total Respondents (API Query):</strong> " . $metrics['total_respondents'] . "</p>";

// Check if there are OTHER teachers with evaluations that might be confusing
echo "<h2>All Teachers with Evaluations</h2>";
$stmt = $pdo->prepare("
    SELECT 
        e.teacher_id,
        u.name,
        COUNT(*) as eval_count
    FROM evaluations e
    JOIN users u ON u.id = e.teacher_id
    GROUP BY e.teacher_id
    ORDER BY eval_count DESC
");
$stmt->execute();
$teachers = $stmt->fetchAll();

echo "<table border='1' cellpadding='10' cellspacing='0' style='width:100%;'>";
echo "<tr><th>Teacher ID</th><th>Teacher Name</th><th>Total Evaluations</th></tr>";
foreach ($teachers as $t) {
    echo "<tr>";
    echo "<td>" . $t['teacher_id'] . "</td>";
    echo "<td>" . $t['name'] . "</td>";
    echo "<td>" . $t['eval_count'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check questions table - maybe there are 20 questions?
echo "<h2>Questions in Database (target_role='student')</h2>";
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM questions WHERE target_role = 'student'");
$stmt->execute();
$qcount = $stmt->fetch();
echo "<p><strong>Total student questions:</strong> " . $qcount['count'] . "</p>";

$stmt = $pdo->prepare("SELECT id, question_text FROM questions WHERE target_role = 'student' ORDER BY id");
$stmt->execute();
$questions = $stmt->fetchAll();
echo "<ul>";
foreach ($questions as $q) {
    echo "<li>Q" . $q['id'] . ": " . $q['question_text'] . "</li>";
}
echo "</ul>";

?>
