<?php
header('Content-Type: application/json');
require 'db.php';

// Accept both JSON and form data
$input = [];
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

if (!isset($input['usuario']) || empty($input['usuario']) || !isset($input['password']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Usuário e senha são obrigatórios']);
    exit;
}

$usuario = trim($input['usuario']);
$password = $input['password'];

try {
    // Check if username already exists
    $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM Usuarios WHERE usuario = ?');
    $checkStmt->execute([$usuario]);
    $userExists = (int)$checkStmt->fetchColumn() > 0;

    if ($userExists) {
        http_response_code(409); // Conflict
        echo json_encode(['error' => 'Este nome de usuário já está em uso']);
        exit;
    }

    // Insert new user
    $insertStmt = $pdo->prepare('INSERT INTO Usuarios (usuario, password) VALUES (?, ?)');
    $insertStmt->execute([$usuario, $password]);

    $newUserId = $pdo->lastInsertId();    // Start session and log user in automatically
    session_start();
    $_SESSION['user_id'] = $newUserId;
    $_SESSION['username'] = $usuario; // Changed to match the variable used in frontend

    echo json_encode([
        'success' => true,
        'message' => 'Usuário registrado com sucesso!',
        'user' => [
            'id' => $newUserId,
            'usuario' => $usuario
        ]
    ]);
} catch (PDOException $e) {
    error_log('Erro ao registrar usuário: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao registrar usuário: ' . $e->getMessage()]);
}
