<?php
require_once 'config/db.php';
echo "<h2>DB Diagnosis for Subjects Student Count</h2>";
$user_id = $_GET['teacher_id'] ?? 0;
echo "<p>Teacher ID for query: $user_id</p>";

// 1. Pedro users
echo '<h3>Pedro Reyes / Dr. Pedro users:</h3>';
$stmt = $pdo->query("SELECT id, name FROM users WHERE name LIKE '%Pedro%' OR name LIKE '%Reyes%'");
while ($row = $stmt->fetch()) {
    echo "ID: " . $row['id'] . ' Name: ' . $row['name'] . '<br>';
}

// 2. Farhiya users
echo '<h3>Farhiya Ayyub users:</h3>';
$stmt = $pdo->query("SELECT id, name FROM users WHERE name LIKE '%Farhiya%' OR name LIKE '%Ayyub%'");
while ($row = $stmt->fetch()) {
    echo "ID: " . $row['id'] . ' Name: ' . $row['name'] . '<br>';
}

// 3. Relevant tables
echo '<h3>Assignment tables:</h3>';
$stmt = $pdo->query("SHOW TABLES LIKE '%assign%'");
while ($row = $stmt->fetch()) {
    echo "Table: " . print_r($row, true) . '<br>';
}
echo '<h3>Student tables:</h3>';
$stmt = $pdo->query("SHOW TABLES LIKE '%student%'");
while ($row = $stmt->fetch()) {
    echo "Table: " . print_r($row, true) . '<br>';
}

// 4. class_assignments DESC LIMIT 5
echo '<h3>class_assignments recent:</h3>';
$stmt = $pdo->query('SELECT * FROM class_assignments ORDER BY id DESC LIMIT 5');
if ($stmt) {
    while ($row = $stmt->fetch()) {
        echo '<pre>' . print_r($row, true) . '</pre>';
    }
} else {
    echo 'Table not found or error.<br>';
}

// 5. student_subjects LIMIT 5
echo '<h3>student_subjects sample:</h3>';
$stmt = $pdo->query('SELECT * FROM student_subjects LIMIT 5');
if ($stmt) {
    while ($row = $stmt->fetch()) {
        echo '<pre>' . print_r($row, true) . '</pre>';
    }
} else {
    echo 'Table not found or error.<br>';
}

// 6. Exact my_subjects query
echo '<h3>My Subjects query results for teacher_id = $user_id:</h3>';
$stmt = $pdo->prepare("
    SELECT ca.id as class_id, s.subject_id, s.subject_name, 
           ca.course, ca.year_level, ca.section, ca.semester,
           COUNT(ss.id) as student_count
    FROM class_assignments ca
    JOIN subjects s ON ca.subject_id = s.subject_id
    LEFT JOIN student_subjects ss ON ss.subject_id = ca.subject_id
    WHERE ca.teacher_id = ?
    GROUP BY ca.id, s.subject_id, s.subject_name, ca.course, ca.year_level, ca.section, ca.semester
    ORDER BY s.subject_name, ca.section
");
$stmt->execute([$user_id]);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($subjects)) {
    echo 'No results - this is why 0 students or no cards shown.<br>';
} else {
    foreach ($subjects as $subject) {
        echo '<pre>' . print_r($subject, true) . '</pre>';
    }
}

echo '<p><a href="diagnose_subjects_fixed.php">Reload</a> | Try ?teacher_id=9 (sample Pedro id) | <a href="../teacher/my_subjects.php" target="_blank">Test my_subjects.php</a></p>';
?>

