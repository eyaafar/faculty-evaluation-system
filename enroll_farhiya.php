<?php
require_once 'config/db.php';

echo "<h2>Enroll Farhiya to Pedro's subject</h2>";

$stmt = $pdo->prepare("INSERT IGNORE INTO student_subjects (student_id, subject_id) VALUES (?, ?)");
$result = $stmt->execute([16, 1]);
if ($result) {
    echo "Success: Farhiya (16) enrolled in subject 1.<br>";
} else {
    echo "Insert failed or already exists.<br>";
}

// Verify
echo "<h3>Verify:</h3>";
$stmt = $pdo->prepare("SELECT u.name as student, s.subject_name FROM student_subjects ss JOIN users u ON ss.student_id = u.id JOIN subjects s ON ss.subject_id = s.subject_id WHERE ss.student_id = ?");
$stmt->execute([16]);
while ($row = $stmt->fetch()) {
    echo $row['student'] . ' - ' . $row['subject_name'] . '<br>';
}

// Pedro count
echo "<h3>Pedro student count:</h3>";
$stmt = $pdo->prepare("SELECT s.subject_name, COUNT(ss.id) as count FROM class_assignments ca JOIN subjects s ON ca.subject_id = s.subject_id LEFT JOIN student_subjects ss ON ss.subject_id = ca.subject_id WHERE ca.teacher_id = 4 GROUP BY ca.id");
$stmt->execute();
while ($row = $stmt->fetch()) {
    echo $row['subject_name'] . ': ' . $row['count'] . ' students<br>';
}

echo '<p><a href="../teacher/my_subjects.php" target="_blank">Test my_subjects.php</a> | <a href="diagnose_subjects_fixed.php?teacher_id=4">Diagnostic</a></p>';
?>

