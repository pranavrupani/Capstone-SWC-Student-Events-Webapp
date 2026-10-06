<?php
// This file acts as a role-based redirect page.
// If a user is not logged in, they must log in first.
// Once they are logged in, they are sent to the correct event page for their role.
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Admins should be sent to the admin event management page.
if (($_SESSION['user_role'] ?? '') === 'admin') {
    header('Location: admin_functions.php?view=events');
    exit;
}

// Students should be sent to the student event list page.
header('Location: student_functions.php?view=events');
exit;
?>
