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
    <title>F5 GESTÃO - Login</title>
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
                        <span class="card-title center-align">F5 GESTÃO</span>
                        <form id="loginForm">
                            <div class="input-field">
                                <input id="email" name="email" type="email" class="validate" required>
                                <label for="email">Email</label>
                            </div>
                            <div class="input-field">
                                <input id="password" name="password" type="password" class="validate" required>
                                <label for="password">Senha</label>
                            </div>
                            <div class="center-align">
                                <button class="btn waves-effect waves-light black" type="submit">
                                    Entrar
                                    <i class="material-icons right">send</i>
                                </button>
                            </div>
                        </form>
                        <div class="center-align" style="margin-top: 20px;">
                            <a href="register.php" class="waves-effect waves-light btn-flat">
                                <i class="material-icons left">person_add</i>
                                Criar nova conta
                            </a>
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
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: 'api/auth.php',
                    method: 'POST',
                    data: {
                        action: 'login',
                        email: $('#email').val(),
                        password: $('#password').val()
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = 'index.php';
                        } else {
                            M.toast({html: response.message, classes: 'red'});
                        }
                    },
                    error: function() {
                        M.toast({html: 'Erro ao realizar login', classes: 'red'});
                    }
                });
            });
        });
    </script>
</body>
</html>
