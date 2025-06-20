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

$usuario = $input['usuario'];
$password = $input['password'];

try {
    // Find user by username
    $stmt = $pdo->prepare('SELECT id, usuario, password FROM Usuarios WHERE usuario = ?');
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuário ou senha incorretos']);
        exit;
    }

    // Simple password comparison (since we're storing passwords directly as mentioned by user)
    if ($password === $user['password']) {        // Start session and store user info
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['usuario']; // Changed to match the variable used in frontend

        echo json_encode([
            'success' => true,
            'message' => 'Login efetuado com sucesso!',
            'user' => [
                'id' => $user['id'],
                'usuario' => $user['usuario']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Usuário ou senha incorretos']);
    }
} catch (PDOException $e) {
    error_log('Erro no login: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao processar login: ' . $e->getMessage()]);
}
