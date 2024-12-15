<?php
require_once '../utils/functions.php';
require_once '../database/connection.php';

session_start();
checkLogin();

// Buscar clientes
$pdo = getConnection();

try {
    $stmt = $pdo->query("SELECT * FROM clients WHERE status = 1 ORDER BY empresa");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro ao buscar clientes: " . $e->getMessage());
    $clientes = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Sistema F5</title>
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
                    <h2>Clientes</h2>
                    <a href="add.php" class="btn red darken-1">
                        <i class="material-icons left">add</i>
                        Novo Cliente
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
                                        <th>Empresa</th>
                                        <th>Responsável</th>
                                        <th>Telefone</th>
                                        <th>Email</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cliente['empresa'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($cliente['responsavel'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($cliente['telefone'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($cliente['email'] ?? '') ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="view.php?id=<?= $cliente['id'] ?>" class="btn-floating btn-small blue tooltipped" data-position="top" data-tooltip="Visualizar">
                                                    <i class="material-icons">visibility</i>
                                                </a>
                                                <a href="edit.php?id=<?= $cliente['id'] ?>" class="btn-small blue">
                                                    <i class="material-icons">edit</i>
                                                </a>
                                                <a href="create_invoice.php?id=<?= $cliente['id'] ?>" class="btn-small green">
                                                    <i class="material-icons">receipt</i>
                                                </a>
                                                <button class="btn-small red delete-client" data-id="<?= $cliente['id'] ?>">
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
            
            // Deletar cliente
            $('.delete-client').click(function() {
                const id = $(this).data('id');
                if (confirm('Tem certeza que deseja excluir este cliente?')) {
                    $.ajax({
                        url: '../api/clients.php',
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
                                M.toast({html: response.message || 'Erro ao excluir cliente', classes: 'red'});
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
