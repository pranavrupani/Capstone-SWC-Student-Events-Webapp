<?php
// Logout endpoint.
// It clears the session data and returns the user to the public homepage.
session_start();

session_unset();
session_destroy();

header('Location: index.php');
exit;
