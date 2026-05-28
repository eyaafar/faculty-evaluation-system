<?php
require_once 'config/db.php';

// Check evaluations table schema
echo "<h2>Evaluations Table Schema</h2>";
$stmt = $pdo->query("DESCRIBE evaluations");
$schema = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($schema);
echo "</pre>";

// Check a sample evaluation to see what data exists
echo "<h2>Sample Evaluations</h2>";
$stmt = $pdo->query("SELECT * FROM evaluations LIMIT 5");
$evals = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($evals);
echo "</pre>";

// Check the pending query in dashboard
echo "<h2>Pending Query Test - Student ID 5</h2>";
$student_id = 5;
$course = 'BSIT';
$year_level = 1;
$section = 'A';

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM class_assignments ca 
    LEFT JOIN evaluations e ON e.student_id = ? AND e.teacher_id = ca.teacher_id AND e.subject_id = ca.subject_id 
    WHERE ca.course = ? AND ca.year_level = ? AND ca.section = ? AND e.evaluation_id IS NULL
");
$stmt->execute([$student_id, $course, $year_level, $section]);
$pending_count = $stmt->fetchColumn();
echo "Pending count (old query): " . $pending_count . "<br>";

// Show the actual pending assignments
$stmt = $pdo->prepare("
    SELECT ca.id, s.subject_name, ca.subject_id, ca.teacher_id, u.name as teacher_name, e.evaluation_id
    FROM class_assignments ca
    JOIN subjects s ON ca.subject_id = s.subject_id
    JOIN users u ON ca.teacher_id = u.id
    LEFT JOIN evaluations e ON e.student_id = ? AND e.teacher_id = ca.teacher_id AND e.subject_id = ca.subject_id 
    WHERE ca.course = ? AND ca.year_level = ? AND ca.section = ?
    ORDER BY s.subject_name
");
$stmt->execute([$student_id, $course, $year_level, $section]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<h3>Assignments Detail:</h3>";
echo "<pre>";
print_r($assignments);
echo "</pre>";

// Check the improved query from evaluate.php
echo "<h2>Pending Query Test - Using evaluate.php query</h2>";
$stmt = $pdo->prepare("
    SELECT ca.id, s.subject_name as subject_name, ca.subject_id, ca.teacher_id, u.name as teacher_name
    FROM class_assignments ca
    JOIN subjects s ON ca.subject_id = s.subject_id
    JOIN users u ON ca.teacher_id = u.id
    LEFT JOIN evaluations e ON (e.teacher_id = ca.teacher_id AND e.subject_id = ca.subject_id)
        AND (e.student_id = ? OR (e.student_id IS NULL AND e.evaluator_role = 'student'))
    WHERE ca.course = ? AND ca.year_level = ? AND ca.section = ? AND e.evaluation_id IS NULL
    ORDER BY s.subject_name
");
$stmt->execute([$student_id, $course, $year_level, $section]);
$assignments2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Pending assignments (evaluate.php query): ";
echo count($assignments2);
echo "<pre>";
print_r($assignments2);
echo "</pre>";

// Check evaluations done count
echo "<h2>Evaluations Done Count</h2>";
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT e.evaluation_id) FROM evaluations e
    WHERE e.student_id = ?
    AND EXISTS (SELECT 1 FROM class_assignments ca 
               WHERE ca.teacher_id = e.teacher_id 
               AND ca.subject_id = e.subject_id)
");
$stmt->execute([$student_id]);
$done_count = $stmt->fetchColumn();
echo "Evaluations done: " . $done_count . "<br>";

// Show recent evaluations for this student
echo "<h2>Recent Evaluations for Student</h2>";
$stmt = $pdo->prepare("
    SELECT evaluation_id, teacher_id, subject_id, evaluator_role, student_id, rating, feedback, date_submitted
    FROM evaluations
    WHERE evaluator_role = 'student' AND (student_id = ? OR evaluator_id = ?)
    ORDER BY date_submitted DESC LIMIT 10
");
$stmt->execute([$student_id, $student_id]);
$recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($recent);
echo "</pre>";
?>
