<?php
require_once 'config/db.php';

try {
    $stmt = $pdo->query("SELECT id, id_number, name, role FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total Users: " . count($users) . "\n\n";
    foreach ($users as $u) {
        echo "ID: {$u['id']}, ID#: {$u['id_number']}, Name: {$u['name']}, Role: {$u['role']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
