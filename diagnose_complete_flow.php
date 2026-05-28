<?php
require_once 'config/db.php';
echo "<h1>🔍 Complete Feedback Flow Diagnosis for Dr. Pedro Reyes</h1>";

// 1. Find Dr. Pedro
$stmt = $pdo->query("SELECT id, name, role FROM users WHERE name LIKE '%Pedro%' LIMIT 1");
$pedro = $stmt->fetch();
if (!$pedro) {
    echo "<h2 style='color:red;'>❌ Dr. Pedro not found</h2>";
    exit;
}
$pedro_id = $pedro['id'];
echo "<h2 style='color:green;'>✅ Found Dr. Pedro (ID: $pedro_id)</h2>";

// 2. Check questions table for 'faculty' target_role
echo "<h2>Questions Table - target_role = 'faculty':</h2>";
$stmt = $pdo->query("SELECT id, question_text, target_role FROM questions WHERE target_role = 'faculty'");
$faculty_questions = $stmt->fetchAll();
echo "Count: " . count($faculty_questions) . "<br>";
if (!empty($faculty_questions)) {
    echo "<pre>" . print_r($faculty_questions, true) . "</pre>";
} else {
    echo "<span style='color:red;'>⚠️ No questions found for target_role='faculty'</span><br>";
}

// 3. Check questions table for 'student' target_role (what students use)
echo "<h2>Questions Table - target_role = 'student':</h2>";
$stmt = $pdo->query("SELECT id, question_text, target_role FROM questions WHERE target_role = 'student'");
$student_questions = $stmt->fetchAll();
echo "Count: " . count($student_questions) . "<br>";
if (!empty($student_questions)) {
    echo "<pre>" . print_r($student_questions, true) . "</pre>";
} else {
    echo "<span style='color:red;'>⚠️ No questions found for target_role='student'</span><br>";
}

// 4. Check Pedro's class assignments
echo "<h2>Dr. Pedro's Class Assignments:</h2>";
$stmt = $pdo->prepare("
    SELECT ca.id, ca.subject_id, s.subject_name, ca.course, ca.year_level, ca.section
    FROM class_assignments ca
    JOIN subjects s ON ca.subject_id = s.subject_id
    WHERE ca.teacher_id = ?
");
$stmt->execute([$pedro_id]);
$assignments = $stmt->fetchAll();
echo "Count: " . count($assignments) . "<br>";
echo "<pre>" . print_r($assignments, true) . "</pre>";

// 5. Check all evaluations for Pedro
echo "<h2>All Evaluations for Dr. Pedro (teacher_id = $pedro_id):</h2>";
$stmt = $pdo->prepare("
    SELECT e.*, u.name as student_name, s.subject_name
    FROM evaluations e
    LEFT JOIN users u ON e.student_id = u.id
    LEFT JOIN subjects s ON e.subject_id = s.subject_id
    WHERE e.teacher_id = ?
    ORDER BY e.date_submitted DESC
");
$stmt->execute([$pedro_id]);
$evals = $stmt->fetchAll();
echo "Count: " . count($evals) . "<br>";
if (!empty($evals)) {
    foreach ($evals as $eval) {
        echo "<pre>";
        echo "ID: {$eval['evaluation_id']}\n";
        echo "Subject: {$eval['subject_name']} (ID: {$eval['subject_id']})\n";
        echo "Student: {$eval['student_name']} (ID: {$eval['student_id']})\n";
        echo "Rating: {$eval['rating']}\n";
        echo "Role: {$eval['evaluator_role']}\n";
        echo "Feedback (RAW): {$eval['feedback']}\n";
        echo "Feedback (PARSED): " . print_r(json_decode($eval['feedback'], true), true) . "\n";
        echo "---\n";
        echo "</pre>";
    }
} else {
    echo "<span style='color:orange;'>⚠️ No evaluations found for Dr. Pedro</span><br>";
}

// 6. Check what evaluations query in feedback.php would return
echo "<h2>Simulating feedback.php AJAX for Subject (example):</h2>";
if (!empty($assignments)) {
    $subject_id = $assignments[0]['subject_id'];
    $subject_name = $assignments[0]['subject_name'];
    echo "Testing with Subject: $subject_name (ID: $subject_id)<br>";
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total, AVG(rating) as avg 
        FROM evaluations 
        WHERE teacher_id = ? AND subject_id = ?
    ");
    $stmt->execute([$pedro_id, $subject_id]);
    $summary = $stmt->fetch();
    echo "Total Evaluations: {$summary['total']}<br>";
    echo "Avg Rating: {$summary['avg']}<br>";
    
    // Show the actual evaluations
    echo "<h3>Evaluations for this subject:</h3>";
    $stmt = $pdo->prepare("
        SELECT e.*, u.name as student_name
        FROM evaluations e
        LEFT JOIN users u ON e.student_id = u.id
        WHERE e.teacher_id = ? AND e.subject_id = ?
    ");
    $stmt->execute([$pedro_id, $subject_id]);
    $subj_evals = $stmt->fetchAll();
    echo "Count: " . count($subj_evals) . "<br>";
    if (!empty($subj_evals)) {
        echo "<pre>" . print_r($subj_evals, true) . "</pre>";
    }
}

// 7. Check semester data
echo "<h2>Semester Data:</h2>";
$stmt = $pdo->query("SELECT id, semester_name, school_year FROM semesters ORDER BY id DESC LIMIT 5");
$semesters = $stmt->fetchAll();
echo "<pre>" . print_r($semesters, true) . "</pre>";

// 8. Summary
echo "<h2 style='background:#f0f0f0;padding:1rem;'>📋 SUMMARY:</h2>";
echo "<ul>";
echo "<li><strong>Dr. Pedro ID:</strong> $pedro_id</li>";
echo "<li><strong>Class Assignments:</strong> " . count($assignments) . "</li>";
echo "<li><strong>Total Evaluations (all subjects):</strong> " . count($evals) . "</li>";
echo "<li><strong>Faculty Questions (used by admin/feedback):</strong> " . count($faculty_questions) . "</li>";
echo "<li><strong>Student Questions (used by student/evaluate):</strong> " . count($student_questions) . "</li>";
echo "</ul>";

if (empty($evals)) {
    echo "<div style='background:#ffe6e6;padding:1rem;border-radius:8px;'>";
    echo "<h3 style='color:red;'>❌ NO EVALUATIONS FOUND</h3>";
    echo "<p>This means either:</p>";
    echo "<ol>";
    echo "<li>Students haven't submitted evaluations yet</li>";
    echo "<li>Evaluations were submitted to a different teacher_id</li>";
    echo "<li>Evaluations were submitted but subject_id is missing (NULL)</li>";
    echo "<li>The submit_evaluation.php didn't save the data correctly</li>";
    echo "</ol>";
    echo "</div>";
}
?>
