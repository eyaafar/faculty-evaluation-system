<?php
require_once 'includes/db.php';

try {
    echo "=== DETAILED EVALUATION DATA INSPECTION ===\n\n";
    
    // Check evaluation table structure
    echo "1. EVALUATION TABLE SCHEMA:\n";
    $stmt = $pdo->query("DESCRIBE evaluations");
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        echo "   - {$col['Field']} ({$col['Type']})\n";
    }
    
    echo "\n2. ALL EVALUATIONS IN DATABASE:\n";
    $stmt = $pdo->query("SELECT * FROM evaluations");
    $all_evals = $stmt->fetchAll();
    
    echo "Total evaluations: " . count($all_evals) . "\n";
    foreach ($all_evals as $e) {
        $teacher = $pdo->query("SELECT name FROM users WHERE id = " . $e['teacher_id'])->fetch();
        $evaluator = $pdo->query("SELECT name FROM users WHERE id = " . $e['evaluator_id'])->fetch();
        
        echo "\n  Eval ID: {$e['id']}\n";
        echo "    Teacher: " . ($teacher ? $teacher['name'] : 'N/A') . " (ID: {$e['teacher_id']})\n";
        echo "    Evaluator: " . ($evaluator ? $evaluator['name'] : 'NULL/Empty') . " (ID: {$e['evaluator_id']})\n";
        echo "    Score: " . ($e['score'] === null ? 'NULL' : $e['score']) . "\n";
        echo "    Response Data Exists: " . (!empty($e['response_data']) ? 'YES' : 'NO') . "\n";
        
        if (!empty($e['response_data'])) {
            echo "    Response Data: " . substr($e['response_data'], 0, 100) . "...\n";
        }
    }
    
    echo "\n3. DR. PEDRO SPECIFIC ANALYSIS:\n";
    $stmt = $pdo->query("SELECT id FROM users WHERE name LIKE '%Pedro%' LIMIT 1");
    $pedro = $stmt->fetch();
    
    if ($pedro) {
        $stmt = $pdo->prepare("SELECT * FROM evaluations WHERE teacher_id = ?");
        $stmt->execute([$pedro['id']]);
        $pedro_evals = $stmt->fetchAll();
        
        echo "Dr. Pedro (ID: {$pedro['id']}) has " . count($pedro_evals) . " evaluations\n";
        
        foreach ($pedro_evals as $e) {
            echo "\n  Evaluation ID: {$e['id']}\n";
            echo "    Score Field Value: '" . $e['score'] . "' (Type: " . gettype($e['score']) . ")\n";
            echo "    Evaluator ID: " . ($e['evaluator_id'] === null ? 'NULL' : "'{$e['evaluator_id']}'") . "\n";
            echo "    Response Data: " . substr($e['response_data'], 0, 150) . "\n";
            
            // Try to parse response data if JSON
            if (!empty($e['response_data'])) {
                $decoded = json_decode($e['response_data'], true);
                if ($decoded) {
                    echo "    Parsed Response: \n";
                    foreach ($decoded as $key => $val) {
                        echo "      - $key: $val\n";
                    }
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
?>
