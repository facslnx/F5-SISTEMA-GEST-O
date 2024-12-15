<?php
require_once '../utils/functions.php';
require_once '../database/connection.php';

session_start();
checkLogin();

$pdo = getConnection();

// Buscar todos os clientes para o select
$stmt = $pdo->query("SELECT id, empresa FROM clients WHERE status = 1 ORDER BY empresa");
$clientes = $stmt->fetchAll();

// Parâmetros do filtro
$where_conditions = ["i.status = 1"];
$params = [];

if (!empty($_GET['client_id'])) {
    $where_conditions[] = "i.client_id = ?";
    $params[] = $_GET['client_id'];
}

if (!empty($_GET['data_inicio'])) {
    $where_conditions[] = "i.data_vencimento >= ?";
    $params[] = $_GET['data_inicio'];
}

if (!empty($_GET['data_fim'])) {
    $where_conditions[] = "i.data_vencimento <= ?";
    $params[] = $_GET['data_fim'];
}

if (isset($_GET['status_pagamento']) && $_GET['status_pagamento'] !== '') {
    switch ($_GET['status_pagamento']) {
        case 'pago':
            $where_conditions[] = "i.data_pagamento IS NOT NULL";
            break;
        case 'pendente':
            $where_conditions[] = "i.data_pagamento IS NULL AND i.data_vencimento >= CURDATE()";
            break;
        case 'vencido':
            $where_conditions[] = "i.data_pagamento IS NULL AND i.data_vencimento < CURDATE()";
            break;
    }
}

// Calcular o total geral das faturas filtradas
$sql_total = "
    SELECT 
        SUM(valor_total) as valor_total_geral
    FROM invoices i
    WHERE " . implode(" AND ", $where_conditions);

$stmt_total = $pdo->prepare($sql_total);
$stmt_total->execute($params);
$totais = $stmt_total->fetch();
$valor_total_geral = floatval($totais['valor_total_geral']);

// Buscar faturas com filtros
$sql = "
    SELECT 
        i.*,
        c.empresa as client_name,
        GROUP_CONCAT(s.name) as services
    FROM invoices i
    JOIN clients c ON i.client_id = c.id
    LEFT JOIN invoice_services is2 ON i.id = is2.invoice_id
    LEFT JOIN services s ON is2.service_id = s.id
    WHERE " . implode(" AND ", $where_conditions) . "
    GROUP BY i.id
    ORDER BY i.data_vencimento DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$faturas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faturas - Sistema F5</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .bold {
            font-weight: bold;
        }
        td.bold {
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <?php require_once '../components/header.php'; ?>

    <div class="container">
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <!-- Filtros -->
                        <div class="row">
                            <form id="filterForm" method="GET" class="col s12">
                                <div class="row mb-0">
                                    <div class="input-field col s12 m3">
                                        <select name="client_id" id="client_id">
                                            <option value="">Todos os clientes</option>
                                            <?php foreach ($clientes as $cliente): ?>
                                                <option value="<?= $cliente['id'] ?>" <?= isset($_GET['client_id']) && $_GET['client_id'] == $cliente['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cliente['empresa']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label>Cliente</label>
                                    </div>
                                    
                                    <div class="input-field col s12 m2">
                                        <input type="date" id="data_inicio" name="data_inicio" 
                                               value="<?= $_GET['data_inicio'] ?? '' ?>">
                                        <label for="data_inicio">Data Inicial</label>
                                    </div>
                                    
                                    <div class="input-field col s12 m2">
                                        <input type="date" id="data_fim" name="data_fim" 
                                               value="<?= $_GET['data_fim'] ?? '' ?>">
                                        <label for="data_fim">Data Final</label>
                                    </div>
                                    
                                    <div class="input-field col s12 m3">
                                        <select name="status_pagamento" id="status_pagamento">
                                            <option value="">Todos os status</option>
                                            <option value="pendente" <?= isset($_GET['status_pagamento']) && $_GET['status_pagamento'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                            <option value="pago" <?= isset($_GET['status_pagamento']) && $_GET['status_pagamento'] == 'pago' ? 'selected' : '' ?>>Pago</option>
                                            <option value="vencido" <?= isset($_GET['status_pagamento']) && $_GET['status_pagamento'] == 'vencido' ? 'selected' : '' ?>>Vencido</option>
                                        </select>
                                        <label>Status</label>
                                    </div>
                                    
                                    <div class="input-field col s12 m2">
                                        <button type="submit" class="btn waves-effect waves-light red darken-1">
                                            <i class="material-icons left">search</i>
                                            Filtrar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Resumo dos Valores -->
                        <div class="row">
                            <div class="col s12">
                                <div class="card-panel blue-grey lighten-5">
                                    <div class="row mb-0">
                                        <div class="col s12">
                                            <h6 class="blue-grey-text">Total Geral das Faturas:</h6>
                                            <h4 class="blue-grey-text text-darken-2">R$ <?= number_format($valor_total_geral, 2, ',', '.') ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="card-title">Faturas</span>
                        </div>

                        <table class="highlight responsive-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Serviços</th>
                                    <th>Valor Original</th>
                                    <th>Desconto</th>
                                    <th>Total</th>
                                    <th>Vencimento</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($faturas as $fatura): ?>
                                    <?php
                                    $valor_original = floatval($fatura['valor_original']);
                                    $desconto = floatval($fatura['desconto']);
                                    $valor_total = floatval($fatura['valor_total']);
                                    
                                    // Define a classe CSS baseada no status de pagamento
                                    $valor_class = '';
                                    if ($fatura['data_pagamento']) {
                                        $valor_class = 'green-text text-darken-2';
                                    } elseif (strtotime($fatura['data_vencimento']) < time()) {
                                        $valor_class = 'red-text text-darken-2';
                                    } else {
                                        $valor_class = 'blue-text text-darken-2';
                                    }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($fatura['client_name']) ?></td>
                                        <td><?= htmlspecialchars($fatura['services']) ?></td>
                                        <td>R$ <?= number_format($valor_original, 2, ',', '.') ?></td>
                                        <td>R$ <?= number_format($desconto, 2, ',', '.') ?></td>
                                        <td class="<?= $valor_class ?> bold">
                                            <strong>R$ <?= number_format($valor_total, 2, ',', '.') ?></strong>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($fatura['data_vencimento'])) ?></td>
                                        <td>
                                            <?php if ($fatura['data_pagamento']): ?>
                                                <span class="new badge green" data-badge-caption="">Pago</span>
                                            <?php else: ?>
                                                <?php if (strtotime($fatura['data_vencimento']) < strtotime('today')): ?>
                                                    <span class="new badge red" data-badge-caption="">Vencido</span>
                                                <?php else: ?>
                                                    <span class="new badge orange" data-badge-caption="">Pendente</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="view.php?id=<?= $fatura['id'] ?>" class="btn-small waves-effect waves-light" title="Visualizar">
                                                <i class="material-icons">visibility</i>
                                            </a>
                                            <?php if (!$fatura['data_pagamento']): ?>
                                                <button class="btn-small green waves-effect waves-light btn-pay" 
                                                        data-id="<?= $fatura['id'] ?>" 
                                                        title="Marcar como pago">
                                                    <i class="material-icons">paid</i>
                                                </button>
                                                <button class="btn-small red waves-effect waves-light btn-cancel" 
                                                        data-id="<?= $fatura['id'] ?>"
                                                        title="Cancelar">
                                                    <i class="material-icons">cancel</i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar selects do Materialize
            $('select').formSelect();

            // Inicializar datepickers
            var elems = document.querySelectorAll('.datepicker');
            M.Datepicker.init(elems, {
                format: 'yyyy-mm-dd',
                i18n: {
                    months: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                    monthsShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                    weekdays: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
                    weekdaysShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
                    weekdaysAbbrev: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
                    today: 'Hoje',
                    clear: 'Limpar',
                    cancel: 'Sair',
                    done: 'Confirmar'
                }
            });

            // Marcar fatura como paga
            $('.btn-pay').click(function() {
                if (confirm('Deseja marcar esta fatura como paga?')) {
                    const id = $(this).data('id');
                    $.ajax({
                        url: '../api/invoices.php',
                        type: 'POST',
                        data: {
                            action: 'mark_as_paid',
                            invoice_id: id
                        },
                        success: function(response) {
                            M.toast({html: response.message, classes: 'green'});
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            let error = xhr.responseJSON || {message: 'Erro ao marcar fatura como paga'};
                            M.toast({html: error.message, classes: 'red'});
                        }
                    });
                }
            });

            // Cancelar fatura
            $('.btn-cancel').click(function() {
                if (confirm('Deseja cancelar esta fatura?')) {
                    const id = $(this).data('id');
                    $.post('../api/invoices.php', {
                        action: 'cancel',
                        invoice_id: id
                    })
                    .done(function(response) {
                        if (response.success) {
                            M.toast({html: response.message, classes: 'green'});
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            M.toast({html: response.message, classes: 'red'});
                        }
                    })
                    .fail(function() {
                        M.toast({html: 'Erro ao comunicar com o servidor', classes: 'red'});
                    });
                }
            });
        });
    </script>
</body>
</html>
