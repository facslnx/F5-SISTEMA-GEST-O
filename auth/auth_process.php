<?php
require_once '../config/database.php';
require_once '../utils/functions.php';
session_start();

// Habilitar exibição de erros para debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log da requisição
error_log('Request received: ' . print_r($_POST, true));

if (!isset($_POST['action'])) {
    jsonResponse(false, null, 'Ação não especificada');
}

$action = $_POST['action'];

switch ($action) {
    case 'register':
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        error_log("Tentativa de registro para email: $email");
        
        if (empty($name) || empty($email) || empty($password)) {
            jsonResponse(false, null, 'Todos os campos são obrigatórios');
        }
        
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(false, null, 'Email inválido');
        }
        
        // Validar senha
        if (strlen($password) < 6) {
            jsonResponse(false, null, 'A senha deve ter pelo menos 6 caracteres');
        }
        
        try {
            // Verificar se o email já existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                jsonResponse(false, null, 'Este email já está cadastrado');
            }
            
            // Criar novo usuário
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, active, created_at) 
                VALUES (?, ?, ?, TRUE, NOW())
            ");
            $stmt->execute([$name, $email, $hashedPassword]);
            
            error_log("Usuário registrado com sucesso: $email");
            jsonResponse(true, null, 'Conta criada com sucesso');
            
        } catch (PDOException $e) {
            error_log('Erro no registro: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            jsonResponse(false, null, 'Erro ao criar conta');
        }
        break;

    case 'login':
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        error_log("Tentativa de login para email: $email");
        
        if (empty($email) || empty($password)) {
            jsonResponse(false, null, 'Email e senha são obrigatórios');
        }
        
        try {
            // Verificar se o usuário existe e está ativo
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND active = TRUE LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Usuário encontrado: " . ($user ? 'sim' : 'não'));
            if ($user) {
                error_log("Hash armazenado: " . $user['password']);
            }
            
            if (!$user) {
                jsonResponse(false, null, 'Email ou senha inválidos');
            }
            
            // Verificar a senha
            if (!password_verify($password, $user['password'])) {
                error_log("Senha inválida para usuário: $email");
                jsonResponse(false, null, 'Email ou senha inválidos');
            }
            
            // Atualizar último login
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Remove a senha antes de armazenar na sessão
            unset($user['password']);
            $_SESSION['user'] = $user;
            
            error_log("Login bem sucedido para usuário: " . $user['email']);
            
            jsonResponse(true, [
                'user' => $user,
                'redirect' => '../index.php'
            ], 'Login realizado com sucesso');
            
        } catch (PDOException $e) {
            error_log('Erro no login: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            jsonResponse(false, null, 'Erro ao fazer login: ' . $e->getMessage());
        }
        break;

    default:
        jsonResponse(false, null, 'Ação inválida');
}
