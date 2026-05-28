<?php
/**
 * Check current state of database - what test data exists?
 */

require_once 'config/db.php';

echo "<h2>📊 Current Database State</h2>";
echo "<hr>";

// Check total evaluations
$stmt = $pdo->query("SELECT COUNT(*) as count FROM evaluations");
$total = $stmt->fetch()['count'];
echo "<p><strong>Total Evaluations in Database:</strong> <strong style='color:#00d4ff; font-size:1.2em;'>$total</strong></p>";

// Check evaluations by teacher
echo "<h3>Evaluations by Teacher:</h3>";
$stmt = $pdo->query("
    SELECT 
        u.id, 
        u.name, 
        COUNT(e.id) as eval_count,
        MIN(e.created_at) as first_date,
        MAX(e.created_at) as last_date,
        GROUP_CONCAT(e.id) as eval_ids
    FROM users u
    LEFT JOIN evaluations e ON u.id = e.teacher_id
    WHERE u.role = 'teacher'
    GROUP BY u.id
    ORDER BY eval_count DESC
");
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='12' cellspacing='0' style='width:100%; margin:15px 0; border-collapse:collapse;'>";
echo "<tr style='background:#f0f0f0;'><th>Teacher ID</th><th>Name</th><th>Eval Count</th><th>First Eval</th><th>Last Eval</th><th>Eval IDs</th></tr>";

foreach ($teachers as $t) {
    $bgColor = $t['eval_count'] > 0 ? '#fff5e6' : '#f0f0f0';
    echo "<tr style='background:$bgColor;'>";
    echo "<td>" . $t['id'] . "</td>";
    echo "<td>" . htmlspecialchars($t['name']) . "</td>";
    echo "<td style='text-align:center; font-weight:bold; color:" . ($t['eval_count'] > 5 ? 'orange' : 'green') . ";'>" . $t['eval_count'] . "</td>";
    echo "<td>" . ($t['first_date'] ? date('Y-m-d H:i', strtotime($t['first_date'])) : 'N/A') . "</td>";
    echo "<td>" . ($t['last_date'] ? date('Y-m-d H:i', strtotime($t['last_date'])) : 'N/A') . "</td>";
    echo "<td><small>" . ($t['eval_ids'] ? $t['eval_ids'] : 'None') . "</small></td>";
    echo "</tr>";
}
echo "</table>";

// Check for suspicious patterns (likely test data)
echo "<h3>⚠️ Checking for Suspicious Patterns:</h3>";

$stmt = $pdo->query("
    SELECT 
        e.id,
        e.teacher_id,
        u.name as teacher,
        e.evaluator_id,
        eu.name as evaluator,
        e.created_at,
        e.score,
        e.feedback
    FROM evaluations e
    JOIN users u ON e.teacher_id = u.id
    LEFT JOIN users eu ON e.evaluator_id = eu.id
    ORDER BY e.created_at
");

$evals = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($evals)) {
    echo "<p style='color:green;'>✅ No evaluations found - database is clean!</p>";
} else {
    echo "<table border='1' cellpadding='12' cellspacing='0' style='width:100%; margin:15px 0; border-collapse:collapse; font-size:0.9em;'>";
    echo "<tr style='background:#f0f0f0;'><th>Eval ID</th><th>Teacher</th><th>Evaluator</th><th>Date</th><th>Score</th><th>Feedback</th></tr>";
    
    foreach ($evals as $e) {
        $feedback = is_string($e['feedback']) ? json_decode($e['feedback'], true) : $e['feedback'];
        $feedback_text = is_array($feedback) ? implode('; ', array_map(fn($k, $v) => "$k:$v", array_keys($feedback), array_values($feedback))) : 'N/A';
        if (strlen($feedback_text) > 100) $feedback_text = substr($feedback_text, 0, 100) . '...';
        
        echo "<tr>";
        echo "<td>" . $e['id'] . "</td>";
        echo "<td>" . htmlspecialchars($e['teacher']) . "</td>";
        echo "<td>" . htmlspecialchars($e['evaluator'] ?? 'Unknown') . "</td>";
        echo "<td>" . date('Y-m-d H:i', strtotime($e['created_at'])) . "</td>";
        echo "<td style='text-align:center;'>" . $e['score'] . "</td>";
        echo "<td><small>" . htmlspecialchars($feedback_text) . "</small></td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<p style='background:#e8f5e9; padding:15px; border-radius:5px; border-left:4px solid #4caf50;'>";
echo "✅ <strong>Summary:</strong> Database analysis complete.<br>";
if ($total === 0) {
    echo "Database is clean - no test or real evaluations.";
} else {
    echo "Total evaluations: <strong>$total</strong> - Review suspicious entries above.";
}
echo "</p>";
?>
