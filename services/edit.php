<?php
require_once '../utils/functions.php';
require_once '../database/connection.php';

session_start();
checkLogin();

// Verificar se foi fornecido um ID
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];
$pdo = getConnection();

// Se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Preparar e executar a atualização
        $sql = "UPDATE services SET 
                name = :name,
                description = :description,
                value = :value
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'value' => str_replace(',', '.', str_replace('.', '', $_POST['value'])), // Converter formato BR para US
            'id' => $id
        ]);

        $_SESSION['success_message'] = "Serviço atualizado com sucesso!";
        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        $error = "Erro ao atualizar serviço: " . $e->getMessage();
    }
}

// Buscar dados do serviço
try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$service) {
        $_SESSION['error_message'] = "Serviço não encontrado.";
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    $error = "Erro ao buscar dados do serviço: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Serviço - Sistema F5</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php require_once '../components/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Editar Serviço</span>
                        
                        <?php if (isset($error)): ?>
                            <div class="red-text"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST" class="row">
                            <div class="input-field col s12">
                                <input type="text" id="name" name="name" value="<?= htmlspecialchars($service['name'] ?? '') ?>" required>
                                <label for="name" <?= !empty($service['name']) ? 'class="active"' : '' ?>>Nome do Serviço</label>
                            </div>

                            <div class="input-field col s12">
                                <textarea id="description" name="description" class="materialize-textarea"><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
                                <label for="description" <?= !empty($service['description']) ? 'class="active"' : '' ?>>Descrição</label>
                            </div>

                            <div class="input-field col s12">
                                <input type="text" id="valor" name="value" value="<?= number_format($service['value'] ?? 0, 2, ',', '.') ?>" required>
                                <label for="valor" <?= !empty($service['value']) ? 'class="active"' : '' ?>>Valor (R$)</label>
                            </div>

                            <div class="col s12">
                                <button type="submit" class="btn waves-effect waves-light red darken-1">
                                    Salvar
                                    <i class="material-icons right">save</i>
                                </button>
                                <a href="index.php" class="btn waves-effect waves-light grey">
                                    Cancelar
                                    <i class="material-icons right">cancel</i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar componentes do Materialize
            $('.sidenav').sidenav();
            $('.dropdown-trigger').dropdown();
            
            // Inicializar máscara para o campo de valor
            $('#valor').mask('#.##0,00', {reverse: true});
            
            // Forçar labels a ficarem ativos
            M.updateTextFields();
            
            // Inicializar textarea
            M.textareaAutoResize($('#description'));
        });
    </script>
</body>
</html>
