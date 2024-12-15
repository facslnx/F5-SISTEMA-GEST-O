<?php
require_once 'config/database.php';
session_start();

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F5 GESTÃO - Registro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="row">
            <div class="col s12 m6 offset-m3">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title center-align">Criar Conta - F5 GESTÃO</span>
                        <form id="registerForm">
                            <div class="input-field">
                                <input id="name" type="text" name="name" required>
                                <label for="name">Nome</label>
                            </div>
                            <div class="input-field">
                                <input id="email" type="email" name="email" required>
                                <label for="email">E-mail</label>
                            </div>
                            <div class="input-field">
                                <input id="password" type="password" name="password" required>
                                <label for="password">Senha</label>
                            </div>
                            <div class="input-field">
                                <input id="confirmPassword" type="password" name="confirmPassword" required>
                                <label for="confirmPassword">Confirmar Senha</label>
                            </div>
                            <div class="center-align">
                                <button class="btn waves-effect waves-light red" type="submit">
                                    Registrar
                                    <i class="material-icons right">person_add</i>
                                </button>
                            </div>
                        </form>
                        <div class="center-align" style="margin-top: 20px;">
                            <a href="login.php">Já tem uma conta? Faça login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                
                const password = $('#password').val();
                const confirmPassword = $('#confirmPassword').val();

                if (password !== confirmPassword) {
                    M.toast({html: 'As senhas não coincidem', classes: 'red'});
                    return;
                }

                $.ajax({
                    url: 'auth/auth_process.php',
                    type: 'POST',
                    data: {
                        action: 'register',
                        name: $('#name').val(),
                        email: $('#email').val(),
                        password: password
                    },
                    success: function(response) {
                        if (response.success) {
                            M.toast({html: 'Conta criada com sucesso!', classes: 'green'});
                            setTimeout(function() {
                                window.location.href = 'login.php';
                            }, 1000);
                        } else {
                            M.toast({html: response.message || 'Erro ao criar conta', classes: 'red'});
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro na requisição:', error);
                        M.toast({html: 'Erro ao criar conta. Tente novamente.', classes: 'red'});
                    }
                });
            });
        });
    </script>
</body>
</html>
