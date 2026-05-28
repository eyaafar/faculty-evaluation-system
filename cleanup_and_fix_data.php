<?php
include 'includes/tab_session.php';
require_once 'config/db.php';

echo "<pre>";
echo "=== COMPREHENSIVE DATA CLEANUP & FIX ===\n\n";

try {
    // Step 1: Delete all incorrect feedback data for Dr. Pedro
    echo "STEP 1: Deleting incorrect Dr. Pedro feedback data\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $pdo->query("SELECT id FROM users WHERE name LIKE '%Pedro%' OR username = 'preyes' LIMIT 1");
    $pedro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedro) {
        echo "ERROR: Dr. Pedro not found\n";
        exit;
    }
    
    $pedro_id = $pedro['id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM evaluations WHERE teacher_id = ?");
    $stmt->execute([$pedro_id]);
    $count_before = $stmt->fetchColumn();
    echo "Evaluations before cleanup: $count_before\n";
    
    $stmt = $pdo->prepare("DELETE FROM evaluations WHERE teacher_id = ?");
    $stmt->execute([$pedro_id]);
    $deleted = $stmt->rowCount();
    echo "Deleted: $deleted records\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM evaluations WHERE teacher_id = ?");
    $stmt->execute([$pedro_id]);
    $count_after = $stmt->fetchColumn();
    echo "Evaluations after cleanup: $count_after\n";
    echo "✓ Cleanup complete\n\n";
    
    // Step 2: Verify question IDs
    echo "STEP 2: Verifying question IDs in database\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $pdo->query("SELECT id, target_role, question_text FROM questions ORDER BY id");
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $student_qs = array_filter($questions, function($q) { return $q['target_role'] === 'student'; });
    $faculty_qs = array_filter($questions, function($q) { return $q['target_role'] === 'faculty'; });
    
    echo "Total Questions: " . count($questions) . "\n";
    echo "Student Questions: " . count($student_qs) . " (IDs: " . implode(', ', array_column($student_qs, 'id')) . ")\n";
    echo "Faculty Questions: " . count($faculty_qs) . " (IDs: " . implode(', ', array_column($faculty_qs, 'id')) . ")\n\n";
    
    // Step 3: Load corrected sample data
    echo "STEP 3: Loading corrected sample data\n";
    echo str_repeat("-", 80) . "\n";
    
    // Get user IDs
    $stmt = $pdo->query("SELECT id, username, name FROM users WHERE role IN ('student', 'teacher')");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $farhiya_id = null;
    $juan_id = null;
    $maria_id = null;
    $pedro_id = null;
    $ana_id = null;
    $ben_id = null;
    
    foreach ($users as $u) {
        if (strpos(strtolower($u['name']), 'farhiya') !== false) $farhiya_id = $u['id'];
        if (strpos(strtolower($u['username']), 'jdelacruz') !== false) $juan_id = $u['id'];
        if (strpos(strtolower($u['username']), 'msantos') !== false) $maria_id = $u['id'];
        if (strpos(strtolower($u['username']), 'preyes') !== false) $pedro_id = $u['id'];
        if (strpos(strtolower($u['username']), 'analim') !== false) $ana_id = $u['id'];
        if (strpos(strtolower($u['username']), 'benkho') !== false) $ben_id = $u['id'];
    }
    
    echo "User IDs:\n";
    echo "  Farhiya: $farhiya_id\n";
    echo "  Juan: $juan_id\n";
    echo "  Maria: $maria_id\n";
    echo "  Dr. Pedro: $pedro_id\n";
    echo "  Ana: $ana_id\n";
    echo "  Ben: $ben_id\n\n";
    
    // Get a subject for the evaluations
    $stmt = $pdo->query("SELECT subject_id FROM subjects LIMIT 1");
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    $subject_id = $subject['subject_id'] ?? 1;
    
    // 1. Farhiya (student) evaluates Dr. Pedro (teacher) - STUDENT questions (1-8)
    if ($farhiya_id && $pedro_id) {
        $feedback_farhiya = json_encode([
            '1' => 5,
            '2' => 4,
            '3' => 4,
            '4' => 4,
            '5' => 4,
            '6' => 'Dr. Pedro explains complex concepts clearly and with great patience',
            '7' => 'Could provide more real-world examples and case studies',
            '8' => 'Overall excellent instructor. Very knowledgeable and approachable.'
        ]);
        
        $stmt = $pdo->prepare("
            INSERT INTO evaluations (teacher_id, subject_id, evaluator_role, evaluator_id, rating, feedback, date_submitted)
            VALUES (?, ?, 'student', ?, 4.2, ?, NOW())
            ON DUPLICATE KEY UPDATE feedback = VALUES(feedback)
        ");
        $stmt->execute([$pedro_id, $subject_id, $farhiya_id, $feedback_farhiya]);
        echo "✓ Loaded: Farhiya evaluating Dr. Pedro (Student evaluation)\n";
    }
    
    // 2. Juan (teacher) evaluates Dr. Pedro (teacher) - FACULTY questions (9-16)
    if ($juan_id && $pedro_id && $juan_id != $pedro_id) {
        $feedback_juan = json_encode([
            '9' => 5,
            '10' => 4,
            '11' => 4,
            '12' => 5,
            '13' => 4,
            '14' => 'Dr. Pedro demonstrates excellent command of subject matter and strong pedagogical skills',
            '15' => 'Could attend more faculty development workshops and conferences',
            '16' => 'Dr. Pedro is a valuable and dedicated member of our faculty team.'
        ]);
        
        $stmt = $pdo->prepare("
            INSERT INTO evaluations (teacher_id, subject_id, evaluator_role, evaluator_id, rating, feedback, date_submitted)
            VALUES (?, ?, 'teacher', ?, 4.3, ?, NOW())
            ON DUPLICATE KEY UPDATE feedback = VALUES(feedback)
        ");
        $stmt->execute([$pedro_id, $subject_id, $juan_id, $feedback_juan]);
        echo "✓ Loaded: Juan evaluating Dr. Pedro (Teacher evaluation)\n";
    }
    
    // 3. Maria (teacher) evaluates Dr. Pedro (teacher) - FACULTY questions (9-16)
    if ($maria_id && $pedro_id && $maria_id != $pedro_id) {
        $feedback_maria = json_encode([
            '9' => 4,
            '10' => 5,
            '11' => 5,
            '12' => 4,
            '13' => 5,
            '14' => 'Excellent curriculum design and strong commitment to student success',
            '15' => 'Very collaborative. Works well with all colleagues.',
            '16' => 'Dr. Pedro is an asset to our institution.'
        ]);
        
        $stmt = $pdo->prepare("
            INSERT INTO evaluations (teacher_id, subject_id, evaluator_role, evaluator_id, rating, feedback, date_submitted)
            VALUES (?, ?, 'teacher', ?, 4.6, ?, NOW())
            ON DUPLICATE KEY UPDATE feedback = VALUES(feedback)
        ");
        $stmt->execute([$pedro_id, $subject_id, $maria_id, $feedback_maria]);
        echo "✓ Loaded: Maria evaluating Dr. Pedro (Teacher evaluation)\n";
    }
    
    echo "\n";
    
    // Step 4: Verify the data
    echo "STEP 4: Verification of loaded data\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            e.evaluation_id,
            e.evaluator_role,
            u.name as evaluator,
            e.rating,
            e.feedback,
            e.date_submitted
        FROM evaluations e
        JOIN users u ON u.id = e.evaluator_id
        WHERE e.teacher_id = ?
        ORDER BY e.date_submitted DESC
    ");
    $stmt->execute([$pedro_id]);
    $verif = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total evaluations for Dr. Pedro: " . count($verif) . "\n\n";
    foreach ($verif as $v) {
        echo "ID: {$v['evaluation_id']} | Evaluator: {$v['evaluator']} ({$v['evaluator_role']}) | Rating: {$v['rating']}\n";
        echo "  Feedback: {$v['feedback']}\n\n";
    }
    
    echo "✓ SUCCESS: Data cleanup and reload complete!\n";
    echo "\nNext steps:\n";
    echo "1. Log in as Farhiya Ayyub (farhiya/password)\n";
    echo "2. Go to Evaluate tab\n";
    echo "3. You should see Dr. Pedro Reyes as a PENDING evaluation\n";
    echo "4. Click Evaluate Now and submit the form\n";
    echo "5. The pending evaluation will be removed and 'Evaluations Done' count increases\n";
    echo "\n6. Log in as Dr. Pedro Reyes (preyes/password)\n";
    echo "7. Go to Feedback & Results to see his evaluations from Farhiya, Juan, and Maria\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "\n=== END OF OPERATION ===\n";
echo "</pre>";
?>
