<?php
require_once 'config/db.php';
echo "<h1>🔍 Dr. Pedro Reyes Feedback Data Diagnosis</h1>";

// Find Pedro
$stmt = $pdo->query("SELECT id, name, role FROM users WHERE name LIKE '%Pedro%' OR name LIKE '%Reyes%'");
$pedro_users = $stmt->fetchAll();
echo "<h2>1. Pedro User(s):</h2><pre>" . print_r($pedro_users, true) . "</pre>";

if (!empty($pedro_users)) {
    $pedro_id = $pedro_users[0]['id'];
    echo "<h3 style='color: green;'>✅ Using Pedro ID: $pedro_id</h3>";
    
    // Check evaluations table structure
    echo "<h2>2. Evaluations Table Structure:</h2>";
    $stmt = $pdo->query("DESCRIBE evaluations");
    echo "<pre>" . print_r($stmt->fetchAll(), true) . "</pre>";
    
    // Check evaluations for Pedro
    echo "<h2>3. All Evaluations for Pedro (teacher_id = $pedro_id):</h2>";
    $stmt = $pdo->prepare("SELECT * FROM evaluations WHERE teacher_id = ? LIMIT 20");
    $stmt->execute([$pedro_id]);
    $evals = $stmt->fetchAll();
    echo "<pre>" . print_r($evals, true) . "</pre>";
    echo "Total count: " . count($evals) . "<br>";
    
    // Check his class assignments
    echo "<h2>4. Pedro's Class Assignments:</h2>";
    $stmt = $pdo->prepare("
        SELECT ca.id, ca.teacher_id, ca.subject_id, ca.course, ca.year_level, ca.section, s.subject_name
        FROM class_assignments ca 
        JOIN subjects s ON ca.subject_id = s.subject_id
        WHERE ca.teacher_id = ?
    ");
    $stmt->execute([$pedro_id]);
    $assignments = $stmt->fetchAll();
    echo "<pre>" . print_r($assignments, true) . "</pre>";
    
    // Check evaluations for each subject
    echo "<h2>5. Evaluations by Subject:</h2>";
    foreach ($assignments as $assign) {
        $subject_id = $assign['subject_id'];
        $subject_name = $assign['subject_name'];
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as cnt, AVG(rating) as avg_rating
            FROM evaluations 
            WHERE teacher_id = ? AND subject_id = ?
        ");
        $stmt->execute([$pedro_id, $subject_id]);
        $result = $stmt->fetch();
        echo "<br><strong>Subject: $subject_name (ID: $subject_id)</strong><br>";
        echo "Evaluations: " . $result['cnt'] . " | Avg Rating: " . $result['avg_rating'] . "<br>";
        
        // Show sample evaluations for this subject
        $stmt = $pdo->prepare("
            SELECT evaluation_id, rating, feedback, date_submitted 
            FROM evaluations 
            WHERE teacher_id = ? AND subject_id = ?
            LIMIT 3
        ");
        $stmt->execute([$pedro_id, $subject_id]);
        $evals_sample = $stmt->fetchAll();
        if (!empty($evals_sample)) {
            echo "<pre>" . print_r($evals_sample, true) . "</pre>";
        }
    }
    
    // Check if there's any evaluations data at all
    echo "<h2>6. Total Evaluations in Database:</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM evaluations");
    $total = $stmt->fetch();
    echo "Total: " . $total['cnt'] . "<br>";
    
    // Sample random evaluations
    echo "<h2>7. Sample Evaluations (any teacher):</h2>";
    $stmt = $pdo->query("SELECT evaluation_id, teacher_id, subject_id, rating, date_submitted FROM evaluations LIMIT 10");
    echo "<pre>" . print_r($stmt->fetchAll(), true) . "</pre>";
} else {
    echo "<h2 style='color:red;'>❌ NO Pedro found!</h2>";
}
?>
