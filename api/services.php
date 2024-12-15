<?php
require_once '../config/database.php';
require_once '../utils/functions.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user'])) {
    jsonResponse(false, null, 'Usuário não autenticado');
    exit;
}

// Verificar se a ação foi especificada
if (!isset($_POST['action'])) {
    jsonResponse(false, null, 'Ação não especificada');
    exit;
}

$action = $_POST['action'];

try {
    switch ($action) {
        case 'create':
            // Validar campos obrigatórios
            $required_fields = ['name', 'value'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    jsonResponse(false, null, "Campo obrigatório não fornecido: $field");
                    exit;
                }
            }
            
            // Preparar e executar a query
            $stmt = $pdo->prepare("
                INSERT INTO services (name, description, value) 
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['name'],
                $_POST['description'] ?? '',
                floatval($_POST['value'])
            ]);
            
            jsonResponse(true, ['id' => $pdo->lastInsertId()], 'Serviço criado com sucesso');
            break;

        case 'update':
            if (!isset($_POST['id'])) {
                jsonResponse(false, null, 'ID do serviço não fornecido');
                exit;
            }
            
            $fields = [];
            $values = [];
            
            // Campos que podem ser atualizados
            $allowed_fields = ['name', 'description', 'value', 'status'];
            
            foreach ($allowed_fields as $field) {
                if (isset($_POST[$field]) && $_POST[$field] !== '') {
                    $fields[] = "$field = ?";
                    $values[] = $field === 'value' ? floatval($_POST[$field]) : $_POST[$field];
                }
            }
            
            if (empty($fields)) {
                jsonResponse(false, null, 'Nenhum campo para atualizar');
                exit;
            }
            
            // Adicionar ID ao final dos valores
            $values[] = $_POST['id'];
            
            $sql = "UPDATE services SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            
            if ($stmt->rowCount() === 0) {
                jsonResponse(false, null, 'Serviço não encontrado ou nenhuma alteração feita');
                exit;
            }
            
            jsonResponse(true, null, 'Serviço atualizado com sucesso');
            break;

        case 'delete':
            if (!isset($_POST['id'])) {
                jsonResponse(false, null, 'ID do serviço não fornecido');
                exit;
            }
            
            // Soft delete - apenas atualiza o status
            $stmt = $pdo->prepare("UPDATE services SET status = 0 WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            if ($stmt->rowCount() === 0) {
                jsonResponse(false, null, 'Serviço não encontrado');
                exit;
            }
            
            jsonResponse(true, null, 'Serviço removido com sucesso');
            break;

        case 'get':
            if (!isset($_POST['id'])) {
                jsonResponse(false, null, 'ID do serviço não fornecido');
                exit;
            }
            
            $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND status = 1");
            $stmt->execute([$_POST['id']]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$service) {
                jsonResponse(false, null, 'Serviço não encontrado');
                exit;
            }
            
            jsonResponse(true, $service, 'Serviço encontrado');
            break;

        case 'list':
            $stmt = $pdo->query("SELECT * FROM services WHERE status = 1 ORDER BY name");
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonResponse(true, $services, 'Serviços listados com sucesso');
            break;

        default:
            jsonResponse(false, null, 'Ação inválida');
            exit;
    }
} catch (PDOException $e) {
    error_log('Erro na API de serviços: ' . $e->getMessage());
    jsonResponse(false, null, 'Erro ao processar a requisição: ' . $e->getMessage());
}
