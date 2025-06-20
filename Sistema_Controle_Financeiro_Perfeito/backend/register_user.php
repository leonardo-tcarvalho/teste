<?php
// Include database connection
require_once 'db.php';

// Function to check if request is empty
function isEmptyRequest()
{
    return empty($_POST['usuario']) || empty($_POST['password']) || empty($_POST['confirm_password']);
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate input
    if (isEmptyRequest()) {
        header('Location: ../frontend/register.php?error=empty');
        exit;
    }

    // Get form data
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        header('Location: ../frontend/register.php?error=password');
        exit;
    }

    try {
        // Check if username already exists
        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM Usuarios WHERE USUARIO = ?');
        $checkStmt->execute([$usuario]);
        $userExists = (bool) $checkStmt->fetchColumn();

        if ($userExists) {
            header('Location: ../frontend/register.php?error=exists');
            exit;
        }

        // Insert new user
        // In a real-world application, you would hash the password with password_hash()
        // But for this simple personal app, we're storing it directly as specified
        $insertStmt = $pdo->prepare('INSERT INTO Usuarios (USUARIO, PASSWORD) VALUES (?, ?)');
        $result = $insertStmt->execute([$usuario, $password]);

        if ($result) {
            // Registration successful
            header('Location: ../frontend/login.php?success=1');
            exit;
        } else {
            // Error during insertion
            header('Location: ../frontend/register.php?error=server');
            exit;
        }
    } catch (PDOException $e) {
        error_log('Registration error: ' . $e->getMessage());
        header('Location: ../frontend/register.php?error=server');
        exit;
    }
} else {
    // If not POST request, redirect to registration page
    header('Location: ../frontend/register.php');
    exit;
}
