<?php
require_once '../database/connection.php';
require_once '../utils/functions.php';

session_start();
checkLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getConnection();
        $action = $_POST['action'] ?? '';

        if ($action === 'mark_as_paid') {
            $invoice_id = filter_input(INPUT_POST, 'invoice_id', FILTER_VALIDATE_INT);
            
            if (!$invoice_id) {
                throw new Exception('ID da fatura inválido');
            }

            $stmt = $pdo->prepare("
                UPDATE invoices 
                SET data_pagamento = CURRENT_DATE(), 
                    updated_at = NOW() 
                WHERE id = ? AND status = 1
            ");
            
            if ($stmt->execute([$invoice_id])) {
                echo json_encode(['success' => true, 'message' => 'Fatura marcada como paga com sucesso']);
            } else {
                throw new Exception('Erro ao marcar fatura como paga');
            }
        } elseif ($action === 'cancel') {
            $invoice_id = filter_input(INPUT_POST, 'invoice_id', FILTER_VALIDATE_INT);
            
            if (!$invoice_id) {
                throw new Exception('ID da fatura inválido');
            }

            $stmt = $pdo->prepare("
                UPDATE invoices 
                SET status = 0,
                    updated_at = NOW() 
                WHERE id = ? AND status = 1
            ");
            
            if ($stmt->execute([$invoice_id])) {
                echo json_encode(['success' => true, 'message' => 'Fatura cancelada com sucesso']);
            } else {
                throw new Exception('Erro ao cancelar fatura');
            }
        } elseif ($action === 'create') {
            // Validar dados recebidos
            $client_id = filter_input(INPUT_POST, 'client_id', FILTER_VALIDATE_INT);
            $data_vencimento = htmlspecialchars(trim($_POST['data_vencimento'] ?? ''));
            $desconto = filter_input(INPUT_POST, 'desconto', FILTER_VALIDATE_FLOAT) ?? 0;
            $periodo = filter_input(INPUT_POST, 'periodo', FILTER_VALIDATE_INT);
            $servicos = json_decode($_POST['servicos'], true);

            if (!$client_id || !$data_vencimento || !$periodo || empty($servicos)) {
                throw new Exception('Dados inválidos');
            }

            // Calcular valor total
            $valor_total = 0;
            foreach ($servicos as $servico) {
                $valor_total += floatval($servico['value']);
            }

            // Iniciar transação
            $pdo->beginTransaction();

            try {
                // Criar faturas para cada mês do período
                $data_base = new DateTime($data_vencimento);
                
                for ($i = 0; $i < $periodo; $i++) {
                    // Criar fatura
                    $stmt = $pdo->prepare("
                        INSERT INTO invoices (
                            client_id, 
                            data_vencimento, 
                            valor_original,
                            desconto, 
                            status, 
                            created_at, 
                            updated_at
                        ) VALUES (?, ?, ?, ?, 1, NOW(), NOW())
                    ");
                    $stmt->execute([
                        $client_id,
                        $data_base->format('Y-m-d'),
                        $valor_total,
                        $desconto
                    ]);
                    
                    $invoice_id = $pdo->lastInsertId();

                    // Adicionar serviços à fatura
                    $stmt = $pdo->prepare("
                        INSERT INTO invoice_services (
                            invoice_id, 
                            service_id, 
                            value, 
                            created_at, 
                            updated_at
                        ) VALUES (?, ?, ?, NOW(), NOW())
                    ");

                    foreach ($servicos as $servico) {
                        $stmt->execute([
                            $invoice_id,
                            $servico['id'],
                            $servico['value']
                        ]);
                    }

                    // Avançar para o próximo mês
                    $data_base->modify('+1 month');
                }

                // Confirmar transação
                $pdo->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Faturas geradas com sucesso!'
                ]);

            } catch (Exception $e) {
                $pdo->rollBack();
                throw new Exception('Erro ao gerar faturas: ' . $e->getMessage());
            }
        } else {
            throw new Exception('Ação inválida');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
