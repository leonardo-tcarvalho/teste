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

try {
    // Inicializa a query base
    $sql = 'SELECT id, nome, tipo, valor, dataMovimentacao, statusPagamento FROM MovimentacoesFinanceiras';
    $params = [];
    $conditions = [];

    // Always filter by user ID
    $conditions[] = 'IDUsuario = ?';
    $params[] = $user_id;

    // Filtro por tipo (Receitas, Cartões, Gastos Fixos, etc.)
    if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
        $conditions[] = 'tipo = ?';
        $params[] = $_GET['tipo'];
    }

    // Filtro por status de pagamento
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $conditions[] = 'statusPagamento = ?';
        $params[] = $_GET['status'];
    }

    // Filtro por data (mês/ano)
    if (isset($_GET['mes']) && isset($_GET['ano'])) {
        $conditions[] = "MONTH(dataMovimentacao) = ? AND YEAR(dataMovimentacao) = ?";
        $params[] = intval($_GET['mes']);
        $params[] = intval($_GET['ano']);
    }

    // Filtro por intervalo de valores
    if (isset($_GET['valorMin']) && is_numeric($_GET['valorMin'])) {
        $conditions[] = "valor >= ?";
        $params[] = floatval($_GET['valorMin']);
    }

    if (isset($_GET['valorMax']) && is_numeric($_GET['valorMax'])) {
        $conditions[] = "valor <= ?";
        $params[] = floatval($_GET['valorMax']);
    }

    // Adiciona WHERE se houver condições
    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    // Ordenação
    $orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'dataMovimentacao';
    $orderDir = isset($_GET['orderDir']) && strtoupper($_GET['orderDir']) === 'DESC' ? 'DESC' : 'ASC';

    // Lista de colunas válidas para ordenação
    $validColumns = ['id', 'nome', 'tipo', 'valor', 'dataMovimentacao', 'statusPagamento'];

    if (in_array($orderBy, $validColumns)) {
        $sql .= " ORDER BY $orderBy $orderDir";
    } else {
        $sql .= " ORDER BY dataMovimentacao DESC";
    }

    // Prepara e executa a consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $movimentacoes = $stmt->fetchAll();

    echo json_encode($movimentacoes);
} catch (PDOException $e) {
    error_log('Erro ao buscar movimentações: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar movimentações. Por favor, tente novamente.']);
}
