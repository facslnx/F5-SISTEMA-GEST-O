<?php
require_once '../utils/functions.php';
require_once '../database/connection.php';

session_start();
checkLogin();

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'change_password':
                // Validar campos obrigatórios
                if (empty($_POST['current_password']) || empty($_POST['new_password'])) {
                    jsonResponse(false, null, 'Todos os campos são obrigatórios');
                    exit;
                }
                
                // Validar senha
                if (strlen($_POST['new_password']) < 6) {
                    jsonResponse(false, null, 'A nova senha deve ter pelo menos 6 caracteres');
                    exit;
                }
                
                // Verificar senha atual
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user']['id']]);
                $user = $stmt->fetch();
                
                if (!password_verify($_POST['current_password'], $user['password'])) {
                    jsonResponse(false, null, 'Senha atual incorreta');
                    exit;
                }
                
                // Atualizar senha
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET password = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    password_hash($_POST['new_password'], PASSWORD_DEFAULT),
                    $_SESSION['user']['id']
                ]);
                
                jsonResponse(true, null, 'Senha alterada com sucesso');
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
