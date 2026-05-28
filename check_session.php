<?php
// Simple session state checker for debugging teacher switching

// Use standard session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>Current Session State</h1>";
echo "<h2>Session Data:</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h2>Session Details:</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";

echo "<h2>Cookies:</h2>";
echo "<pre>" . print_r($_COOKIE, true) . "</pre>";

echo "<h2>Test Actions:</h2>";
echo "<p><a href='?clear_session=1'>Clear Session</a></p>";
echo "<p><a href='?check_login=1'>Check Login Status</a></p>";

// Clear session if requested
if (isset($_GET['clear_session'])) {
    session_destroy();
    session_start();
    echo "<p><strong>Session cleared!</strong></p>";
    echo "<script>setTimeout(function(){ window.location.href = window.location.pathname; }, 2000);</script>";
}

// Check if user is logged in
if (isset($_GET['check_login'])) {
    if (isset($_SESSION['user_id'])) {
        echo "<p><strong>✓ User is logged in:</strong> " . htmlspecialchars($_SESSION['name'] ?? 'Unknown') . " (ID: " . $_SESSION['user_id'] . ")</p>";
    } else {
        echo "<p><strong>✗ No user logged in</strong></p>";
    }
}

echo "<hr>";
echo "<p><a href='comprehensive_test.php'>Go to Comprehensive Test</a></p>";
echo "<p><a href='debug_session.php'>Go to Debug Session</a></p>";
?>