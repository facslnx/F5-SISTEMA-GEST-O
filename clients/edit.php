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
        $sql = "UPDATE clients SET 
                empresa = :empresa,
                responsavel = :responsavel,
                telefone = :telefone,
                email = :email,
                endereco = :endereco,
                documento = :documento
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            'empresa' => $_POST['empresa'],
            'responsavel' => $_POST['responsavel'],
            'telefone' => $_POST['telefone'],
            'email' => $_POST['email'],
            'endereco' => $_POST['endereco'],
            'documento' => $_POST['documento'],
            'id' => $id
        ]);

        $_SESSION['success_message'] = "Cliente atualizado com sucesso!";
        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        $error = "Erro ao atualizar cliente: " . $e->getMessage();
    }
}

// Buscar dados do cliente
try {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        $_SESSION['error_message'] = "Cliente não encontrado.";
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    $error = "Erro ao buscar dados do cliente: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - Sistema F5</title>
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
                        <span class="card-title">Editar Cliente</span>
                        
                        <?php if (isset($error)): ?>
                            <div class="red-text"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST" class="row">
                            <div class="input-field col s12 m6">
                                <input type="text" id="empresa" name="empresa" value="<?= htmlspecialchars($cliente['empresa'] ?? '') ?>">
                                <label for="empresa" <?= !empty($cliente['empresa']) ? 'class="active"' : '' ?>>Empresa</label>
                            </div>

                            <div class="input-field col s12 m6">
                                <input type="text" id="responsavel" name="responsavel" value="<?= htmlspecialchars($cliente['responsavel'] ?? '') ?>">
                                <label for="responsavel" <?= !empty($cliente['responsavel']) ? 'class="active"' : '' ?>>Nome do Responsável</label>
                            </div>

                            <div class="input-field col s12 m6">
                                <input type="tel" id="telefone" name="telefone" value="<?= htmlspecialchars($cliente['telefone'] ?? '') ?>">
                                <label for="telefone" <?= !empty($cliente['telefone']) ? 'class="active"' : '' ?>>Telefone</label>
                            </div>

                            <div class="input-field col s12 m6">
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>">
                                <label for="email" <?= !empty($cliente['email']) ? 'class="active"' : '' ?>>Email</label>
                            </div>

                            <div class="input-field col s12">
                                <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($cliente['endereco'] ?? '') ?>">
                                <label for="endereco" <?= !empty($cliente['endereco']) ? 'class="active"' : '' ?>>Endereço</label>
                            </div>

                            <div class="input-field col s12 m6">
                                <input type="text" id="documento" name="documento" value="<?= htmlspecialchars($cliente['documento'] ?? '') ?>">
                                <label for="documento" <?= !empty($cliente['documento']) ? 'class="active"' : '' ?>>CNPJ/CPF</label>
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
            
            // Inicializar máscaras
            $('#telefone').mask('(00) 00000-0000');
            $('#documento').mask('00.000.000/0000-00');
            
            // Forçar labels a ficarem ativos
            M.updateTextFields();
        });
    </script>
</body>
</html>
