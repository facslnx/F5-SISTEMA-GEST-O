<?php
require_once '../config/database.php';
require_once '../utils/functions.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

$user = $_SESSION['user'];

// Buscar serviços
$sql = "SELECT * FROM services WHERE status = 1 ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$services = $stmt->fetchAll();

// Buscar clientes para o select
$stmt = $pdo->query("SELECT id, empresa FROM clients WHERE status = 1 ORDER BY empresa");
$clients = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços - Sistema F5</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php require_once '../components/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col s12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Serviços</h2>
                    <a href="add.php" class="btn red darken-1">
                        <i class="material-icons left">add</i>
                        Novo Serviço
                    </a>
                </div>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="card-panel green white-text">
                        <?= $_SESSION['success_message'] ?>
                        <?php unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="card-panel red white-text">
                        <?= $_SESSION['error_message'] ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-content">
                        <div class="table-responsive">
                            <table class="striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Descrição</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($services as $service): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($service['name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($service['description'] ?? '') ?></td>
                                        <td>R$ <?= number_format((float)($service['value'] ?? 0), 2, ',', '.') ?></td>
                                        <td>
                                            <span class="new badge green" data-badge-caption="">Ativo</span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="view.php?id=<?= $service['id'] ?>" class="btn-floating btn-small blue tooltipped" data-position="top" data-tooltip="Visualizar">
                                                    <i class="material-icons">visibility</i>
                                                </a>
                                                <a href="edit.php?id=<?= $service['id'] ?>" class="btn-small blue">
                                                    <i class="material-icons">edit</i>
                                                </a>
                                                <button class="btn-small red delete-service" data-id="<?= $service['id'] ?>">
                                                    <i class="material-icons">delete</i>
                                                </button>
                                            </div>
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
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar componentes do Materialize
            $('.sidenav').sidenav();
            $('.dropdown-trigger').dropdown();
            $('.tooltipped').tooltip();
            
            // Deletar serviço
            $('.delete-service').click(function() {
                const id = $(this).data('id');
                if (confirm('Tem certeza que deseja excluir este serviço?')) {
                    $.ajax({
                        url: '../api/services.php',
                        type: 'POST',
                        data: {
                            action: 'delete',
                            id: id
                        },
                        success: function(response) {
                            if (response.success) {
                                M.toast({html: response.message, classes: 'green'});
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                M.toast({html: response.message || 'Erro ao excluir serviço', classes: 'red'});
                            }
                        },
                        error: function() {
                            M.toast({html: 'Erro ao conectar com o servidor', classes: 'red'});
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
