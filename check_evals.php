<?php
session_start();
require_once 'config/db.php';

echo "<h2>Evaluation Debug Tool</h2>";

// All teachers
$stmt = $pdo->query("SELECT id, name FROM users WHERE role='teacher' ORDER BY name");
$teachers = $stmt->fetchAll();
echo "<h3>Teachers:</h3><ul>";
foreach ($teachers as $t) {
    echo "<li>ID {$t['id']}: {$t['name']}</li>";
}
echo "</ul>";

// Evals per teacher
$stmt = $pdo->query("SELECT teacher_id, COUNT(*) as count, GROUP_CONCAT(DISTINCT student_id) as students FROM evaluations GROUP BY teacher_id ORDER BY teacher_id");
$evals = $stmt->fetchAll();
echo "<h3>Evaluations per Teacher:</h3><table border='1'><tr><th>Teacher ID</th><th>Count</th><th>Students</th></tr>";
foreach ($evals as $e) {
    $teacher = array_filter($teachers, fn($t) => $t['id'] == $e['teacher_id'])[0] ?? ['name' => 'UNKNOWN'];
    echo "<tr><td>{$e['teacher_id']} ({$teacher['name']})</td><td>{$e['count']}</td><td>" . ($e['students'] ?: 'None') . "</td></tr>";
}
echo "</table>";

// Recent evals
echo "<h3>Recent 10 Evaluations:</h3>";
$stmt = $pdo->query("SELECT * FROM evaluations ORDER BY date_submitted DESC LIMIT 10");
foreach ($stmt as $row) {
    $teacher = array_filter($teachers, fn($t) => $t['id'] == $row['teacher_id'])[0] ?? ['name' => 'UNKNOWN'];
    echo "<p>ID {$row['evaluation_id']}: Teacher {$teacher['name']} ({$row['teacher_id']}), Rating {$row['rating']}, Date " . $row['date_submitted'] . "</p>";
}

echo "<hr>
    <a href='?clear_all_except_pedro' style='background:#ef4444;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;'>🗑️ Clear ALL except Dr. Pedro</a> |
    <a href='?clear_all' style='background:#f59e0b;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;'>💥 Clear ALL Evaluations</a> |
    <a href='login.php'>← Back</a>
    <p><small>Run check_evals.php first to see current data, then use buttons to clean.</small></p>";


if (isset($_GET['clear_all_except_pedro'])) {
    $pedro_id = $pdo->query("SELECT id FROM users WHERE name LIKE '%Pedro%'")->fetchColumn();
    if ($pedro_id) {
        $stmt = $pdo->prepare("DELETE FROM evaluations WHERE teacher_id != ?");
        $stmt->execute([$pedro_id]);
        echo "<p style='color:green'>✅ Cleared ALL evaluations except Dr. Pedro Reyes (ID: $pedro_id)!</p>";
    } else {
        echo "<p style='color:red'>Dr. Pedro not found!</p>";
    }
    // Reload
    echo "<script>location.href='check_evals.php';</script>";
} elseif (isset($_GET['clear_all'])) {
    $pdo->exec("DELETE FROM evaluations");
    echo "<p style='color:orange'>🧹 Cleared ALL evaluations!</p>";
    echo "<script>location.href='check_evals.php';</script>";
}
?>

