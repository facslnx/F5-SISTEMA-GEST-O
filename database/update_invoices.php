<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Ler o arquivo SQL
    $sql = file_get_contents(__DIR__ . '/update_invoices.sql');
    
    // Executar as queries
    $pdo->exec($sql);
    
    echo "Tabela de faturas atualizada com sucesso!\n";
    echo "View para total geral criada com sucesso!\n";
    
} catch (PDOException $e) {
    echo "Erro ao atualizar banco de dados: " . $e->getMessage() . "\n";
    exit(1);
}
