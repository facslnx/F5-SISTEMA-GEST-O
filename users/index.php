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

$pdo = getConnection();
$stmt = $pdo->query("SELECT * FROM users ORDER BY name");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Sistema F5</title>
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
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="card-title">Usuários</span>
                            <a href="add.php" class="btn-floating btn-large waves-effect waves-light red">
                                <i class="material-icons">add</i>
                            </a>
                        </div>

                        <table class="highlight responsive-table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Função</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= $user['role'] === 'admin' ? 'Administrador' : 'Usuário' ?></td>
                                        <td>
                                            <?php if ($user['active']): ?>
                                                <span class="new badge green" data-badge-caption="">Ativo</span>
                                            <?php else: ?>
                                                <span class="new badge grey" data-badge-caption="">Inativo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit.php?id=<?= $user['id'] ?>" class="btn-small waves-effect waves-light" title="Editar">
                                                <i class="material-icons">edit</i>
                                            </a>
                                            <button class="btn-small waves-effect waves-light change-password" 
                                                    data-id="<?= $user['id'] ?>"
                                                    title="Alterar Senha">
                                                <i class="material-icons">lock</i>
                                            </button>
                                            <?php if ($user['id'] !== $_SESSION['user']['id']): ?>
                                                <button class="btn-small <?= $user['active'] ? 'red' : 'green' ?> waves-effect waves-light toggle-status"
                                                        data-id="<?= $user['id'] ?>"
                                                        data-active="<?= $user['active'] ?>"
                                                        title="<?= $user['active'] ? 'Desativar' : 'Ativar' ?>">
                                                    <i class="material-icons"><?= $user['active'] ? 'block' : 'check' ?></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Alterar Senha -->
    <div id="modalPassword" class="modal">
        <div class="modal-content">
            <h4>Alterar Senha</h4>
            <form id="passwordForm">
                <input type="hidden" id="user_id" name="user_id">
                <div class="input-field">
                    <input type="password" id="new_password" name="new_password" required>
                    <label for="new_password">Nova Senha</label>
                </div>
                <div class="input-field">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <label for="confirm_password">Confirmar Senha</label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-close waves-effect waves-red btn-flat">Cancelar</a>
            <button type="button" id="savePassword" class="waves-effect waves-green btn-flat">Salvar</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar modal
            $('.modal').modal();

            // Abrir modal de alteração de senha
            $('.change-password').click(function() {
                const userId = $(this).data('id');
                $('#user_id').val(userId);
                $('#modalPassword').modal('open');
            });

            // Salvar nova senha
            $('#savePassword').click(function() {
                const password = $('#new_password').val();
                const confirm = $('#confirm_password').val();

                if (password !== confirm) {
                    M.toast({html: 'As senhas não coincidem', classes: 'red'});
                    return;
                }

                if (password.length < 6) {
                    M.toast({html: 'A senha deve ter pelo menos 6 caracteres', classes: 'red'});
                    return;
                }

                $.post('../api/users.php', {
                    action: 'change_password',
                    user_id: $('#user_id').val(),
                    password: password
                })
                .done(function(response) {
                    if (response.success) {
                        M.toast({html: 'Senha alterada com sucesso', classes: 'green'});
                        $('#modalPassword').modal('close');
                        $('#passwordForm')[0].reset();
                    } else {
                        M.toast({html: response.message, classes: 'red'});
                    }
                })
                .fail(function() {
                    M.toast({html: 'Erro ao comunicar com o servidor', classes: 'red'});
                });
            });

            // Alternar status do usuário
            $('.toggle-status').click(function() {
                const userId = $(this).data('id');
                const active = $(this).data('active');
                const newStatus = active ? 0 : 1;

                if (confirm(`Deseja ${active ? 'desativar' : 'ativar'} este usuário?`)) {
                    $.post('../api/users.php', {
                        action: 'toggle_status',
                        user_id: userId,
                        status: newStatus
                    })
                    .done(function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            M.toast({html: response.message, classes: 'red'});
                        }
                    })
                    .fail(function() {
                        M.toast({html: 'Erro ao comunicar com o servidor', classes: 'red'});
                    });
                }
            });
        });
    </script>
</body>
</html>
