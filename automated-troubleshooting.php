<?php
/**
 * AUTOMATED TROUBLESHOOTING & FIX SYSTEM
 * Checks and fixes all common issues automatically
 */

echo "=== AUTOMATED TROUBLESHOOTING SYSTEM ===\n\n";

// 1. CHECK NGROK STATUS
echo "1. 🔍 CHECKING NGROK STATUS:\n";
$ngrok_running = false;
exec('tasklist /FI "IMAGENAME eq ngrok.exe" 2>NUL', $output);
foreach ($output as $line) {
    if (strpos($line, 'ngrok.exe') !== false) {
        $ngrok_running = true;
        break;
    }
}

if ($ngrok_running) {
    echo "   ✅ Ngrok is already running\n";
} else {
    echo "   ❌ Ngrok is NOT running\n";
    echo "   🚀 ATTEMPTING TO START NGROK...\n";
    
    // Try to start ngrok
    $ngrok_path = 'C:\ngrok\ngrok.exe'; // Common location
    if (file_exists($ngrok_path)) {
        echo "   📍 Found ngrok at: $ngrok_path\n";
        echo "   📝 To start manually, run: $ngrok_path http 80\n";
        echo "   📝 Or open Command Prompt and type: ngrok http 80\n";
    } else {
        echo "   ❌ Ngrok not found in common location\n";
        echo "   📝 Download ngrok from: https://ngrok.com/download\n";
        echo "   📝 Extract to C:\ngrok and run: ngrok http 80\n";
    }
}

echo "\n";

// 2. CHECK XAMPP APACHE STATUS
echo "2. 🔍 CHECKING XAMPP APACHE STATUS:\n";
$apache_running = false;
exec('tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL', $apache_output);
foreach ($apache_output as $line) {
    if (strpos($line, 'httpd.exe') !== false) {
        $apache_running = true;
        break;
    }
}

if ($apache_running) {
    echo "   ✅ XAMPP Apache is running\n";
} else {
    echo "   ❌ XAMPP Apache is NOT running\n";
    echo "   🚀 TO FIX:\n";
    echo "      1. Open XAMPP Control Panel\n";
    echo "      2. Click 'Start' next to Apache\n";
    echo "      3. Wait for green status indicator\n";
    echo "      4. Test: http://localhost/FEFS/fe-system/\n";
}

echo "\n";

// 3. CHECK API FILES LOCATION
echo "3. 🔍 CHECKING API FILES LOCATION:\n";
$required_files = [
    'teacher/api/recommendations.php',
    'teacher/api/teacher-specific-data-hybrid.php',
    'config/db.php'
];

$all_files_exist = true;
foreach ($required_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "   ✅ Found: $file\n";
    } else {
        echo "   ❌ Missing: $file\n";
        $all_files_exist = false;
    }
}

if (!$all_files_exist) {
    echo "   📝 Current directory: " . __DIR__ . "\n";
    echo "   📝 Make sure all files are in correct locations\n";
}

echo "\n";

// 4. CHECK DATABASE CONNECTION
echo "4. 🔍 CHECKING DATABASE CONNECTION:\n";
$db_config_path = __DIR__ . '/config/db.php';
if (file_exists($db_config_path)) {
    try {
        require_once $db_config_path;
        
        // Test connection
        $test_query = "SELECT COUNT(*) as count FROM teachers WHERE teacher_id = 4";
        $stmt = $pdo->query($test_query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && isset($result['count'])) {
            echo "   ✅ Database connection successful\n";
            echo "   ✅ Found {$result['count']} teachers in database\n";
        } else {
            echo "   ⚠️  Database connected but no data found\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Database connection failed\n";
        echo "   ❌ Error: " . $e->getMessage() . "\n";
        echo "   📝 Check config/db.php settings\n";
    }
} else {
    echo "   ❌ Database config file not found\n";
}

echo "\n";

// 5. CHECK EXTERNAL ACCESS
echo "5. 🔍 CHECKING EXTERNAL ACCESS:\n";
$ngrok_url = "https://yeah-treadmill-tingling.ngrok-free.dev";
$test_endpoints = [
    $ngrok_url,
    $ngrok_url . "/FEFS/fe-system/",
    $ngrok_url . "/FEFS/fe-system/teacher/api/recommendations.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id=4&format=ai"
];

foreach ($test_endpoints as $i => $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $test_names = ['Ngrok Tunnel', 'XAMPP Homepage', 'API Endpoint'];
    echo "   Testing {$test_names[$i]}: ";
    
    if ($http_code == 200) {
        echo "✅ Accessible (HTTP $http_code)\n";
    } else {
        echo "❌ Not accessible (HTTP $http_code)\n";
        if ($error) echo "      Error: $error\n";
    }
}

echo "\n";

// 6. FIREWALL CHECK
echo "6. 🔍 FIREWALL SETTINGS CHECK:\n";
echo "   📝 Manual check required:\n";
echo "   1. Open Windows Defender Firewall\n";
echo "   2. Check 'Inbound Rules' for Apache (httpd.exe)\n";
echo "   3. Ensure port 80 is allowed for Apache\n";
echo "   4. Check if ngrok.exe is allowed through firewall\n";
echo "   5. Temporarily disable firewall to test (remember to re-enable)\n";

echo "\n";

// 7. QUICK FIXES SUMMARY
echo "7. 🚀 QUICK FIXES SUMMARY:\n";
echo "   If any issues found above:\n\n";
echo "   📝 NGROK NOT RUNNING:\n";
echo "      Run: ngrok http 80\n\n";
echo "   📝 XAMPP APACHE NOT RUNNING:\n";
echo "      Open XAMPP Control Panel → Start Apache\n\n";
echo "   📝 MISSING FILES:\n";
echo "      Verify all files are in c:\\xampp\\htdocs\\FEFS\\fe-system\\\n\n";
echo "   📝 DATABASE ERRORS:\n";
echo "      Check MySQL is running in XAMPP\n";
echo "      Verify database credentials in config/db.php\n\n";
echo "   📝 FIREWALL ISSUES:\n";
echo "      Allow Apache and ngrok through Windows Firewall\n";
echo "      Test with firewall temporarily disabled\n\n";

echo "=== TROUBLESHOOTING COMPLETE ===\n";
echo "Fix any issues above, then test again!\n";
?>