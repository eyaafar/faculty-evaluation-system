<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
if ($role === 'admin') {
        header('Location: admin/admin.php');
    } elseif ($role === 'student') {
        header('Location: student/dashboard.php');
    } elseif ($role === 'teacher') {
        header('Location: teacher/dashboard.php');
    }
} else {
    header('Location: login.php');
}
exit();
?>

