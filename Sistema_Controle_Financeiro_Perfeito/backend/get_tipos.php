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
    // Consulta para obter tipos distintos de movimentações apenas do usuário logado
    $sql = "SELECT DISTINCT tipo FROM MovimentacoesFinanceiras WHERE IDUsuario = ? ORDER BY tipo";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);

    $tipos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tipos[] = $row['tipo'];
    }

    echo json_encode($tipos);
} catch (PDOException $e) {
    error_log('Erro ao buscar tipos: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar tipos: ' . $e->getMessage()]);
}
