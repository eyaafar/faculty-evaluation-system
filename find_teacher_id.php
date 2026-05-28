<?php
// Quick script to find teacher ID
require_once 'config/db.php';

// First check table structure
try {
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'teacher' LIMIT 1");
    $teacher = $stmt->fetch();
    
    if ($teacher) {
        echo "=== TEACHERS IN SYSTEM ===\n";
        foreach ($teacher as $key => $value) {
            echo "Column: $key\n";
        }
        echo "\nFirst Teacher:\n";
        foreach ($teacher as $key => $value) {
            echo "$key: $value\n";
        }
    } else {
        echo "No teachers found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
