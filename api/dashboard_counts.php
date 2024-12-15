<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    // Contar clientes
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM clients WHERE status = 1");
    $clientCount = $stmt->fetch()['count'];

    // Contar serviços
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM services WHERE status = 1");
    $serviceCount = $stmt->fetch()['count'];

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'clients' => $clientCount,
            'services' => $serviceCount
        ]
    ]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar dados'
    ]);
}
