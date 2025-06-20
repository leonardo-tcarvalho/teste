<?php
// Start session
session_start();

// Include database connection
require_once 'db.php';

// Function to check if request is empty
function isEmptyRequest()
{
    return empty($_POST['usuario']) || empty($_POST['password']);
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate input
    if (isEmptyRequest()) {
        header('Location: ../frontend/login.php?error=empty');
        exit;
    }

    // Get form data
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    try {
        // Prepare SQL statement
        $stmt = $pdo->prepare('SELECT ID, USUARIO, PASSWORD FROM Usuarios WHERE USUARIO = ?');
        $stmt->execute([$usuario]);

        // Check if user exists
        $user = $stmt->fetch();

        if ($user) {
            // Verify password - in a real-world app, you'd use password_verify() with hashed passwords
            // But since this is a simple app for personal use as specified, we're comparing directly
            if ($password === $user['PASSWORD']) {
                // Password is correct, create session
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['username'] = $user['USUARIO'];

                // Redirect to dashboard
                header('Location: ../frontend/index.php');
                exit;
            }
        }

        // If we get here, authentication failed
        header('Location: ../frontend/login.php?error=invalid');
        exit;
    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        header('Location: ../frontend/login.php?error=server');
        exit;
    }
} else {
    // If not POST request, redirect to login page
    header('Location: ../frontend/login.php');
    exit;
}
