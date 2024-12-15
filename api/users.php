<?php
require_once '../utils/functions.php';
require_once '../database/connection.php';

session_start();
checkLogin();

// Verificar se o usuário atual é admin
if ($_SESSION['user']['role'] !== 'admin') {
    jsonResponse(false, null, 'Acesso negado');
    exit;
}

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
                // Validar campos obrigatórios
                if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['role'])) {
                    jsonResponse(false, null, 'Todos os campos são obrigatórios');
                    exit;
                }
                
                // Validar email
                if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                    jsonResponse(false, null, 'Email inválido');
                    exit;
                }
                
                // Validar senha
                if (strlen($_POST['password']) < 6) {
                    jsonResponse(false, null, 'A senha deve ter pelo menos 6 caracteres');
                    exit;
                }
                
                // Verificar se o email já existe
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$_POST['email']]);
                if ($stmt->fetch()) {
                    jsonResponse(false, null, 'Este email já está em uso');
                    exit;
                }
                
                // Criar usuário
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password, role, active, created_at, updated_at)
                    VALUES (?, ?, ?, ?, 1, NOW(), NOW())
                ");
                
                $stmt->execute([
                    trim($_POST['name']),
                    trim($_POST['email']),
                    password_hash($_POST['password'], PASSWORD_DEFAULT),
                    $_POST['role']
                ]);
                
                jsonResponse(true, ['id' => $pdo->lastInsertId()], 'Usuário criado com sucesso');
                break;
                
            case 'update':
                // Validar campos obrigatórios
                if (empty($_POST['id']) || empty($_POST['name']) || empty($_POST['email']) || empty($_POST['role'])) {
                    jsonResponse(false, null, 'Todos os campos são obrigatórios');
                    exit;
                }
                
                // Validar email
                if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                    jsonResponse(false, null, 'Email inválido');
                    exit;
                }
                
                // Verificar se o email já existe para outro usuário
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$_POST['email'], $_POST['id']]);
                if ($stmt->fetch()) {
                    jsonResponse(false, null, 'Este email já está em uso');
                    exit;
                }
                
                // Atualizar usuário
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, role = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    trim($_POST['name']),
                    trim($_POST['email']),
                    $_POST['role'],
                    $_POST['id']
                ]);
                
                jsonResponse(true, null, 'Usuário atualizado com sucesso');
                break;
                
            case 'change_password':
                // Validar campos obrigatórios
                if (empty($_POST['user_id']) || empty($_POST['password'])) {
                    jsonResponse(false, null, 'Todos os campos são obrigatórios');
                    exit;
                }
                
                // Validar senha
                if (strlen($_POST['password']) < 6) {
                    jsonResponse(false, null, 'A senha deve ter pelo menos 6 caracteres');
                    exit;
                }
                
                // Atualizar senha
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET password = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    password_hash($_POST['password'], PASSWORD_DEFAULT),
                    $_POST['user_id']
                ]);
                
                jsonResponse(true, null, 'Senha alterada com sucesso');
                break;
                
            case 'toggle_status':
                // Validar campos obrigatórios
                if (empty($_POST['user_id']) || !isset($_POST['status'])) {
                    jsonResponse(false, null, 'Todos os campos são obrigatórios');
                    exit;
                }
                
                // Não permitir desativar o próprio usuário
                if ($_POST['user_id'] == $_SESSION['user']['id']) {
                    jsonResponse(false, null, 'Você não pode desativar seu próprio usuário');
                    exit;
                }
                
                // Atualizar status
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET active = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $_POST['status'],
                    $_POST['user_id']
                ]);
                
                jsonResponse(true, null, 'Status atualizado com sucesso');
                break;
                
            default:
                jsonResponse(false, null, 'Ação inválida');
                break;
        }
    } else {
        jsonResponse(false, null, 'Método não permitido');
    }
    
} catch (Exception $e) {
    error_log('Erro ao processar requisição: ' . $e->getMessage());
    jsonResponse(false, null, 'Erro ao processar requisição');
}
