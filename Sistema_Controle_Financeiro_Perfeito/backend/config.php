<?php

/**
 * Arquivo de configuração do sistema
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'GastosPessoais');
define('DB_USER', 'sa');
define('DB_PASS', '50062493@Leo');

// Configurações de ambiente
define('ENV_PRODUCTION', false);
define('DEBUG', !ENV_PRODUCTION);

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de codificação
ini_set('default_charset', 'UTF-8');

// Configurações de exibição de erros (apenas em desenvolvimento)
if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', ENV_PRODUCTION); // true em produção

// Versão da aplicação
define('APP_VERSION', '1.0.0');

// URL base da aplicação
$baseUrl = ENV_PRODUCTION ? 'https://seudominio.com' : 'http://localhost';
define('BASE_URL', $baseUrl);

// Caminhos da aplicação
define('ROOT_PATH', dirname(__DIR__));
define('BACKEND_PATH', ROOT_PATH . '/backend');
define('FRONTEND_PATH', ROOT_PATH . '/frontend');
