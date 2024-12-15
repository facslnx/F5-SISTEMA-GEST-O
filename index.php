<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];

// Definir datas do mês atual
$data_inicio = date('Y-m-01'); // Primeiro dia do mês atual
$data_fim = date('Y-m-t'); // Último dia do mês atual

// Parâmetros do filtro
$where_conditions = ["i.status = 1"];
$params = [];

// Filtro de cliente
if (!empty($_GET['client_id'])) {
    $where_conditions[] = "i.client_id = ?";
    $params[] = $_GET['client_id'];
}

// Filtro de data
if (!empty($_GET['data_inicio'])) {
    $data_inicio = $_GET['data_inicio'];
}
if (!empty($_GET['data_fim'])) {
    $data_fim = $_GET['data_fim'];
}

$where_conditions[] = "i.data_vencimento BETWEEN ? AND ?";
$params[] = $data_inicio;
$params[] = $data_fim;

// Filtro de status de pagamento
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

// Buscar estatísticas
$stats = [
    'clientes' => $pdo->query("SELECT COUNT(*) as total FROM clients WHERE status = 1")->fetch()['total'],
    'servicos' => $pdo->query("SELECT COUNT(*) as total FROM services WHERE status = 1")->fetch()['total'],
    'faturas_pendentes' => $pdo->query("SELECT COUNT(*) as total FROM invoices WHERE status = 1 AND data_pagamento IS NULL")->fetch()['total'],
    'faturas_pagas' => $pdo->query("SELECT COUNT(*) as total FROM invoices WHERE status = 1 AND data_pagamento IS NOT NULL")->fetch()['total']
];

// Calcular porcentagem de faturas pagas
$total_faturas = $stats['faturas_pendentes'] + $stats['faturas_pagas'];
$porcentagem_pagas = $total_faturas > 0 ? round(($stats['faturas_pagas'] / $total_faturas) * 100) : 0;

// Buscar clientes para o select
$stmt = $pdo->query("SELECT id, empresa FROM clients WHERE status = 1 ORDER BY empresa");
$clientes = $stmt->fetchAll();

// Buscar faturas do mês atual
$sql = "
    SELECT i.*, c.empresa as client_name, 
           DATEDIFF(i.data_vencimento, CURDATE()) as dias_restantes
    FROM invoices i
    JOIN clients c ON i.client_id = c.id
    WHERE " . implode(" AND ", $where_conditions) . "
    ORDER BY i.data_vencimento ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$faturas_mes = $stmt->fetchAll();

// Calcular total do mês
$total_mes = array_reduce($faturas_mes, function($carry, $item) {
    return $carry + $item['valor_total'];
}, 0);

// Calcular totais das faturas filtradas
$sql_totais = "
    SELECT 
        COUNT(*) as total_faturas,
        SUM(valor_original) as total_valor_original,
        SUM(desconto) as total_descontos,
        SUM(valor_total) as total_geral,
        SUM(CASE WHEN data_pagamento IS NOT NULL THEN valor_total ELSE 0 END) as total_pago,
        SUM(CASE WHEN data_pagamento IS NULL THEN valor_total ELSE 0 END) as total_pendente,
        COUNT(CASE WHEN data_pagamento IS NOT NULL THEN 1 END) as qtd_pagas,
        COUNT(CASE WHEN data_pagamento IS NULL THEN 1 END) as qtd_pendentes
    FROM invoices i
    WHERE " . implode(" AND ", $where_conditions);

$stmt_totais = $pdo->prepare($sql_totais);
$stmt_totais->execute($params);
$totais = $stmt_totais->fetch();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F5 GESTÃO - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #f5f5f5;
            color: #333;
        }
        .black-theme {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card .card-title {
            color: #000;
            font-weight: 600;
            border-bottom: 2px solid #ff0000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .stats-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .total-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .total-item {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            height: 100%;
            transition: transform 0.2s;
        }
        .total-item:hover {
            transform: translateY(-2px);
        }
        .total-label {
            display: block;
            font-size: 0.9em;
            color: #666;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .total-value {
            display: block;
            font-size: 1.6em;
            font-weight: bold;
            color: #000;
        }
        .total-value.highlight {
            color: #ff0000;
        }
        .total-count {
            display: block;
            font-size: 0.8em;
            color: #888;
            margin-top: 5px;
        }
        .filter-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-filter {
            background-color: #ff0000;
            color: white;
        }
        .btn-filter:hover {
            background-color: #cc0000;
        }
        .btn-clear {
            background-color: #f8f9fa;
            color: #333;
        }
        .btn-clear:hover {
            background-color: #e9ecef;
        }
        table.highlight tbody tr:hover {
            background-color: #f8f9fa;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 3px;
            color: white !important;
            font-weight: bold;
            display: inline-block;
            text-align: center;
            min-width: 100px;
        }
        .status-paid { 
            background-color: #4CAF50; 
        }
        .status-pending { 
            background-color: #FF9800; 
        }
        .status-overdue { 
            background-color: #f44336; 
        }
    </style>
</head>
<body>
    <?php require_once 'components/header.php'; ?>

    <div class="container">
        <div class="row" style="margin-top: 20px;">
            <!-- Cards de Estatísticas -->
            <div class="col s12 m6 l3">
                <div class="stats-card">
                    <i class="material-icons red-accent card-icon">business</i>
                    <div class="dashboard-stat"><?= $stats['clientes'] ?></div>
                    <div class="dashboard-label">Clientes Ativos</div>
                </div>
            </div>
            <div class="col s12 m6 l3">
                <div class="stats-card">
                    <i class="material-icons red-accent card-icon">assignment</i>
                    <div class="dashboard-stat"><?= $stats['servicos'] ?></div>
                    <div class="dashboard-label">Serviços Ativos</div>
                </div>
            </div>
            <div class="col s12 m6 l3">
                <div class="stats-card">
                    <i class="material-icons red-accent card-icon">warning</i>
                    <div class="dashboard-stat"><?= $stats['faturas_pendentes'] ?></div>
                    <div class="dashboard-label">Faturas Pendentes</div>
                </div>
            </div>
            <div class="col s12 m6 l3">
                <div class="stats-card">
                    <i class="material-icons red-accent card-icon">trending_up</i>
                    <div class="dashboard-stat"><?= $porcentagem_pagas ?>%</div>
                    <div class="dashboard-label">Taxa de Conclusão</div>
                    <div class="progress">
                        <div class="determinate" style="width: <?= $porcentagem_pagas ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Faturas do Mês -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Faturas do Mês (<?= date('m/Y') ?>)</span>
                        
                        <!-- Totais -->
                        <div class="total-section">
                            <div class="row">
                                <div class="col s12 m6 l3">
                                    <div class="total-item">
                                        <span class="total-label">Total Geral</span>
                                        <span class="total-value highlight">R$ <?= number_format($totais['total_geral'], 2, ',', '.') ?></span>
                                    </div>
                                </div>
                                <div class="col s12 m6 l3">
                                    <div class="total-item">
                                        <span class="total-label">Total Pago</span>
                                        <span class="total-value">R$ <?= number_format($totais['total_pago'], 2, ',', '.') ?></span>
                                        <span class="total-count"><?= $totais['qtd_pagas'] ?> faturas pagas</span>
                                    </div>
                                </div>
                                <div class="col s12 m6 l3">
                                    <div class="total-item">
                                        <span class="total-label">Total Pendente</span>
                                        <span class="total-value">R$ <?= number_format($totais['total_pendente'], 2, ',', '.') ?></span>
                                        <span class="total-count"><?= $totais['qtd_pendentes'] ?> faturas pendentes</span>
                                    </div>
                                </div>
                                <div class="col s12 m6 l3">
                                    <div class="total-item">
                                        <span class="total-label">Total Descontos</span>
                                        <span class="total-value">R$ <?= number_format($totais['total_descontos'], 2, ',', '.') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filtros -->
                        <div class="filter-section">
                            <form method="GET" class="row">
                                <div class="input-field col s12 m3">
                                    <select name="client_id">
                                        <option value="">Todos os Clientes</option>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?= $cliente['id'] ?>" <?= isset($_GET['client_id']) && $_GET['client_id'] == $cliente['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cliente['empresa']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label>Cliente</label>
                                </div>
                                <div class="input-field col s12 m3">
                                    <input type="date" name="data_inicio" value="<?= $data_inicio ?>" class="white-text">
                                    <label>Data Início</label>
                                </div>
                                <div class="input-field col s12 m3">
                                    <input type="date" name="data_fim" value="<?= $data_fim ?>" class="white-text">
                                    <label>Data Fim</label>
                                </div>
                                <div class="input-field col s12 m3">
                                    <select name="status_pagamento">
                                        <option value="">Todos os Status</option>
                                        <option value="pendente" <?= isset($_GET['status_pagamento']) && $_GET['status_pagamento'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                        <option value="pago" <?= isset($_GET['status_pagamento']) && $_GET['status_pagamento'] == 'pago' ? 'selected' : '' ?>>Pago</option>
                                        <option value="vencido" <?= isset($_GET['status_pagamento']) && $_GET['status_pagamento'] == 'vencido' ? 'selected' : '' ?>>Vencido</option>
                                    </select>
                                    <label>Status</label>
                                </div>
                                <div class="col s12" style="margin-top: 20px;">
                                    <button type="submit" class="btn btn-filter">
                                        <i class="material-icons left">search</i>Filtrar
                                    </button>
                                    <a href="index.php" class="btn btn-clear">
                                        <i class="material-icons left">clear</i>Limpar
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- Total do Mês -->
                        <div class="month-total">
                            Total do Período: R$ <?= number_format($total_mes, 2, ',', '.') ?>
                        </div>

                        <table class="highlight responsive-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Valor</th>
                                    <th>Vencimento</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($faturas_mes as $fatura): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($fatura['client_name']) ?></td>
                                        <td>R$ <?= number_format($fatura['valor_total'], 2, ',', '.') ?></td>
                                        <td><?= date('d/m/Y', strtotime($fatura['data_vencimento'])) ?></td>
                                        <td>
                                            <?php
                                            if ($fatura['data_pagamento']) {
                                                echo '<span class="status-badge status-paid">Pago</span>';
                                            } elseif (strtotime($fatura['data_vencimento']) < strtotime('today')) {
                                                echo '<span class="status-badge status-overdue">Vencido</span>';
                                            } else {
                                                echo '<span class="status-badge status-pending">Pendente</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="invoices/view.php?id=<?= $fatura['id'] ?>" class="btn-small waves-effect waves-light" title="Visualizar">
                                                <i class="material-icons">visibility</i>
                                            </a>
                                            <?php if (!$fatura['data_pagamento']): ?>
                                                <button class="btn-small green waves-effect waves-light btn-pay" 
                                                        data-id="<?= $fatura['id'] ?>" 
                                                        title="Marcar como pago">
                                                    <i class="material-icons">paid</i>
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
                        url: 'api/invoices.php',
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
        });
    </script>
</body>
</html>
