<?php
/**
 * CHECK REAL DATABASE DATA FOR DR. PEDRO REYES
 * Shows the actual data that Professor Jag will receive
 */

require_once 'config/db.php';

echo "=== REAL DATABASE DATA FOR DR. PEDRO REYES ===\n\n";

// Get teacher info
echo "1. TEACHER INFORMATION:\n";
$stmt = $pdo->prepare("SELECT id, name, username FROM users WHERE id = 4 AND role = 'teacher'");
$stmt->execute();
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

if ($teacher) {
    echo "   ID: {$teacher['id']}\n";
    echo "   Name: {$teacher['name']}\n";
    echo "   Username: {$teacher['username']}\n\n";
} else {
    echo "   ❌ Teacher ID 4 not found!\n\n";
}

// Get evaluation statistics
echo "2. EVALUATION STATISTICS:\n";
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_evaluations,
        AVG(rating) as average_rating,
        COUNT(CASE WHEN evaluator_role = 'student' THEN 1 END) as student_evaluations,
        COUNT(CASE WHEN evaluator_role = 'teacher' THEN 1 END) as faculty_evaluations,
        AVG(CASE WHEN evaluator_role = 'student' THEN rating END) as student_avg_rating,
        AVG(CASE WHEN evaluator_role = 'teacher' THEN rating END) as faculty_avg_rating,
        MIN(rating) as min_rating,
        MAX(rating) as max_rating
    FROM evaluations 
    WHERE teacher_id = 4
");
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

if ($stats && $stats['total_evaluations'] > 0) {
    echo "   Total Evaluations: {$stats['total_evaluations']}\n";
    echo "   Average Rating: " . round($stats['average_rating'], 1) . "/5\n";
    echo "   Student Evaluations: {$stats['student_evaluations']}\n";
    echo "   Faculty Evaluations: {$stats['faculty_evaluations']}\n";
    echo "   Student Avg Rating: " . round($stats['student_avg_rating'], 1) . "/5\n";
    echo "   Faculty Avg Rating: " . round($stats['faculty_avg_rating'], 1) . "/5\n";
    echo "   Min Rating: {$stats['min_rating']}/5\n";
    echo "   Max Rating: {$stats['max_rating']}/5\n\n";
} else {
    echo "   ❌ No evaluations found for teacher ID 4\n\n";
}

// Get recent feedback
echo "3. RECENT FEEDBACK:\n";
$stmt = $pdo->prepare("
    SELECT 
        e.rating,
        e.feedback,
        e.evaluator_role,
        e.date_submitted,
        u.name as evaluator_name,
        s.subject_name
    FROM evaluations e
    LEFT JOIN users u ON e.evaluator_id = u.id
    LEFT JOIN subjects s ON e.subject_id = s.subject_id
    WHERE e.teacher_id = 4
    ORDER BY e.date_submitted DESC
    LIMIT 10
");
$stmt->execute();
$recent_feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($recent_feedback) {
    foreach ($recent_feedback as $i => $feedback) {
        echo "   Evaluation " . ($i + 1) . ":\n";
        echo "   - Rating: {$feedback['rating']}/5\n";
        echo "   - Evaluator: {$feedback['evaluator_name']} ({$feedback['evaluator_role']})\n";
        echo "   - Subject: {$feedback['subject_name']}\n";
        echo "   - Date: {$feedback['date_submitted']}\n";
        echo "   - Feedback: {$feedback['feedback']}\n\n";
    }
} else {
    echo "   ❌ No feedback found\n\n";
}

// Show what Professor Jag should actually say
echo "4. CORRECT PROFESSOR JAG RESPONSE:\n";
if ($teacher && $stats && $stats['total_evaluations'] > 0) {
    echo "   Professor Jag should say:\n";
    echo "   \"Based on your evaluation data, I can see {$teacher['name']} has {$stats['total_evaluations']} evaluations with an average rating of " . round($stats['average_rating'], 1) . "/5. Here are my recommendations...\"\n\n";
    
    echo "   This is CORRECT because:\n";
    echo "   - It matches the REAL database data\n";
    echo "   - It uses the actual teacher name from the database\n";
    echo "   - It shows the actual evaluation count\n";
    echo "   - It shows the actual average rating\n";
} else {
    echo "   ❌ Cannot generate correct response - missing data\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
?>