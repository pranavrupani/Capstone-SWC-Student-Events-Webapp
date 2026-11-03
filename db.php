<?php
// This file creates the shared MySQL connection used by all pages.
$host = 'sql102.infinityfree.com';
$user = 'if0_43342259';
$password = 'CCg0KfEMeesF';
$database = 'if0_43342259_swc_events';
$port = 8889;

// Reuse this connection throughout the app instead of opening a new connection on every page.
$conn = new mysqli($host, $user, $password, $database, 3306);

// If MySQL is not running or the database is unavailable, stop the app with a clear message.
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>







