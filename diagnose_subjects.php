<?php
require_once 'config/db.php';
echo "<h2>DB Diagnosis for Subjects Student Count</h2>";
$user_id = $_GET['teacher_id'] ?? 0;
echo "<p>Teacher ID: $user_id</p>";

// 1. Users Pedro
echo '<h3>Pedro Reyes users:</h3>';
$stmt = $pdo->query("SELECT id, name FROM users WHERE name LIKE '%Pedro%' OR name LIKE '%Reyes'");
while ($row = $stmt->fetch()) echo "ID: {$row['id']} Name: {$row['name']}<br>";

// 2. Farhiya
echo '<h3>Farhiya users:</h3>';
$stmt = $pdo->query("SELECT id, name FROM users WHERE name LIKE '%Farhiya%' OR name LIKE '%Ayyub%'");
while ($row = $stmt->fetch()) echo "ID: {$row['id']} Name: {$row['name']}<br>";

// 3. Tables
echo '<h3>Tables:</h3>';
$stmt = $pdo->query("SHOW TABLES LIKE '%assign%'");
while ($row = $stmt->fetch()) echo 'assign table: ' . print_r($row, true) . '<br>';
$stmt = $pdo->query("SHOW TABLES LIKE '%student%'");
while ($row = $stmt->fetch()) echo 'student table: ' . print_r($row, true) . '<br>";

// 4. class_assignments
echo '<h3>class_assignments sample:</h3>';
$stmt = $pdo->query('SELECT * FROM class_assignments LIMIT 5');
while ($row = $stmt->fetch()) echo print_r($row, true) . '<br>';

// 5. student_subjects sample
echo '<h3>student_subjects sample:</h3>';
$stmt = $pdo->query('SELECT * FROM student_subjects LIMIT 5');
while ($row = $stmt->fetch()) echo print_r($row, true) . '<br>';

// 6. My subjects query simulation
echo '<h3>My subjects query for teacher $user_id:</h3>';
$stmt = $pdo->prepare("
    SELECT ca.id as class_id, s.subject_id, s.subject_name, 
           ca.course, ca.year_level, ca.section, ca.semester,
           COUNT(ss.id) as student_count
    FROM class_assignments ca
    JOIN subjects s ON ca.subject_id = s.subject_id
    LEFT JOIN student_subjects ss ON ss.subject_id = ca.subject_id
    WHERE ca.teacher_id = ?
    GROUP BY ca.id, s.subject_id, s.subject_name, ca.course, ca.year_level, ca.section, ca.semester
");
$stmt->execute([$user_id]);
while ($row = $stmt->fetch()) {
    echo print_r($row, true) . '<br>';
}

echo '<p><a href=\"diagnose_subjects.php\">Reload</a></p>';
?>

