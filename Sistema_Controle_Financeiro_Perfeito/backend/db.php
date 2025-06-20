<?php
// Evitar enviar conteúdo JSON na conexão ao banco (só deve ser feito por endpoints específicos)
// header comentado para evitar problemas quando este arquivo é incluído em outros scripts
// header('Content-Type: application/json');

// Carregar configurações
require_once __DIR__ . '/config.php';

$host = DB_HOST;
$db = DB_NAME;
$user = DB_USER;
$pass = DB_PASS;

$dsn = "sqlsrv:Server=$host;Database=$db";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false, // Força o uso de prepared statements nativos
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Não enviar json de sucesso na conexão - isso será tratado pelos endpoints
    // echo json_encode('Sucesso');
} catch (PDOException $e) {
    // Log do erro (em ambiente de produção seria em arquivo)
    error_log('Erro de conexão com banco de dados: ' . $e->getMessage());

    // Se for chamado diretamente, retorna erro JSON
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Erro na conexão com o banco de dados']);
        exit;
    } else {
        // Se for incluído por outro arquivo, propaga a exceção
        throw $e;
    }
}
