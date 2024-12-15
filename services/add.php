<?php
require_once '../utils/functions.php';
require_once '../database/connection.php';

session_start();
checkLogin();

$pdo = getConnection();

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar e limpar os dados
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $value = filter_input(INPUT_POST, 'value', FILTER_VALIDATE_FLOAT);
        
        // Validar os campos obrigatórios
        if (empty($name)) {
            throw new Exception('O nome do serviço é obrigatório');
        }
        
        if ($value === false || $value === null) {
            throw new Exception('O valor do serviço é obrigatório e deve ser um número válido');
        }

        // Preparar e executar a query
        $stmt = $pdo->prepare("
            INSERT INTO services (
                name, 
                description, 
                value,
                status,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, 1, NOW(), NOW())
        ");

        if ($stmt->execute([$name, $description, $value])) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Serviço adicionado com sucesso!'
            ];
            header('Location: index.php');
            exit;
        } else {
            throw new Exception('Erro ao adicionar serviço');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Serviço - Sistema F5</title>
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
                        <span class="card-title">Adicionar Novo Serviço</span>
                        
                        <?php if (isset($error)): ?>
                            <div class="card-panel red white-text">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="row">
                            <div class="input-field col s12">
                                <input type="text" id="name" name="name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                                <label for="name">Nome do Serviço *</label>
                            </div>

                            <div class="input-field col s12">
                                <textarea id="description" name="description" class="materialize-textarea"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                                <label for="description">Descrição</label>
                            </div>

                            <div class="input-field col s12 m6">
                                <input type="number" id="value" name="value" step="0.01" min="0" value="<?= isset($_POST['value']) ? htmlspecialchars($_POST['value']) : '' ?>" required>
                                <label for="value">Valor (R$) *</label>
                            </div>

                            <div class="col s12" style="margin-top: 20px;">
                                <a href="index.php" class="btn grey waves-effect waves-light">
                                    <i class="material-icons left">arrow_back</i>
                                    Voltar
                                </a>
                                <button type="submit" class="btn green waves-effect waves-light">
                                    <i class="material-icons left">save</i>
                                    Salvar
                                </button>
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
        document.addEventListener('DOMContentLoaded', function() {
            M.AutoInit();
            
            // Inicializar textarea
            var textareas = document.querySelectorAll('.materialize-textarea');
            M.textareaAutoResize(textareas);
        });
    </script>
</body>
</html>
