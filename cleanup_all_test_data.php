<?php
/**
 * Remove all remaining test data - ensure admin dashboard shows ONLY real data
 */

require_once 'config/db.php';

echo "<h2>🧹 Cleaning Test Data</h2>";
echo "<hr>";

// Identify and delete test evaluations
$stmt = $pdo->query("
    SELECT 
        e.id,
        e.teacher_id,
        u.name as teacher,
        e.evaluator_id,
        e.created_at,
        e.score,
        e.feedback,
        e.evaluator_role
    FROM evaluations e
    JOIN users u ON e.teacher_id = u.id
");

$all_evals = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Found " . count($all_evals) . " Total Evaluations</h3>";
echo "<p>Analyzing each evaluation to identify test data...</p>";

$test_evals = [];
$real_evals = [];

foreach ($all_evals as $e) {
    $is_test = false;
    $reason = '';
    
    // Test data indicators:
    // 1. Unknown/null evaluator with question ID format feedback
    // 2. Score of 0 with formatted question:response feedback
    // 3. Evaluator ID is null
    
    $feedback = is_string($e['feedback']) ? json_decode($e['feedback'], true) : [];
    $feedback_str = is_array($feedback) ? implode(';', array_keys($feedback)) : '';
    
    if ($e['evaluator_id'] === null) {
        $is_test = true;
        $reason = "No evaluator (unknown source)";
    }
    
    if ($e['score'] == 0 && preg_match('/^\d+:\d+/', implode('', array_keys((array)$feedback)))) {
        $is_test = true;
        $reason = "Zero score with formatted feedback";
    }
    
    if ($is_test) {
        $test_evals[] = [
            'id' => $e['id'],
            'teacher' => $e['teacher'],
            'reason' => $reason,
            'evaluator_id' => $e['evaluator_id'],
            'score' => $e['score']
        ];
    } else {
        $real_evals[] = $e;
    }
}

echo "<h3>📊 Analysis Result:</h3>";
echo "<ul style='font-size:1.1em;'>";
echo "<li>Real Evaluations: <strong style='color:green;'>" . count($real_evals) . "</strong></li>";
echo "<li>Test Evaluations: <strong style='color:orange;'>" . count($test_evals) . "</strong></li>";
echo "</ul>";

if (!empty($test_evals)) {
    echo "<h3>⚠️ Test Evaluations Identified:</h3>";
    echo "<table border='1' cellpadding='12' cellspacing='0' style='width:100%; margin:15px 0;'>";
    echo "<tr style='background:#ffe8e8;'><th>ID</th><th>Teacher</th><th>Evaluator</th><th>Score</th><th>Reason</th></tr>";
    
    foreach ($test_evals as $t) {
        echo "<tr>";
        echo "<td><strong>" . $t['id'] . "</strong></td>";
        echo "<td>" . htmlspecialchars($t['teacher']) . "</td>";
        echo "<td>" . ($t['evaluator_id'] ? $t['evaluator_id'] : '(NULL)') . "</td>";
        echo "<td>" . $t['score'] . "</td>";
        echo "<td>" . htmlspecialchars($t['reason']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color:red;'>🗑️ Deleting Test Evaluations...</h3>";
    
    try {
        foreach ($test_evals as $t) {
            $stmt = $pdo->prepare("DELETE FROM evaluations WHERE id = ?");
            $stmt->execute([$t['id']]);
            echo "<p style='color:green;'>✅ Deleted evaluation ID " . $t['id'] . " (" . htmlspecialchars($t['teacher']) . ")</p>";
        }
        
        // Verify deletion
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM evaluations");
        $remaining = $stmt->fetch()['count'];
        
        echo "<hr>";
        echo "<p style='background:#e8f5e9; padding:15px; border-radius:5px; border-left:4px solid #4caf50; font-size:1.1em;'>";
        echo "✅ <strong>Cleanup Complete!</strong><br>";
        echo "Deleted: " . count($test_evals) . " test evaluations<br>";
        echo "Remaining: <strong>" . $remaining . "</strong> real evaluations<br>";
        echo "Admin dashboard will now show only real data.";
        echo "</p>";
        
    } catch (Exception $e) {
        echo "<p style='color:red;'><strong>❌ Error during deletion:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} else {
    echo "<hr>";
    echo "<p style='background:#e8f5e9; padding:15px; border-radius:5px; border-left:4px solid #4caf50; font-size:1.1em;'>";
    echo "✅ <strong>Database is Clean!</strong><br>";
    echo "No test data found. All " . count($real_evals) . " evaluations appear to be real data.<br>";
    echo "Admin dashboard is showing accurate information.";
    echo "</p>";
}
?>
