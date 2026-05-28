<?php
require_once 'includes/db.php';

try {
    echo "=== EVALUATION DATA INSPECTION ===\n\n";
    
    // Get ALL raw evaluation data
    echo "ALL EVALUATIONS:\n";
    $stmt = $pdo->query("SELECT * FROM evaluations");
    $all_evals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total: " . count($all_evals) . "\n\n";
    
    foreach ($all_evals as $i => $e) {
        echo "Record " . ($i + 1) . ":\n";
        foreach ($e as $key => $val) {
            $display = $val === null ? 'NULL' : (is_string($val) ? "'{$val}'" : $val);
            echo "  $key: $display\n";
        }
        echo "\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "IMPORTANT FIELDS TO CHECK:\n\n";
    
    // Check the score vs rating columns
    echo "Score Column (int):\n";
    $stmt = $pdo->query("SELECT teacher_id, score FROM evaluations");
    $scores = $stmt->fetchAll();
    foreach ($scores as $s) {
        echo "  Teacher ID {$s['teacher_id']}: score = " . ($s['score'] === null ? 'NULL' : $s['score']) . "\n";
    }
    
    echo "\nRating Column (decimal):\n";
    $stmt = $pdo->query("SELECT teacher_id, rating FROM evaluations");
    $ratings = $stmt->fetchAll();
    foreach ($ratings as $r) {
        echo "  Teacher ID {$r['teacher_id']}: rating = " . ($r['rating'] === null ? 'NULL' : $r['rating']) . "\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "ISSUE: The query uses 'score' but data might be in 'rating' column!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
