<?php
require_once '../backend/auth_check.php';

// If user is already logged in, redirect to index page
redirect_if_logged_in('index.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Controle Financeiro</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .btn {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        /* Responsive styles */
        @media screen and (max-width: 480px) {
            .login-container {
                width: 90%;
                margin: 50px auto;
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Sistema de Controle Financeiro</h1>
            <h3>Login</h3>
        </div>

        <div id="alert-box" class="alert alert-danger" style="display: none;"></div>

        <form id="login-form">
            <div class="form-group">
                <label for="usuario">Usuário:</label>
                <input type="text" id="usuario" name="usuario" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn">Entrar</button>
            </div>
        </form>

        <div class="register-link">
            <p>Não possui uma conta? <a href="register.php">Registre-se aqui</a></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('login-form');
            const alertBox = document.getElementById('alert-box');

            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const usuario = document.getElementById('usuario').value;
                const password = document.getElementById('password').value;

                // Hide any previous error messages
                alertBox.style.display = 'none';

                // Send login request
                fetch('../backend/login.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            usuario: usuario,
                            password: password
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Redirect to main page on successful login
                            window.location.href = 'index.php';
                        } else {
                            // Show error message
                            alertBox.textContent = data.error || 'Erro ao efetuar login. Tente novamente.';
                            alertBox.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alertBox.textContent = 'Erro ao conectar com o servidor. Tente novamente mais tarde.';
                        alertBox.style.display = 'block';
                    });
            });
        });
    </script>
</body>

</html>