<?php
session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html>
<head><title>Student History Diagnosis</title></head>
<body>
<h1>Student History Diagnosis</h1>
<?php if (!isset($_SESSION['user_id'])): ?>
<p><strong>❌ Not logged in.</strong> <a href="login.php">Login as student</a></p>
<?php else: ?>
<h2>Session Info:</h2>
<ul>
<li>User ID: <?= $_SESSION['user_id'] ?></li>
<li>Name: <?= htmlspecialchars($_SESSION['name'] ?? 'MISSING') ?></li>
<li>Role: <?= $_SESSION['role'] ?? 'MISSING' ?></li>
</ul>

<h2>Evaluations for this student:</h2>
<?php
$stmt = $pdo->prepare("
SELECT e.*, s.subject_name, u.name as teacher_name 
FROM evaluations e 
JOIN subjects s ON e.subject_id = s.subject_id 
JOIN users u ON e.teacher_id = u.id 
WHERE e.student_id = ? ORDER BY date_submitted DESC LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll();
if ($history): ?>
<table border="1">
<tr><th>ID</th><th>Subject</th><th>Teacher</th><th>Rating</th><th>Date</th></tr>
<?php foreach($history as $h): ?>
<tr>
<td><?= $h['evaluation_id'] ?></td>
<td><?= htmlspecialchars($h['subject_name']) ?></td>
<td><?= htmlspecialchars($h['teacher_name']) ?></td>
<td><?= $h['rating'] ?></td>
<td><?= $h['date_submitted'] ?></td>
</tr>
<?php endforeach; ?>
</table>
<p><strong>✅ <?= count($history) ?> evaluations found. History.php should work!</strong></p>
<?php else: ?>
<p><strong>❌ No evaluations for student_id = <?= $_SESSION['user_id'] ?>. Run SQL fixes.</strong></p>
<?php endif; ?>

<h2>All Recent Student Evaluations:</h2>
<?php
$stmt = $pdo->query("SELECT e.student_id, u.name, COUNT(*) as count FROM evaluations e JOIN users u ON e.student_id = u.id WHERE e.evaluator_role='student' GROUP BY e.student_id ORDER BY count DESC LIMIT 10");
$all = $stmt->fetchAll();
echo '<ul>';
foreach($all as $a) echo "<li>ID $a[student_id] ({$a['name']}): {$a['count']} evals</li>";
echo '</ul>';
?>

<p><a href="student/history.php">→ Test history.php</a></p>
<?php endif; ?>
</body></html>
