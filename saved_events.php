<?php
// This page redirects the logged-in user to the correct saved-events view.
// Admins are sent to the admin page, while students are sent to their saved events list.
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (($_SESSION['user_role'] ?? '') === 'admin') {
    header('Location: admin_functions.php');
    exit;
}

header('Location: student_functions.php?view=saved');
exit;
?>
