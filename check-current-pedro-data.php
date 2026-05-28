<?php
// Check current database state for Dr. Pedro Reyes
require_once 'config/db.php';

echo "=== CURRENT DATABASE STATE FOR DR. PEDRO REYES ===\n\n";

// Check if evaluations table exists and has data for teacher 4
try {
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM evaluations WHERE teacher_id = ?');
    $stmt->execute([4]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Evaluations for Teacher ID 4: " . $result['count'] . " records\n\n";
    
    if ($result['count'] > 0) {
        // Show actual data
        $stmt = $pdo->prepare('
            SELECT rating, COUNT(*) as count, AVG(rating) as avg_rating 
            FROM evaluations 
            WHERE teacher_id = ? 
            GROUP BY rating 
            ORDER BY rating
        ');
        $stmt->execute([4]);
        $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Rating Distribution:\n";
        $total = 0;
        $weighted_sum = 0;
        
        foreach ($ratings as $row) {
            echo "  Rating " . $row['rating'] . ": " . $row['count'] . " evaluations\n";
            $total += $row['count'];
            $weighted_sum += $row['rating'] * $row['count'];
        }
        
        $actual_average = $total > 0 ? round($weighted_sum / $total, 1) : 0;
        
        echo "\nTotal Evaluations: $total\n";
        echo "Actual Average Rating: $actual_average\n\n";
        
        // Compare with Professor Jag's response
        echo "=== COMPARISON WITH PROFESSOR JAG ===\n";
        echo "Professor Jag said: 4.2/5 rating, 15 evaluations\n";
        echo "Actual database: $actual_average/5 rating, $total evaluations\n\n";
        
        if ($actual_average == 4.2 && $total == 15) {
            echo "✅ PERFECT MATCH! Professor Jag is using real data.\n";
        } elseif ($actual_average == 4.2) {
            echo "⚠️  PARTIAL MATCH: Rating is correct but count is wrong.\n";
            echo "   Professor Jag may be using cached or aggregated data.\n";
        } else {
            echo "❌ NO MATCH: Professor Jag is not using the current database data.\n";
            echo "   Possible reasons:\n";
            echo "   - Using cached/previous session data\n";
            echo "   - Reading from different data source\n";
            echo "   - Using default fallback values\n";
        }
    } else {
        echo "❌ NO EVALUATIONS FOUND for Dr. Pedro Reyes in database!\n";
        echo "Professor Jag must be using placeholder or cached data.\n";
    }
    
} catch (Exception $e) {
    echo "Error checking database: " . $e->getMessage() . "\n";
}
?>