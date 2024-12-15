<?php
require_once '../utils/functions.php';
require_once '../database/connection.php';

header('Content-Type: application/json');

try {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        jsonResponse(false, null, 'Por favor, preencha todos os campos');
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        jsonResponse(false, null, 'E-mail ou senha inválidos');
    }

    // Iniciar sessão e guardar dados do usuário
    session_start();
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role']
    ];

    jsonResponse(true, ['redirect' => '/index.php'], 'Login realizado com sucesso');
} catch (Exception $e) {
    jsonResponse(false, null, 'Erro ao realizar login: ' . $e->getMessage());
}
