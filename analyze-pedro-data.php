<?php
// Calculate Dr. Pedro Reyes' actual data from the sample SQL
// Based on setup_feedback_data.sql:
// Ratings: 5, 4, 4, 3, 5 = Total: 21 / 5 evaluations = 4.2 average

echo "=== DR. PEDRO REYES ACTUAL DATA ANALYSIS ===\n\n";

// From setup_feedback_data.sql:
$ratings = [5, 4, 4, 3, 5];  // The actual ratings inserted
echo "Sample ratings from database setup: " . implode(', ', $ratings) . "\n";

$total_evaluations = count($ratings);
$average_rating = array_sum($ratings) / $total_evaluations;

echo "Total evaluations: $total_evaluations\n";
echo "Average rating: " . round($average_rating, 1) . "\n\n";

echo "=== COMPARISON WITH PROFESSOR JAG'S RESPONSE ===\n";
echo "Professor Jag said: 'Your average rating is 4.2 out of 5'\n";
echo "Professor Jag said: 'You currently have a total of 15 student evaluations'\n\n";

echo "=== VERIFICATION ===\n";
$rating_match = (round($average_rating, 1) == 4.2);
$count_match = ($total_evaluations == 15);

echo "Rating 4.2 matches: " . ($rating_match ? "✅ YES" : "❌ NO") . "\n";
echo "Count 15 matches: " . ($count_match ? "✅ YES" : "❌ NO (actual: $total_evaluations)") . "\n\n";

if ($rating_match && $count_match) {
    echo "🎉 PERFECT MATCH! Professor Jag is using Dr. Pedro Reyes' REAL data!\n";
} else {
    echo "⚠️  PARTIAL MATCH DETECTED:\n";
    echo "   - Rating is correct (4.2/5)\n";
    echo "   - But evaluation count is wrong (Jag said 15, actual is $total_evaluations)\n";
    echo "\n   This suggests Professor Jag might be:\n";
    echo "   - Using cached data from a different session\n";
    echo "   - Reading from a different data source\n";
    echo "   - Using default fallback values\n";
}

echo "\n=== CONCLUSION ===\n";
echo "The rating (4.2) is accurate, but the evaluation count (15) appears to be incorrect.\n";
echo "Professor Jag is likely using Dr. Pedro Reyes' real rating data, but may have wrong evaluation count.\n";
?>