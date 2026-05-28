<?php
// Quick check of Dr. Pedro Reyes' actual database data
require_once 'config/db.php';

echo "=== DR. PEDRO REYES ACTUAL DATABASE DATA ===\n";
echo "Teacher ID: 4\n\n";

// Get real evaluation data
$stmt = $pdo->prepare('SELECT COUNT(*) as total_count, AVG(rating) as avg_rating FROM evaluations WHERE teacher_id = ?');
$stmt->execute([4]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "REAL DATABASE RESULTS:\n";
echo "- Total Evaluations: " . $result['total_count'] . "\n";
echo "- Average Rating: " . round($result['avg_rating'], 1) . "\n\n";

// Compare with what Professor Jag said
echo "PROFESSOR JAG SAID:\n";
echo "- Rating: 4.2/5\n";
echo "- Evaluations: 15\n\n";

// Check if they match
$rating_match = (round($result['avg_rating'], 1) == 4.2);
$eval_match = ($result['total_count'] == 15);

echo "VERIFICATION:\n";
echo "- Rating matches: " . ($rating_match ? "✅ YES" : "❌ NO") . "\n";
echo "- Evaluations match: " . ($eval_match ? "✅ YES" : "❌ NO") . "\n";

if ($rating_match && $eval_match) {
    echo "\n🎉 CONFIRMED: Professor Jag is using Dr. Pedro Reyes' REAL data!\n";
} else {
    echo "\n⚠️  WARNING: Data mismatch detected!\n";
    echo "Professor Jag may be using placeholder or cached data.\n";
}
?>