<?php
/**
 * Debug page to show what data is being injected into feedback.php for JotForm
 */
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$viewer = 'student';

// This is the EXACT query that system-data.php would run
$where = " WHERE e.teacher_id = ? ";
$params = [$teacher_id];
$where .= " AND (e.evaluator_role = 'student' OR e.evaluator_role IS NULL)";

$sql = "SELECT
    COUNT(DISTINCT e.id) as total_evaluations,
    COALESCE(AVG(e.rating), 0) as overall_rating,
    COUNT(DISTINCT e.evaluator_id) as total_respondents,
    MIN(e.created_at) as first_evaluation_date,
    MAX(e.created_at) as last_evaluation_date
FROM evaluations e" . $where;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$metrics = $stmt->fetch(PDO::FETCH_ASSOC);

// Get teacher info
$stmt2 = $pdo->prepare("SELECT id, name FROM users WHERE id = ?");
$stmt2->execute([$teacher_id]);
$teacher = $stmt2->fetch();

?>
<!DOCTYPE html>
<html>
<head>
    <title>JotForm Data Debug - <?php echo $teacher['name']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #1a1a1a; color: #fff; }
        h1 { color: #4CAF50; }
        .section { background: #2a2a2a; padding: 15px; margin: 15px 0; border-left: 4px solid #4CAF50; }
        code { background: #333; padding: 10px; display: block; white-space: pre-wrap; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #444; padding: 8px; text-align: left; }
        th { background: #4CAF50; color: black; }
        .number { color: #FFD700; font-weight: bold; }
    </style>
</head>
<body>
    <h1>JotForm Data Debug Dashboard</h1>
    
    <div class="section">
        <h2>Logged In Teacher</h2>
        <p><strong>Name:</strong> <?php echo $teacher['name']; ?></p>
        <p><strong>Teacher ID:</strong> <span class="number"><?php echo $teacher['id']; ?></span></p>
        <p><strong>Session Role:</strong> <?php echo $_SESSION['role']; ?></p>
    </div>

    <div class="section">
        <h2>What system-data.php Returns for This Teacher</h2>
        <p>This is the EXACT data that JotForm receives:</p>
        <table>
            <tr>
                <th>Metric</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Total Evaluations</td>
                <td><span class="number"><?php echo $metrics['total_evaluations']; ?></span></td>
            </tr>
            <tr>
                <td>Overall Rating</td>
                <td><?php echo round($metrics['overall_rating'], 2); ?></td>
            </tr>
            <tr>
                <td>Total Respondents</td>
                <td><?php echo $metrics['total_respondents']; ?></td>
            </tr>
            <tr>
                <td>First Evaluation</td>
                <td><?php echo $metrics['first_evaluation_date'] ?? 'N/A'; ?></td>
            </tr>
            <tr>
                <td>Last Evaluation</td>
                <td><?php echo $metrics['last_evaluation_date'] ?? 'N/A'; ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Raw JSON Response</h2>
        <p>This is what the API returns (truncated):</p>
        <code>{
  "success": true,
  "teacherId": <?php echo $teacher_id; ?>,
  "metrics": {
    "total_evaluations": <span class="number"><?php echo $metrics['total_evaluations']; ?></span>,
    "overall_rating": <?php echo round($metrics['overall_rating'], 2); ?>,
    "total_respondents": <?php echo $metrics['total_respondents']; ?>,
    ...
  }
}</code>
    </div>

    <div class="section">
        <h2>All Evaluations for This Teacher</h2>
        <?php
        $stmt3 = $pdo->prepare("
            SELECT 
                e.id,
                e.evaluator_id,
                u.name as evaluator_name,
                e.rating,
                e.evaluator_role,
                e.created_at
            FROM evaluations e
            LEFT JOIN users u ON u.id = e.evaluator_id
            WHERE e.teacher_id = ?
            ORDER BY e.created_at DESC
        ");
        $stmt3->execute([$teacher_id]);
        $evals = $stmt3->fetchAll();
        ?>
        <p>Total: <span class="number"><?php echo count($evals); ?></span> evaluations</p>
        <?php if (count($evals) > 0): ?>
        <table>
            <tr>
                <th>Eval ID</th>
                <th>Evaluator</th>
                <th>Role</th>
                <th>Rating</th>
                <th>Date</th>
            </tr>
            <?php foreach ($evals as $e): ?>
            <tr>
                <td><?php echo $e['id']; ?></td>
                <td><?php echo $e['evaluator_name'] ?? 'Unknown'; ?></td>
                <td><?php echo $e['evaluator_role']; ?></td>
                <td><?php echo $e['rating']; ?></td>
                <td><?php echo substr($e['created_at'], 0, 10); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
        <p style="color: #FFD700;">No evaluations found for this teacher.</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>⚠️ If You See "20 Evaluations" in JotForm AI</h2>
        <p>This debug page shows the ACTUAL data. If JotForm is showing a different number, it means:</p>
        <ul>
            <li>❌ JotForm is not reading the injected data correctly</li>
            <li>❌ JotForm has cached old data from a previous session</li>
            <li>❌ JotForm embed is using hardcoded data instead of dynamic data</li>
            <li>✅ This page shows the CORRECT number</li>
        </ul>
    </div>

    <div class="section">
        <p style="text-align: center; margin-top: 30px;">
            <a href="teacher/feedback.php" style="color: #4CAF50; text-decoration: none; font-weight: bold;">← Go to Feedback Page</a>
        </p>
    </div>
</body>
</html>
