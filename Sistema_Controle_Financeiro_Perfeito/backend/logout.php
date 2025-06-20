<?php
// Start session if it hasn't been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page instead of returning JSON
header('Location: ../frontend/login.php');
exit;
