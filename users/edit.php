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

// Buscar dados do usuário
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$pdo = getConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário - Sistema F5</title>
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
                        <span class="card-title">Editar Usuário</span>
                        
                        <form id="userForm">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            
                            <div class="row">
                                <div class="input-field col s12">
                                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                                    <label for="name">Nome *</label>
                                </div>
                                
                                <div class="input-field col s12">
                                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                    <label for="email">Email *</label>
                                </div>
                                
                                <div class="input-field col s12">
                                    <select name="role" id="role" required>
                                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Usuário</option>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
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
                
                const formData = {
                    action: 'update',
                    id: $('input[name="id"]').val(),
                    name: $('#name').val(),
                    email: $('#email').val(),
                    role: $('#role').val()
                };
                
                $.post('../api/users.php', formData)
                .done(function(response) {
                    if (response.success) {
                        M.toast({html: 'Usuário atualizado com sucesso!', classes: 'green'});
                        setTimeout(() => window.location.href = 'index.php', 1000);
                    } else {
                        M.toast({html: response.message || 'Erro ao atualizar usuário', classes: 'red'});
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
