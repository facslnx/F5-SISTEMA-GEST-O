<?php
require_once '../config/database.php';
require_once '../utils/functions.php';

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        jsonResponse(false, null, 'Email e senha são obrigatórios');
        exit;
    }
    
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Log de sucesso
            error_log("Login bem sucedido para o usuário: " . $user['email'] . " (ID: " . $user['id'] . ")");
            
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'login_time' => time()
            ];
            
            // Atualiza último login
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            jsonResponse(true, ['redirect' => '/F5-SISTEMA-NOVO/index.php'], 'Login realizado com sucesso');
        } else {
            // Log de falha
            error_log("Tentativa de login falhou para o email: " . $email);
            jsonResponse(false, null, 'Email ou senha inválidos');
        }
    } catch (PDOException $e) {
        error_log("Erro no login: " . $e->getMessage());
        jsonResponse(false, null, 'Erro ao realizar login. Por favor, tente novamente.');
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['logout'])) {
    // Log de logout
    if (isset($_SESSION['user'])) {
        error_log("Logout realizado para o usuário: " . $_SESSION['user']['email']);
    }
    
    session_destroy();
    jsonResponse(true, ['redirect' => '/F5-SISTEMA-NOVO/login.php'], 'Logout realizado com sucesso');
} else {
    jsonResponse(false, null, 'Método não permitido');
}
