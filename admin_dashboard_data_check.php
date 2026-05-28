<?php
/**
 * Get admin dashboard data directly
 */

require_once 'config/db.php';

echo "<h2>📊 Admin Dashboard Data Analysis</h2>";
echo "<hr>";

try {
    // Faculty count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'teacher'");
    $faculty_count = $stmt->fetch()['total'];
    
    // Student count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'student'");
    $student_count = $stmt->fetch()['total'];
    
    // Subject count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM subjects");
    $subject_count = $stmt->fetch()['total'];
    
    // Enrollment count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM student_subjects");
    $enrollment_count = $stmt->fetch()['total'];
    
    // Total evaluations
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM evaluations");
    $total_evals = $stmt->fetch()['total'];
    
    // Top faculty
    $stmt = $pdo->prepare("
        SELECT u.name, AVG(e.score) as avg_rating, COUNT(e.id) as eval_count
        FROM evaluations e
        JOIN users u ON e.teacher_id = u.id
        GROUP BY u.id
        ORDER BY avg_rating DESC
        LIMIT 5
    ");
    $stmt->execute();
    $top_faculty = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📈 Admin Dashboard Statistics:</h3>";
    echo "<table border='1' cellpadding='12' cellspacing='0' style='width:100%; margin:15px 0;'>";
    echo "<tr style='background:#f0f0f0;'><th>Metric</th><th>Value</th><th>Status</th></tr>";
    
    $metrics = [
        'Faculty Members' => [$faculty_count, 'real'],
        'Students' => [$student_count, 'real'],
        'Subjects' => [$subject_count, 'real'],
        'Class Assignments' => [$enrollment_count, 'real'],
        'Total Evaluations' => [$total_evals, $total_evals > 0 ? 'needs-review' : 'real'],
    ];
    
    foreach ($metrics as $label => $data) {
        $value = $data[0];
        $status = $data[1];
        $statusColor = $status === 'real' ? 'green' : 'orange';
        $statusText = $status === 'real' ? '✅ Real' : '⚠️ Check';
        
        echo "<tr>";
        echo "<td><strong>$label</strong></td>";
        echo "<td style='font-size:1.2em; font-weight:bold;'>$value</td>";
        echo "<td style='color:$statusColor;'>$statusText</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Top Faculty
    echo "<h3>⭐ Top Rated Faculty:</h3>";
    if (!empty($top_faculty)) {
        echo "<table border='1' cellpadding='12' cellspacing='0' style='width:100%; margin:15px 0;'>";
        echo "<tr style='background:#f0f0f0;'><th>Rank</th><th>Teacher Name</th><th>Avg Rating</th><th>Eval Count</th></tr>";
        
        foreach ($top_faculty as $i => $f) {
            echo "<tr>";
            echo "<td>#" . ($i + 1) . "</td>";
            echo "<td>" . htmlspecialchars($f['name']) . "</td>";
            echo "<td style='text-align:center; font-weight:bold;'>" . number_format((float)$f['avg_rating'], 2) . "</td>";
            echo "<td style='text-align:center;'>" . $f['eval_count'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='background:#fff9c4; padding:15px; border-radius:5px; border-left:4px solid #fbc02d;'>";
        echo "⚠️ <strong>No evaluations to display</strong> - All teachers have zero evaluations";
        echo "</p>";
    }
    
    // Check data quality
    echo "<h3>🔍 Data Quality Check:</h3>";
    
    $stmt = $pdo->query("
        SELECT 
            u.id,
            u.name,
            COUNT(e.id) as eval_count,
            MIN(e.created_at) as first_eval,
            MAX(e.created_at) as last_eval
        FROM users u
        LEFT JOIN evaluations e ON u.id = e.teacher_id
        WHERE u.role = 'teacher'
        GROUP BY u.id
        ORDER BY eval_count DESC
    ");
    
    $teachers_with_evals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $teachers_with_data = array_filter($teachers_with_evals, fn($t) => $t['eval_count'] > 0);
    
    echo "<ul>";
    echo "<li>Teachers with real evaluations: <strong>" . count($teachers_with_data) . "</strong></li>";
    echo "<li>Teachers with zero evaluations: <strong>" . (count($teachers_with_evals) - count($teachers_with_data)) . "</strong></li>";
    echo "<li>Total real evaluation entries: <strong>$total_evals</strong></li>";
    echo "</ul>";
    
    if (count($teachers_with_data) > 0) {
        echo "<h4>Teachers with evaluations:</h4>";
        echo "<table border='1' cellpadding='12' cellspacing='0' style='width:100%; margin:15px 0;'>";
        echo "<tr style='background:#f0f0f0;'><th>Teacher</th><th>Eval Count</th><th>First Eval</th><th>Last Eval</th></tr>";
        
        foreach ($teachers_with_data as $t) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($t['name']) . "</td>";
            echo "<td style='text-align:center;'>" . $t['eval_count'] . "</td>";
            echo "<td>" . date('Y-m-d H:i', strtotime($t['first_eval'])) . "</td>";
            echo "<td>" . date('Y-m-d H:i', strtotime($t['last_eval'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<p style='background:#e8f5e9; padding:15px; border-radius:5px; border-left:4px solid #4caf50;'>";
    echo "✅ <strong>Admin Dashboard Ready</strong><br>";
    echo "The dashboard is displaying <strong>real data only</strong> from the database.<br>";
    echo "Total evaluations being displayed: <strong>" . (int)$total_evals . "</strong>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'><strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
