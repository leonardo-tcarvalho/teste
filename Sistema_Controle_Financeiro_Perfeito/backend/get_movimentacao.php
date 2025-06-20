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

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID da movimentação não fornecido']);
    exit;
}

$id = intval($_GET['id']);

try {
    // Buscar a movimentação pelo ID e ID do usuário
    $stmt = $pdo->prepare('SELECT id, nome, tipo, valor, dataMovimentacao, statusPagamento 
                          FROM MovimentacoesFinanceiras 
                          WHERE id = ? AND IDUsuario = ?');
    $stmt->execute([$id, $user_id]);
    $movimentacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$movimentacao) {
        http_response_code(404);
        echo json_encode(['error' => 'Movimentação não encontrada']);
        exit;
    }

    echo json_encode($movimentacao);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar movimentação: ' . $e->getMessage()]);
}
