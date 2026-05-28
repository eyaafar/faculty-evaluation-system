<?php
require 'config/db.php';

echo "=== Database Schema Check ===\n\n";

// Check evaluations table structure
$stmt = $pdo->prepare("DESCRIBE evaluations");
$stmt->execute();
$columns = $stmt->fetchAll();

echo "Evaluations Table Columns:\n";
foreach ($columns as $col) {
    echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
}

// Check a sample evaluation
echo "\nSample Evaluation Record:\n";
$stmt2 = $pdo->prepare("SELECT * FROM evaluations LIMIT 1");
$stmt2->execute();
$sample = $stmt2->fetch();
if ($sample) {
    foreach ($sample as $key => $value) {
        if (is_string($value) && strlen($value) > 100) {
            echo "  " . $key . ": " . substr($value, 0, 100) . "...\n";
        } else {
            echo "  " . $key . ": " . $value . "\n";
        }
    }
}
