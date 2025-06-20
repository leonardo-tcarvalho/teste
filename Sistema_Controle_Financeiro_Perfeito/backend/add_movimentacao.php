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

// Accept both JSON and form data
$input = [];
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

// Validate required fields
if (!isset($input['nome']) || trim($input['nome']) === '') {
    http_response_code(400);
    echo json_encode(['error' => 'O nome/descrição é obrigatório']);
    exit;
}

if (!isset($input['tipo']) || trim($input['tipo']) === '') {
    http_response_code(400);
    echo json_encode(['error' => 'O tipo é obrigatório']);
    exit;
}

if (!isset($input['valor']) || !is_numeric($input['valor'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Valor inválido']);
    exit;
}

if (!isset($input['dataMovimentacao']) || trim($input['dataMovimentacao']) === '') {
    http_response_code(400);
    echo json_encode(['error' => 'A data é obrigatória']);
    exit;
}

// Validate the date format
$date = DateTime::createFromFormat('Y-m-d', $input['dataMovimentacao']);
if (!$date || $date->format('Y-m-d') !== $input['dataMovimentacao']) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato de data inválido. Use o formato AAAA-MM-DD']);
    exit;
}

try {
    $nome = trim($input['nome']);
    $tipo = trim($input['tipo']);
    $valor = floatval($input['valor']);
    $dataMovimentacao = $input['dataMovimentacao'];
    $statusPagamento = isset($input['statusPagamento']) ? trim($input['statusPagamento']) : null;

    // Insert data with the user ID
    $sql = 'INSERT INTO MovimentacoesFinanceiras (nome, tipo, valor, dataMovimentacao, statusPagamento, IDUsuario) VALUES (?, ?, ?, ?, ?, ?)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $tipo, $valor, $dataMovimentacao, $statusPagamento, $user_id]);

    $id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Movimentação adicionada com sucesso!',
        'id' => $id
    ]);
} catch (PDOException $e) {
    error_log('Erro ao adicionar movimentação: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao adicionar movimentação: ' . $e->getMessage()]);
}
