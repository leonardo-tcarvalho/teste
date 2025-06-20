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

// Validar campos obrigatórios
if (
    !isset($input['nome']) || empty($input['nome']) ||
    !isset($input['tipo']) || empty($input['tipo']) ||
    !isset($input['valor']) || empty($input['valor']) ||
    !isset($input['dataMovimentacao']) || empty($input['dataMovimentacao'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Campos obrigatórios não fornecidos']);
    exit;
}

try {
    // Validar tipo de dados
    $valor = filter_var($input['valor'], FILTER_VALIDATE_FLOAT);
    if ($valor === false) {
        http_response_code(400);
        echo json_encode(['error' => 'O valor deve ser um número válido']);
        exit;
    }

    // Validar formato da data (YYYY-MM-DD)
    $data = DateTime::createFromFormat('Y-m-d', $input['dataMovimentacao']);
    if (!$data || $data->format('Y-m-d') !== $input['dataMovimentacao']) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato de data inválido. Use YYYY-MM-DD']);
        exit;
    }

    // Preparar a inserção
    $stmt = $pdo->prepare('INSERT INTO MovimentacoesFinanceiras 
        (nome, tipo, valor, dataMovimentacao, statusPagamento, IDUsuario) 
        VALUES (?, ?, ?, ?, ?, ?)');

    $stmt->execute([
        trim($input['nome']),
        trim($input['tipo']),
        $valor,
        $input['dataMovimentacao'],
        isset($input['statusPagamento']) ? trim($input['statusPagamento']) : null,
        $user_id // Garantir que a movimentação é associada ao usuário correto
    ]);

    $novaId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Movimentação adicionada com sucesso!',
        'id' => $novaId
    ]);
} catch (PDOException $e) {
    error_log('Erro ao inserir movimentação: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao salvar movimentação: ' . $e->getMessage()]);
}
