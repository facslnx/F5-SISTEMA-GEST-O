<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Ler o arquivo SQL
    $sql = file_get_contents(__DIR__ . '/update_users.sql');
    
    // Executar as queries
    $pdo->exec($sql);
    
    echo "Banco de dados atualizado com sucesso!\n";
    
} catch (PDOException $e) {
    echo "Erro ao atualizar banco de dados: " . $e->getMessage() . "\n";
    exit(1);
}
