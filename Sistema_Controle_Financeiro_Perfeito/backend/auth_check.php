<?php

/**
 * Helper function to check if user is logged in
 * If not, redirects to login page
 * 
 * @param string $redirect_to Path to redirect if not logged in
 * @return int User ID if logged in
 */
function require_login($redirect_to = 'login.php')
{
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        // For AJAX requests, return 401 Unauthorized
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            http_response_code(401);
            exit(json_encode(['error' => 'Unauthorized', 'redirect' => $redirect_to]));
        }

        // For normal requests, redirect to login
        header("Location: $redirect_to");
        exit;
    }

    return $_SESSION['user_id'];
}

// If this file is called directly, just check and return user ID
if (!isset($user_id)) {
    $user_id = require_login();
}
