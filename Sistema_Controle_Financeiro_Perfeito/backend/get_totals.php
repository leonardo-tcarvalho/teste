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
    $response = [
        'totalMovimentacoes' => 0,
        'categorias' => []
    ];

    // Filtros para mês/ano se fornecidos
    $dataCondition = '';
    $dataParams = [];

    if (isset($_GET['mes']) && isset($_GET['ano']) && !empty($_GET['mes']) && !empty($_GET['ano'])) {
        $dataCondition = "AND MONTH(dataMovimentacao) = ? AND YEAR(dataMovimentacao) = ?";
        $dataParams = [intval($_GET['mes']), intval($_GET['ano'])];
    }

    // 1. Total de movimentações
    $sqlTotal = "SELECT COUNT(*) FROM MovimentacoesFinanceiras 
                 WHERE IDUsuario = ? $dataCondition";
    $stmtTotal = $pdo->prepare($sqlTotal);
    $params = array_merge([$user_id], $dataParams);
    $stmtTotal->execute($params);
    $response['totalMovimentacoes'] = (int)$stmtTotal->fetchColumn();

    // 2. Obter categorias distintas e seus totais
    $sqlCategorias = "SELECT tipo as nome, SUM(valor) as total, COUNT(*) as quantidade 
                     FROM MovimentacoesFinanceiras 
                     WHERE IDUsuario = ? $dataCondition
                     GROUP BY tipo 
                     ORDER BY tipo";
    $stmtCategorias = $pdo->prepare($sqlCategorias);
    $stmtCategorias->execute($params);
    $response['categorias'] = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

    // 3. Total de receitas e despesas
    $sqlReceitas = "SELECT SUM(valor) as total FROM MovimentacoesFinanceiras 
                   WHERE IDUsuario = ? AND tipo = 'Receitas' $dataCondition";
    $stmtReceitas = $pdo->prepare($sqlReceitas);
    $stmtReceitas->execute($params);
    $response['totalReceitas'] = (float)$stmtReceitas->fetchColumn() ?: 0;

    $sqlDespesas = "SELECT SUM(valor) as total FROM MovimentacoesFinanceiras 
                   WHERE IDUsuario = ? AND tipo != 'Receitas' $dataCondition";
    $stmtDespesas = $pdo->prepare($sqlDespesas);
    $stmtDespesas->execute($params);
    $response['totalDespesas'] = (float)$stmtDespesas->fetchColumn() ?: 0;

    // 4. Calcular saldo
    $response['saldo'] = $response['totalReceitas'] - $response['totalDespesas'];

    echo json_encode($response);
} catch (PDOException $e) {
    error_log('Erro ao buscar totais: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar totais financeiros']);
}
