<?php
require_once '../utils/functions.php';
require_once '../database/connection.php';

session_start();
checkLogin();

$invoice_id = $_GET['id'] ?? 0;
if (!$invoice_id) {
    header('Location: index.php');
    exit;
}

$pdo = getConnection();

// Buscar dados da fatura
$stmt = $pdo->prepare("
    SELECT 
        i.*,
        c.empresa as client_name,
        c.email as client_email,
        c.telefone as client_phone,
        c.endereco as client_address,
        c.cidade as client_city,
        c.estado as client_state,
        c.cep as client_zip
    FROM invoices i
    JOIN clients c ON i.client_id = c.id
    WHERE i.id = ? AND i.status = 1
");
$stmt->execute([$invoice_id]);
$fatura = $stmt->fetch();

if (!$fatura) {
    header('Location: index.php');
    exit;
}

// Buscar serviços da fatura
$stmt = $pdo->prepare("
    SELECT 
        s.name,
        s.description,
        is2.value
    FROM invoice_services is2
    JOIN services s ON is2.service_id = s.id
    WHERE is2.invoice_id = ?
");
$stmt->execute([$invoice_id]);
$servicos = $stmt->fetchAll();

// Calcular totais
$valor_total = array_sum(array_column($servicos, 'value'));
$valor_final = $valor_total - $fatura['desconto'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Fatura - Sistema F5</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .invoice-header {
            border-bottom: 2px solid #ddd;
            margin-bottom: 20px;
            padding-bottom: 20px;
        }
        .invoice-total {
            border-top: 2px solid #ddd;
            margin-top: 20px;
            padding-top: 20px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 3px;
            color: white;
            font-weight: bold;
            display: inline-block;
        }
        .status-paid { background-color: #4CAF50; }
        .status-pending { background-color: #FF9800; }
        .status-overdue { background-color: #f44336; }
    </style>
</head>
<body>
    <?php require_once '../components/header.php'; ?>

    <div class="container">
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <div class="invoice-header">
                            <div class="row">
                                <div class="col s6">
                                    <h4>Fatura #<?= str_pad($fatura['id'], 6, '0', STR_PAD_LEFT) ?></h4>
                                    <p>
                                        <strong>Data de Emissão:</strong> <?= date('d/m/Y', strtotime($fatura['created_at'])) ?><br>
                                        <strong>Vencimento:</strong> <?= date('d/m/Y', strtotime($fatura['data_vencimento'])) ?>
                                    </p>
                                    <?php if ($fatura['data_pagamento']): ?>
                                        <span class="status-badge status-paid">PAGO</span>
                                    <?php else: ?>
                                        <?php if (strtotime($fatura['data_vencimento']) < strtotime('today')): ?>
                                            <span class="status-badge status-overdue">VENCIDO</span>
                                        <?php else: ?>
                                            <span class="status-badge status-pending">PENDENTE</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="col s6">
                                    <h5>Cliente</h5>
                                    <p>
                                        <strong><?= htmlspecialchars($fatura['client_name']) ?></strong><br>
                                        <?= htmlspecialchars($fatura['client_email']) ?><br>
                                        <?= htmlspecialchars($fatura['client_phone']) ?><br>
                                        <?= htmlspecialchars($fatura['client_address']) ?><br>
                                        <?= htmlspecialchars($fatura['client_city']) ?> - <?= htmlspecialchars($fatura['client_state']) ?><br>
                                        CEP: <?= htmlspecialchars($fatura['client_zip']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <table class="striped">
                            <thead>
                                <tr>
                                    <th>Serviço</th>
                                    <th>Descrição</th>
                                    <th class="right-align">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicos as $servico): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($servico['name']) ?></td>
                                        <td><?= htmlspecialchars($servico['description']) ?></td>
                                        <td class="right-align">R$ <?= number_format($servico['value'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="invoice-total">
                            <div class="row">
                                <div class="col s12">
                                    <table class="right-align" style="width: 300px; margin-left: auto;">
                                        <tr>
                                            <td><strong>Subtotal:</strong></td>
                                            <td>R$ <?= number_format($valor_total, 2, ',', '.') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Desconto:</strong></td>
                                            <td>R$ <?= number_format($fatura['desconto'], 2, ',', '.') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total:</strong></td>
                                            <td><strong>R$ <?= number_format($valor_final, 2, ',', '.') ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 20px;">
                            <div class="col s12">
                                <a href="index.php" class="btn grey waves-effect waves-light">
                                    <i class="material-icons left">arrow_back</i>
                                    Voltar
                                </a>
                                <?php if (!$fatura['data_pagamento']): ?>
                                <button onclick="markAsPaid(<?= $fatura['id'] ?>)" class="btn green waves-effect waves-light">
                                    <i class="material-icons left">check</i>
                                    Marcar como Pago
                                </button>
                                <?php endif; ?>
                                <button class="btn blue waves-effect waves-light right" onclick="window.print()">
                                    <i class="material-icons left">print</i>
                                    Imprimir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            M.AutoInit();
        });

        function markAsPaid(invoiceId) {
            if (confirm('Deseja realmente marcar esta fatura como paga?')) {
                $.ajax({
                    url: '../api/invoices.php',
                    type: 'POST',
                    data: {
                        action: 'mark_as_paid',
                        invoice_id: invoiceId
                    },
                    success: function(response) {
                        M.toast({html: response.message, classes: 'green'});
                        setTimeout(() => window.location.reload(), 1000);
                    },
                    error: function(xhr) {
                        let error = xhr.responseJSON || {message: 'Erro ao marcar fatura como paga'};
                        M.toast({html: error.message, classes: 'red'});
                    }
                });
            }
        }
    </script>
</body>
</html>
