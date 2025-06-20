<?php
// Pegar o código de erro
$errorCode = isset($_GET['code']) ? intval($_GET['code']) : 404;
$errorMessages = [
    400 => 'Requisição inválida',
    401 => 'Não autorizado',
    403 => 'Acesso negado',
    404 => 'Página não encontrada',
    500 => 'Erro interno do servidor',
    503 => 'Serviço indisponível'
];
$errorMessage = isset($errorMessages[$errorCode]) ? $errorMessages[$errorCode] : 'Erro desconhecido';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $errorCode ?> - <?= $errorMessage ?> | Sistema de Controle Financeiro</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-container {
            max-width: 600px;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
        }

        .error-code {
            font-size: 6rem;
            font-weight: bold;
            line-height: 1;
            color: #3949ab;
            margin-bottom: 1rem;
        }

        .error-message {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #495057;
        }

        .error-description {
            margin-bottom: 2rem;
            color: #6c757d;
        }

        .error-icon {
            font-size: 4rem;
            color: #ff6b6b;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="error-container mx-auto">
            <div class="error-icon">
                <?php if ($errorCode == 404): ?>
                    <i class="bi bi-search"></i>
                <?php elseif ($errorCode == 403): ?>
                    <i class="bi bi-shield-lock"></i>
                <?php elseif ($errorCode == 500): ?>
                    <i class="bi bi-exclamation-triangle"></i>
                <?php else: ?>
                    <i class="bi bi-exclamation-circle"></i>
                <?php endif; ?>
            </div>
            <div class="error-code"><?= $errorCode ?></div>
            <h1 class="error-message"><?= $errorMessage ?></h1>
            <p class="error-description">
                <?php
                switch ($errorCode) {
                    case 400:
                        echo "Os dados enviados são inválidos ou mal formatados.";
                        break;
                    case 401:
                        echo "Você precisa estar autenticado para acessar este recurso.";
                        break;
                    case 403:
                        echo "Você não tem permissão para acessar este recurso.";
                        break;
                    case 404:
                        echo "A página ou recurso que você está procurando não foi encontrado.";
                        break;
                    case 500:
                        echo "Ocorreu um erro interno no servidor. Por favor, tente novamente mais tarde.";
                        break;
                    case 503:
                        echo "O serviço está temporariamente indisponível. Por favor, tente novamente mais tarde.";
                        break;
                    default:
                        echo "Ocorreu um erro inesperado.";
                }
                ?>
            </p>
            <a href="/Sistema_Controle_Financeiro/frontend/index.php" class="btn btn-primary">
                <i class="bi bi-house-door me-2"></i>Voltar para a página inicial
            </a>
        </div>
    </div>
</body>

</html>