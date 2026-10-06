<?php
// This file creates the shared MySQL connection used by all pages.
$host = '127.0.0.1';
$user = 'root';
$password = 'root';
$dbname = 'swc_events';
$port = 8889;

// Reuse this connection throughout the app instead of opening a new connection on every page.
$conn = new mysqli($host, $user, $password, $dbname, $port);

// If MySQL is not running or the database is unavailable, stop the app with a clear message.
if ($conn->connect_error) {
    die('Database connection failed. Please start MySQL in MAMP and check the database settings.<br>' . $conn->connect_error);
}
?>