<?php
require_once 'config/db.php';
echo "<h1>🔍 Dr. Pedro Reyes Feedback Diagnosis - FIXED</h1>";

// 1. Find Dr. Pedro
echo "<h2>1. Users - Dr. Pedro Reyes</h2>";
$stmt = $pdo->query("SELECT id, name, role FROM users WHERE name LIKE '%Pedro%' OR name LIKE '%Reyes%' ORDER BY id");
$pedro_users = $stmt->fetchAll();
echo "<pre>" . print_r($pedro_users, true) . "</pre>";

if (!empty($pedro_users)) {
    $pedro_id = $pedro_users[0]['id'];
    echo "<h3 style='color: green;'>✅ Using Pedro ID: $pedro_id</h3>";
    
    // 2. Pedro's class_assignments (my_subjects.php uses this)
    echo "<h2>2. Pedro's class_assignments (should show Programming Fundamentals)</h2>";
    $stmt = $pdo->prepare("
        SELECT ca.*, s.subject_name, s.subject_id
        FROM class_assignments ca 
        JOIN subjects s ON ca.subject_id = s.subject_id 
        WHERE ca.teacher_id = ? 
        ORDER BY s.subject_name
    ");
    $stmt->execute([$pedro_id]);
    $assignments = $stmt->fetchAll();
    echo "<pre>" . print_r($assignments, true) . "</pre>";
    
    // 3. Pedro's teacher_subjects (feedback.php dropdown uses this - likely EMPTY)
    echo "<h2>3. Pedro's teacher_subjects (PROBLEM: likely empty)</h2>";
    $stmt = $pdo->prepare("
        SELECT ts.*, s.subject_name, s.subject_id 
        FROM teacher_subjects ts 
        JOIN subjects s ON ts.subject_id = s.subject_id 
        WHERE ts.teacher_id = ?
    ");
    $stmt->execute([$pedro_id]);
    $tsubjects = $stmt->fetchAll();
    echo "<pre>" . print_r($tsubjects, true) . "</pre>";
    
    // 4. Programming Fundamentals subject
    echo "<h2>4. Programming Fundamentals subject</h2>";
    $stmt = $pdo->query("SELECT subject_id, subject_name FROM subjects WHERE subject_name = 'Programming Fundamentals'");
    $fundamentals = $stmt->fetch();
    echo "<pre>" . print_r($fundamentals, true) . "</pre>";
    
    // 5. Pedro's evaluations
    echo "<h2>5. Pedro's evaluations (for Programming Fundamentals?)</h2>";
    $stmt = $pdo->prepare("SELECT COUNT(*) as eval_count, AVG(rating) as avg_rating FROM evaluations WHERE teacher_id = ?");
    $stmt->execute([$pedro_id]);
    $evals = $stmt->fetch();
    echo "<pre>" . print_r($evals, true) . "</pre>";
    
    // SUMMARY
    echo "<hr><h2 style='color: navy;'>📋 SUMMARY:</h2>";
    echo "<ul>";
    echo "<li><strong>Pedro ID:</strong> $pedro_id</li>";
    echo "<li><strong>class_assignments:</strong> " . count($assignments) . " subjects</li>";
    echo "<li><strong>teacher_subjects:</strong> " . count($tsubjects) . " subjects <span style='color:red;'>(MISMATCH!)</span></li>";
    echo "<li><strong>Programming Fundamentals in class_assignments:</strong> " . (array_search('Programming Fundamentals', array_column($assignments, 'subject_name')) !== false ? '<span style=\"color:green;\">YES</span>' : '<span style=\"color:red;\">NO</span>') . "</li>";
    echo "<li><strong>Has any evaluations:</strong> " . ($evals['eval_count'] ?? 0) . "</li>";
    echo "</ul>";
    
    echo "<h3 style='color: green;'>🛠️ FIX: Add to teacher_subjects</h3>";
    echo "<pre style='background:#f0f8f0;padding:1rem;border-radius:8px;'>";
    foreach ($assignments as $assign) {
        echo "INSERT IGNORE INTO teacher_subjects (teacher_id, subject_id) VALUES ($pedro_id, {$assign['subject_id']}); -- {$assign['subject_name']}\n";
    }
    echo "</pre>";
    
    if ($fundamentals) {
        echo "<p style='color:orange;'>💡 Or specifically for Programming Fundamentals:</p>";
        echo "<pre>INSERT IGNORE INTO teacher_subjects (teacher_id, subject_id) VALUES ($pedro_id, {$fundamentals['subject_id']});</pre>";
    }
} else {
    echo "<h2 style='color:red;'>❌ NO Dr. Pedro Reyes user found in users table!</h2>";
    echo "<p>Add him via admin/faculty.php or check spelling.</p>";
}
echo "<hr><p><a href='javascript:history.back()'>← Back</a> | <a href='login.php'>Login</a></p>";
?>

