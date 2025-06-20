<?php
header('Content-Type: application/json');
require 'db.php';

// Check if user is logged in via session
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Aceitar tanto JSON quanto form data
$input = [];
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

// Verificar se o ID foi fornecido
if (!isset($input['id']) || empty($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID não fornecido']);
    exit;
}

$id = (int)$input['id'];

try {
    // Verificar se a movimentação existe e pertence ao usuário correto
    $checkStmt = $pdo->prepare("SELECT id FROM MovimentacoesFinanceiras WHERE id = ? AND IDUsuario = ?");
    $checkStmt->execute([$id, $user_id]);

    if ($checkStmt->rowCount() === 0) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Movimentação não encontrada ou você não tem permissão para excluí-la']);
        exit;
    }

    // Excluir a movimentação
    $stmt = $pdo->prepare("DELETE FROM MovimentacoesFinanceiras WHERE id = ? AND IDUsuario = ?");
    $stmt->execute([$id, $user_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Movimentação excluída com sucesso!'
    ]);
} catch (PDOException $e) {
    error_log('Erro ao excluir movimentação: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao excluir movimentação: ' . $e->getMessage()]);
}
