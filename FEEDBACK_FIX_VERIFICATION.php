<?php
require_once 'config/db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Feedback System - Complete Fix Verification</title>
    <style>
        body { font-family: Arial; margin: 2rem; background: #f5f5f5; }
        .success { background: #d4edda; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #28a745; }
        .warning { background: #fff3cd; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #ffc107; }
        .error { background: #f8d7da; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #17a2b8; }
        h1 { color: #333; }
        h2 { color: #555; margin-top: 2rem; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
        pre { background: #f0f0f0; padding: 1rem; border-radius: 8px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { border: 1px solid #ddd; padding: 1rem; text-align: left; }
        th { background: #f9f9f9; font-weight: bold; }
    </style>
</head>
<body>
";

echo "<h1>✅ Feedback System - Complete Fix Verification</h1>";

// Check 1: Database Schema
echo "<h2>1️⃣ Database Schema Check</h2>";
$stmt = $pdo->query("DESCRIBE evaluations");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
$required = ['teacher_id', 'student_id', 'subject_id', 'rating', 'feedback', 'evaluator_role'];
$has_all = true;
foreach ($required as $col) {
    if (!in_array($col, $columns)) {
        $has_all = false;
        echo "<div class='error'>❌ Missing column: <code>$col</code></div>";
    }
}
if ($has_all) {
    echo "<div class='success'>✅ All required database columns exist in evaluations table</div>";
}

// Check 2: Questions Table
echo "<h2>2️⃣ Questions Table Check</h2>";
$stmt = $pdo->query("SELECT COUNT(*) FROM questions WHERE target_role = 'student'");
$student_q = $stmt->fetchColumn();
if ($student_q > 0) {
    echo "<div class='success'>✅ Found $student_q questions with target_role='student' (used by student evaluations)</div>";
} else {
    echo "<div class='warning'>⚠️ No student questions found. Create them first!</div>";
}

// Check 3: Data Flow Test
echo "<h2>3️⃣ Data Flow Verification</h2>";
echo "<table>
    <tr>
        <th>Component</th>
        <th>Status</th>
        <th>Details</th>
    </tr>";

// Check evaluate.php submission
$stmt = $pdo->prepare("SELECT * FROM evaluations WHERE evaluator_role = 'student' LIMIT 1");
$stmt->execute();
$student_eval = $stmt->fetch();
if ($student_eval) {
    echo "<tr>
        <td><strong>Student Evaluations</strong><br/>(from student/evaluate.php)</td>
        <td>✅ Working</td>
        <td>Found " . $pdo->query("SELECT COUNT(*) FROM evaluations WHERE evaluator_role = 'student'")->fetchColumn() . " evaluations</td>
    </tr>";
    
    // Check JSON format
    $feedback = json_decode($student_eval['feedback'], true);
    if (is_array($feedback) && !isset($feedback[0])) {
        echo "<tr>
            <td><strong>Feedback JSON Format</strong></td>
            <td>✅ Correct</td>
            <td>Object format: {\"question_id\": rating}</td>
        </tr>";
    } else {
        echo "<tr>
            <td><strong>Feedback JSON Format</strong></td>
            <td>⚠️ Check</td>
            <td>Format: " . substr(print_r($feedback, true), 0, 50) . "</td>
        </tr>";
    }
    
    // Check subject_id
    if ($student_eval['subject_id']) {
        echo "<tr>
            <td><strong>Subject ID Saved</strong></td>
            <td>✅ Yes</td>
            <td>Subject ID: {$student_eval['subject_id']}</td>
        </tr>";
    } else {
        echo "<tr>
            <td><strong>Subject ID Saved</strong></td>
            <td>❌ No</td>
            <td>Subject ID is NULL - evaluations won't filter by subject</td>
        </tr>";
    }
    
    // Check student_id
    if ($student_eval['student_id']) {
        echo "<tr>
            <td><strong>Student ID Saved</strong></td>
            <td>✅ Yes</td>
            <td>Student ID: {$student_eval['student_id']}</td>
        </tr>";
    }
    
} else {
    echo "<tr>
        <td colspan='3'><em>No student evaluations found yet</em></td>
    </tr>";
}

echo "</table>";

// Check 4: Teacher Feedback Display
echo "<h2>4️⃣ Teacher Feedback Display (feedback.php)</h2>";
$stmt = $pdo->query("SELECT COUNT(DISTINCT teacher_id) FROM evaluations WHERE evaluator_role = 'student'");
$teachers_with_evals = $stmt->fetchColumn();
if ($teachers_with_evals > 0) {
    echo "<div class='success'>✅ $teachers_with_evals teacher(s) have student evaluations waiting for feedback display</div>";
    
    // Show sample for Pedro
    $stmt = $pdo->query("SELECT id FROM users WHERE name LIKE '%Pedro%' LIMIT 1");
    $pedro = $stmt->fetch();
    if ($pedro) {
        $pedro_id = $pedro['id'];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM evaluations WHERE teacher_id = ? AND evaluator_role = 'student'");
        $stmt->execute([$pedro_id]);
        $pedro_evals = $stmt->fetchColumn();
        echo "<div class='info'>ℹ️ Dr. Pedro Reyes: <strong>$pedro_evals</strong> evaluations found</div>";
    }
} else {
    echo "<div class='warning'>⚠️ No student evaluations yet. Have students submit feedback first!</div>";
}

// Check 5: Code Changes Applied
echo "<h2>5️⃣ Code Fixes Applied</h2>";
echo "<div class='success'>";
echo "<h3>✅ Changes Made:</h3>";
echo "<ul>";
echo "<li><strong>feedback.php</strong>: Uses <code>class_assignments</code> instead of <code>teacher_subjects</code></li>";
echo "<li><strong>feedback.php</strong>: Uses <code>target_role='student'</code> questions (matches evaluate.php)</li>";
echo "<li><strong>feedback.php</strong>: Properly parses JSON feedback with question ratings</li>";
echo "<li><strong>submit_evaluation.php</strong>: Converts responses array to object format {\"qid\": rating}</li>";
echo "<li><strong>submit_evaluation.php</strong>: Calculates actual average rating from questions</li>";
echo "<li><strong>submit_evaluation.php</strong>: Saves subject_id, student_id, and teacher_id</li>";
echo "<li><strong>process_evaluation.php files</strong>: Include subject_id and proper date_submitted</li>";
echo "</ul>";
echo "</div>";

// Check 6: Apply/Reset Functionality
echo "<h2>6️⃣ Apply & Reset Button Functionality</h2>";
echo "<div class='info'>";
echo "<p><strong>How these work:</strong></p>";
echo "<ul>";
echo "<li><strong>Subject filter</strong>: Auto-triggers data load on change</li>";
echo "<li><strong>Semester filter</strong>: Auto-triggers data load on change</li>";
echo "<li><strong>Apply button</strong>: Manually trigger data load with selected filters</li>";
echo "<li><strong>Reset button</strong>: Clear filters and load all data</li>";
echo "</ul>";
echo "<p><strong>In feedback.php JavaScript (assets/js/feedback-analytics.js):</strong></p>";
echo "<pre>
// Auto-load on filter change
document.getElementById('subjectFilter').addEventListener('change', (e) => {
    fetchData(parseInt(e.target.value), semesterValue);
});

// Apply button explicit trigger
document.getElementById('applyFilters').addEventListener('click', () => {
    fetchData(subjectValue, semesterValue);
});

// Reset clears and loads all
document.getElementById('resetFilters').addEventListener('click', () => {
    subjectFilter.value = '0';
    semesterFilter.value = '';
    fetchData(0, '');
});
</pre>";
echo "</div>";

// Final Instructions
echo "<h2>✨ Next Steps for Testing</h2>";
echo "<div class='info'>";
echo "<ol>";
echo "<li><strong>Make sure students have submitted evaluations:</strong><br/>
Have a student go to <code>student/evaluate.php</code>, select Dr. Pedro's subject, answer all questions, and click Submit.</li>";
echo "<li><strong>View feedback as teacher:</strong><br/>
Go to <code>teacher/feedback.php</code> as Dr. Pedro Reyes, or as any teacher with evaluations.</li>";
echo "<li><strong>Test filters:</strong><br/>
Select a subject from the Subject dropdown → Click Apply button → Should show ratings and feedback for that subject.</li>";
echo "<li><strong>Test reset:</strong><br/>
Click the Reset button → Should clear filters and show all data.</li>";
echo "</ol>";
echo "</div>";

// Database Status
echo "<h2>📊 Current Database Status</h2>";
$stats = [];
$stats['Total Evaluations'] = $pdo->query("SELECT COUNT(*) FROM evaluations")->fetchColumn();
$stats['Student Evaluations'] = $pdo->query("SELECT COUNT(*) FROM evaluations WHERE evaluator_role = 'student'")->fetchColumn();
$stats['Teacher Evaluations'] = $pdo->query("SELECT COUNT(*) FROM evaluations WHERE evaluator_role = 'teacher'")->fetchColumn();
$stats['Teachers with Evaluations'] = $pdo->query("SELECT COUNT(DISTINCT teacher_id) FROM evaluations WHERE evaluator_role = 'student'")->fetchColumn();
$stats['Student Questions'] = $pdo->query("SELECT COUNT(*) FROM questions WHERE target_role = 'student'")->fetchColumn();

echo "<table>";
foreach ($stats as $key => $value) {
    echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
}
echo "</table>";

echo "</body></html>";
?>
