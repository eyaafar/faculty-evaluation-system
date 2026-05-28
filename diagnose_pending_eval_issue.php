<?php
include 'includes/tab_session.php';
require_once 'config/db.php';

echo "<pre>";
echo "=== DIAGNOSTIC REPORT: PENDING EVALUATION ISSUE ===\n\n";

// 1. Check evaluations table structure
echo "1. EVALUATIONS TABLE STRUCTURE:\n";
echo "---\n";
try {
    $stmt = $pdo->query("DESCRIBE evaluations");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

// 2. Check sample data in evaluations table
echo "2. SAMPLE EVALUATION RECORDS:\n";
echo "---\n";
try {
    $stmt = $pdo->query("SELECT * FROM evaluations LIMIT 5");
    $evals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($evals)) {
        echo "No evaluations in database\n\n";
    } else {
        echo "Found " . count($evals) . " evaluations\n";
        foreach ($evals as $eval) {
            echo json_encode($eval, JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

// 3. Check if subject_id column exists
echo "3. COLUMN EXISTENCE CHECK:\n";
echo "---\n";
try {
    $columns = ['subject_id', 'evaluator_id', 'student_id'];
    foreach ($columns as $col) {
        $stmt = $pdo->query("SHOW COLUMNS FROM evaluations WHERE Field = '$col'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Column '$col' EXISTS\n";
        } else {
            echo "✗ Column '$col' MISSING\n";
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

// 4. Test the pending evaluations query from evaluate.php
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    echo "4. PENDING EVALUATIONS QUERY TEST (for user $user_id):\n";
    echo "---\n";
    
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT ca.subject_id, s.subject_name, u.id as teacher_id, u.name as teacher_name
            FROM class_assignments ca
            JOIN subjects s ON ca.subject_id = s.subject_id
            JOIN users u ON ca.teacher_id = u.id
            LEFT JOIN evaluations e ON e.teacher_id = u.id AND e.evaluator_role = 'teacher' AND e.evaluator_id = ? AND e.subject_id = ca.subject_id
            WHERE ca.teacher_id != ? AND e.id IS NULL ORDER BY s.subject_name, u.name
        ");
        $stmt->execute([$user_id, $user_id]);
        $pendings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Pending evaluations: " . count($pendings) . "\n";
        foreach ($pendings as $p) {
            echo "  - " . $p['teacher_name'] . " / " . $p['subject_name'] . "\n";
        }
        echo "\n";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n\n";
    }

    // 5. Check what evaluations THIS user has already given
    echo "5. EVALUATIONS GIVEN BY USER $user_id:\n";
    echo "---\n";
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM evaluations 
            WHERE evaluator_id = ? AND evaluator_role = 'teacher'
        ");
        $stmt->execute([$user_id]);
        $given = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Evaluations given: " . count($given) . "\n";
        foreach ($given as $g) {
            echo json_encode($g, JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n\n";
    }
}

// 6. Check class_assignments
echo "6. CLASS ASSIGNMENTS (sample):\n";
echo "---\n";
try {
    $stmt = $pdo->query("SELECT * FROM class_assignments LIMIT 5");
    $assigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($assigns) . " class assignments\n";
    foreach ($assigns as $a) {
        echo json_encode($a, JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

echo "=== END OF DIAGNOSTIC REPORT ===\n";
echo "</pre>";
?>
