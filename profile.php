<?php
require_once 'utils/functions.php';
require_once 'database/connection.php';

session_start();
checkLogin();

$pdo = getConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Sistema F5</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php require_once 'components/header.php'; ?>
    
    <div class="container">
        <div class="row">
            <div class="col s12 m8 offset-m2">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Meu Perfil</span>
                        
                        <div class="row">
                            <div class="col s12">
                                <div class="input-field">
                                    <input type="text" id="name" value="<?= htmlspecialchars($user['name']) ?>" readonly>
                                    <label for="name">Nome</label>
                                </div>
                                
                                <div class="input-field">
                                    <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                    <label for="email">Email</label>
                                </div>
                                
                                <div class="input-field">
                                    <input type="text" value="<?= $user['role'] === 'admin' ? 'Administrador' : 'Usuário' ?>" readonly>
                                    <label>Função</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="divider"></div>
                        
                        <div class="section">
                            <h5>Alterar Senha</h5>
                            <form id="passwordForm">
                                <div class="row">
                                    <div class="input-field col s12">
                                        <input type="password" id="current_password" name="current_password" required>
                                        <label for="current_password">Senha Atual *</label>
                                    </div>
                                    
                                    <div class="input-field col s12">
                                        <input type="password" id="new_password" name="new_password" required>
                                        <label for="new_password">Nova Senha *</label>
                                    </div>
                                    
                                    <div class="input-field col s12">
                                        <input type="password" id="confirm_password" name="confirm_password" required>
                                        <label for="confirm_password">Confirmar Nova Senha *</label>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col s12">
                                        <button type="submit" class="btn red darken-1 waves-effect waves-light">
                                            <i class="material-icons left">lock</i>
                                            Alterar Senha
                                        </button>
                                    </div>
                                </div>
                            </form>
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
            $('#passwordForm').submit(function(e) {
                e.preventDefault();
                
                const currentPassword = $('#current_password').val();
                const newPassword = $('#new_password').val();
                const confirmPassword = $('#confirm_password').val();
                
                if (newPassword !== confirmPassword) {
                    M.toast({html: 'As senhas não coincidem', classes: 'red'});
                    return;
                }
                
                if (newPassword.length < 6) {
                    M.toast({html: 'A nova senha deve ter pelo menos 6 caracteres', classes: 'red'});
                    return;
                }
                
                $.post('api/profile.php', {
                    action: 'change_password',
                    current_password: currentPassword,
                    new_password: newPassword
                })
                .done(function(response) {
                    if (response.success) {
                        M.toast({html: 'Senha alterada com sucesso!', classes: 'green'});
                        $('#passwordForm')[0].reset();
                    } else {
                        M.toast({html: response.message || 'Erro ao alterar senha', classes: 'red'});
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
