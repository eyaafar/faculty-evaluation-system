<?php
require_once 'config/db.php';

echo "<h1>Create Test Student Account</h1>";

// Create a test student with a known password
$test_id_number = '9999';
$test_username = 'teststudent';
$test_password = 'test123';
$test_name = 'Test Student';

// Hash the password
$hashed_password = password_hash($test_password, PASSWORD_DEFAULT);

try {
    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id_number = ? OR username = ?");
    $stmt->execute([$test_id_number, $test_username]);
    if ($stmt->fetch()) {
        echo "<p style='color:orange'>⚠️ User already exists! Using existing account...</p>";
    } else {
        // Insert new student
        $stmt = $pdo->prepare("INSERT INTO users (id_number, name, username, password, role, course, year_level, section) VALUES (?, ?, ?, ?, 'student', 'BSCS', 1, 'A')");
        $stmt->execute([$test_id_number, $test_name, $test_username, $hashed_password]);
        echo "<p style='color:green'>✅ Test student created successfully!</p>";
    }
    
    echo "<h2>Login Credentials:</h2>";
    echo "<p><strong>ID Number:</strong> $test_id_number</p>";
    echo "<p><strong>Username:</strong> $test_username</p>";
    echo "<p><strong>Password:</strong> $test_password</p>";
    
    echo "<p><a href='login.php' style='background:#00d4ff;color:#04080f;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;'>Try Login Now</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
