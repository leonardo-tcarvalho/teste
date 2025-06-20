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

// Verificar ID
if (!isset($input['id']) || empty($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID é obrigatório para atualização.']);
    exit;
}

// Verificar se existem campos para atualizar
if (
    !isset($input['nome']) && !isset($input['tipo']) && !isset($input['valor']) &&
    !isset($input['dataMovimentacao']) && !isset($input['statusPagamento'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Nenhum campo para atualização foi fornecido.']);
    exit;
}

try {
    // Construir a query dinamicamente com base nos campos enviados
    $sql = 'UPDATE MovimentacoesFinanceiras SET ';
    $params = [];
    $updateFields = [];    // Verificar e adicionar cada campo
    if (isset($input['nome'])) {
        $updateFields[] = 'nome = ?';
        $params[] = trim($input['nome']);
    }

    if (isset($input['tipo'])) {
        $updateFields[] = 'tipo = ?';
        $params[] = trim($input['tipo']);
    }

    if (isset($input['valor'])) {
        $valor = filter_var($input['valor'], FILTER_VALIDATE_FLOAT);
        if ($valor === false) {
            http_response_code(400);
            echo json_encode(['error' => 'O valor deve ser um número válido.']);
            exit;
        }
        $updateFields[] = 'valor = ?';
        $params[] = $valor;
    }

    if (isset($input['dataMovimentacao'])) {
        // Validação da data
        $date = DateTime::createFromFormat('Y-m-d', $input['dataMovimentacao']);
        if (!$date || $date->format('Y-m-d') !== $input['dataMovimentacao']) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de data inválido. Use o formato YYYY-MM-DD.']);
            exit;
        }
        $updateFields[] = 'dataMovimentacao = ?';
        $params[] = $input['dataMovimentacao'];
    }

    if (isset($input['statusPagamento'])) {
        $updateFields[] = 'statusPagamento = ?';
        $params[] = trim($input['statusPagamento']);
    } // Completar a query
    $sql .= implode(', ', $updateFields) . ' WHERE id = ? AND IDUsuario = ?';
    // Adicionar o ID e o ID do usuário ao final dos parâmetros
    $params[] = (int)$input['id'];
    $params[] = $user_id;

    // Preparar e executar a query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Verificar se a atualização afetou algum registro
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Movimentação atualizada com sucesso!'
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Movimentação não encontrada ou nenhum dado foi modificado.']);
    }
} catch (PDOException $e) {
    error_log('Erro ao atualizar movimentação: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao atualizar movimentação: ' . $e->getMessage()]);
}
