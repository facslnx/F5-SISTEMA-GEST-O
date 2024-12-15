<?php
require_once __DIR__ . '/../database/connection.php';

if (!function_exists('jsonResponse')) {
    function jsonResponse($success = false, $data = null, $message = null) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'message' => $message
        ]);
        exit;
    }
}

if (!function_exists('checkLogin')) {
    function checkLogin() {
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
            if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                exit;
            } else {
                header('Location: /F5-SISTEMA-NOVO/login.php');
                exit;
            }
        }

        // Verificar se o usuário existe no banco
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user']['id']]);
            if (!$stmt->fetch()) {
                session_destroy();
                if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                    exit;
                } else {
                    header('Location: /F5-SISTEMA-NOVO/login.php');
                    exit;
                }
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erro ao verificar usuário']);
                exit;
            } else {
                header('Location: /F5-SISTEMA-NOVO/login.php');
                exit;
            }
        }
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($value) {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date) {
        return date('d/m/Y', strtotime($date));
    }
}

if (!function_exists('formatMoney')) {
    function formatMoney($value) {
        return number_format($value, 2, ',', '.');
    }
}

if (!function_exists('formatDateTime')) {
    function formatDateTime($datetime) {
        return date('d/m/Y H:i:s', strtotime($datetime));
    }
}
