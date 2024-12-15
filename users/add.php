<?php
require_once '../utils/functions.php';
require_once '../database/connection.php';

session_start();
checkLogin();

// Verificar se o usuário atual é admin
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Usuário - Sistema F5</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php require_once '../components/header.php'; ?>
    
    <div class="container">
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Novo Usuário</span>
                        
                        <form id="userForm">
                            <div class="row">
                                <div class="input-field col s12">
                                    <input type="text" id="name" name="name" required>
                                    <label for="name">Nome *</label>
                                </div>
                                
                                <div class="input-field col s12">
                                    <input type="email" id="email" name="email" required>
                                    <label for="email">Email *</label>
                                </div>
                                
                                <div class="input-field col s12">
                                    <input type="password" id="password" name="password" required>
                                    <label for="password">Senha *</label>
                                </div>
                                
                                <div class="input-field col s12">
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                    <label for="confirm_password">Confirmar Senha *</label>
                                </div>
                                
                                <div class="input-field col s12">
                                    <select name="role" id="role" required>
                                        <option value="user">Usuário</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                    <label>Função *</label>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col s12">
                                    <button type="submit" class="btn red darken-1 waves-effect waves-light">
                                        <i class="material-icons left">save</i>
                                        Salvar
                                    </button>
                                    <a href="index.php" class="btn grey waves-effect waves-light">
                                        <i class="material-icons left">arrow_back</i>
                                        Voltar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar select
            $('select').formSelect();
            
            // Enviar formulário
            $('#userForm').submit(function(e) {
                e.preventDefault();
                
                const password = $('#password').val();
                const confirm = $('#confirm_password').val();
                
                if (password !== confirm) {
                    M.toast({html: 'As senhas não coincidem', classes: 'red'});
                    return;
                }
                
                if (password.length < 6) {
                    M.toast({html: 'A senha deve ter pelo menos 6 caracteres', classes: 'red'});
                    return;
                }
                
                const formData = {
                    action: 'create',
                    name: $('#name').val(),
                    email: $('#email').val(),
                    password: password,
                    role: $('#role').val()
                };
                
                $.post('../api/users.php', formData)
                .done(function(response) {
                    if (response.success) {
                        M.toast({html: 'Usuário criado com sucesso!', classes: 'green'});
                        setTimeout(() => window.location.href = 'index.php', 1000);
                    } else {
                        M.toast({html: response.message || 'Erro ao criar usuário', classes: 'red'});
                    }
                })
                .fail(function() {
                    M.toast({html: 'Erro ao comunicar com o servidor', classes: 'red'});
                });
            });
        });
    </script>
</body>
</html>
