<?php
require_once '../utils/functions.php';
require_once '../database/connection.php';

session_start();
checkLogin();

$client_id = $_GET['id'] ?? 0;
if (!$client_id) {
    header('Location: index.php');
    exit;
}

$pdo = getConnection();

// Buscar dados do cliente
$stmt = $pdo->prepare("
    SELECT c.*, GROUP_CONCAT(s.id) as service_ids, GROUP_CONCAT(s.name) as service_names, GROUP_CONCAT(s.value) as service_values
    FROM clients c
    LEFT JOIN client_services cs ON c.id = cs.client_id
    LEFT JOIN services s ON cs.service_id = s.id
    WHERE c.id = ? AND c.status = 1
    GROUP BY c.id
");
$stmt->execute([$client_id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    header('Location: index.php');
    exit;
}

// Buscar todos os serviços ativos
$stmt = $pdo->query("SELECT * FROM services WHERE status = 1 ORDER BY name");
$servicos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Fatura - Sistema F5</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php require_once '../components/header.php'; ?>

    <div class="container">
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Nova Fatura - <?= htmlspecialchars($cliente['empresa']) ?></span>
                        
                        <form id="invoiceForm">
                            <input type="hidden" name="client_id" value="<?= $client_id ?>">
                            
                            <div class="row">
                                <div class="col s12">
                                    <h5>Serviços Disponíveis</h5>
                                    <?php foreach ($servicos as $servico): ?>
                                    <p>
                                        <label>
                                            <input type="checkbox" class="filled-in service-checkbox" 
                                                value="<?= $servico['id'] ?>" 
                                                data-name="<?= htmlspecialchars($servico['name']) ?>" 
                                                data-value="<?= $servico['value'] ?>">
                                            <span><?= htmlspecialchars($servico['name']) ?> - R$ <?= number_format($servico['value'], 2, ',', '.') ?></span>
                                        </label>
                                    </p>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="input-field col s6">
                                    <select name="periodo" id="periodo" required>
                                        <option value="1">1 mês</option>
                                        <option value="3">3 meses</option>
                                        <option value="6">6 meses</option>
                                        <option value="12">1 ano</option>
                                    </select>
                                    <label>Período de Faturamento</label>
                                </div>
                                
                                <div class="input-field col s6">
                                    <input type="text" id="data_vencimento" name="data_vencimento" class="datepicker" required>
                                    <label for="data_vencimento">Data do Primeiro Vencimento</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="input-field col s12">
                                    <input type="number" id="desconto" name="desconto" step="0.01" min="0" value="0">
                                    <label for="desconto">Desconto por Fatura (R$)</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col s12">
                                    <h5>Resumo</h5>
                                    <div id="servicosSelecionados" class="collection">
                                        <div class="collection-item">Nenhum serviço selecionado</div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col s12 m3">
                                            <label>Valor Total por Fatura</label>
                                            <div class="input-field">
                                                <input type="text" id="valorTotal" readonly value="R$ 0,00">
                                            </div>
                                        </div>
                                        <div class="col s12 m3">
                                            <label>Desconto por Fatura</label>
                                            <div class="input-field">
                                                <input type="text" id="valorDesconto" readonly value="R$ 0,00">
                                            </div>
                                        </div>
                                        <div class="col s12 m3">
                                            <label>Valor Final por Fatura</label>
                                            <div class="input-field">
                                                <input type="text" id="valorFinal" readonly value="R$ 0,00">
                                            </div>
                                        </div>
                                        <div class="col s12 m3">
                                            <label>Valor Total do Período</label>
                                            <div class="input-field">
                                                <input type="text" id="valorTotalPeriodo" readonly value="R$ 0,00">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col s12 m3">
                                            <label>Número de Faturas</label>
                                            <div class="input-field">
                                                <input type="text" id="numFaturas" readonly value="1">
                                            </div>
                                        </div>
                                    </div>

                                    <div id="previewFaturas" class="collection with-header">
                                        <div class="collection-header"><h5>Previsão de Faturas</h5></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col s12">
                                    <button type="submit" class="btn red darken-1 waves-effect waves-light">
                                        <i class="material-icons left">save</i>
                                        Gerar Fatura
                                    </button>
                                    <a href="index.php" class="btn grey waves-effect waves-light">
                                        <i class="material-icons left">arrow_back</i>
                                        Voltar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar componentes do Materialize
            $('select').formSelect();
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                defaultDate: new Date(),
                setDefaultDate: true,
                minDate: new Date(),
                i18n: {
                    months: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                    monthsShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                    weekdays: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
                    weekdaysShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
                    weekdaysAbbrev: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
                    cancel: 'Cancelar',
                    clear: 'Limpar',
                    done: 'Ok'
                }
            });

            // Função para formatar valor em reais
            function formatMoney(value) {
                return `R$ ${value.toFixed(2).replace('.', ',')}`;
            }

            // Função para calcular datas de vencimento
            function calcularDatasVencimento() {
                const dataInicial = $('#data_vencimento').val();
                if (!dataInicial) return [];
                
                const periodo = parseInt($('#periodo').val()) || 0;
                const datas = [];
                
                for (let i = 0; i < periodo; i++) {
                    const data = new Date(dataInicial);
                    data.setMonth(data.getMonth() + i);
                    datas.push(data.toISOString().split('T')[0]);
                }
                
                return datas;
            }

            // Atualizar totais quando serviços são selecionados
            function atualizarTotais() {
                let valorTotal = 0;
                let servicosSelecionados = [];

                $('.service-checkbox:checked').each(function() {
                    const valor = parseFloat($(this).data('value'));
                    const nome = $(this).data('name');
                    valorTotal += valor;
                    servicosSelecionados.push(`<div class="collection-item">${nome} - ${formatMoney(valor)}</div>`);
                });

                const desconto = parseFloat($('#desconto').val()) || 0;
                const valorFinal = Math.max(0, valorTotal - desconto);
                const periodo = parseInt($('#periodo').val()) || 1;
                const valorTotalPeriodo = valorFinal * periodo;

                $('#valorTotal').val(formatMoney(valorTotal));
                $('#valorDesconto').val(formatMoney(desconto));
                $('#valorFinal').val(formatMoney(valorFinal));
                $('#valorTotalPeriodo').val(formatMoney(valorTotalPeriodo));
                $('#numFaturas').val(periodo);

                if (servicosSelecionados.length > 0) {
                    $('#servicosSelecionados').html(servicosSelecionados.join(''));
                } else {
                    $('#servicosSelecionados').html('<div class="collection-item">Nenhum serviço selecionado</div>');
                }

                // Atualizar preview de faturas
                const datas = calcularDatasVencimento();
                let previewHtml = '<div class="collection-header"><h5>Previsão de Faturas</h5></div>';
                
                datas.forEach((data, index) => {
                    const dataFormatada = new Date(data).toLocaleDateString('pt-BR');
                    previewHtml += `
                        <div class="collection-item">
                            <div class="row" style="margin-bottom: 0;">
                                <div class="col s6">Fatura ${index + 1} - Vencimento: ${dataFormatada}</div>
                                <div class="col s6 right-align">
                                    <div>Valor: ${formatMoney(valorTotal)}</div>
                                    <div>Desconto: ${formatMoney(desconto)}</div>
                                    <div><strong>Total: ${formatMoney(valorFinal)}</strong></div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                $('#previewFaturas').html(previewHtml);
            }

            $('.service-checkbox, #desconto, #periodo, #data_vencimento').on('change input', atualizarTotais);

            // Enviar formulário
            $('#invoiceForm').submit(function(e) {
                e.preventDefault();

                const servicos = [];
                $('.service-checkbox:checked').each(function() {
                    servicos.push({
                        id: $(this).val(),
                        value: parseFloat($(this).data('value'))
                    });
                });

                if (servicos.length === 0) {
                    M.toast({html: 'Selecione pelo menos um serviço', classes: 'red'});
                    return;
                }

                if (!$('#data_vencimento').val()) {
                    M.toast({html: 'Selecione a data de vencimento', classes: 'red'});
                    return;
                }

                const data = {
                    action: 'create',
                    client_id: $('input[name="client_id"]').val(),
                    data_vencimento: $('#data_vencimento').val(),
                    desconto: parseFloat($('#desconto').val()) || 0,
                    periodo: parseInt($('#periodo').val()),
                    servicos: JSON.stringify(servicos)
                };

                // Desabilitar o botão de submit
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="material-icons left">hourglass_empty</i>Processando...');

                $.ajax({
                    url: '../api/invoices.php',
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            M.toast({html: response.message, classes: 'green'});
                            setTimeout(() => window.location.href = '../invoices/index.php', 1000);
                        } else {
                            M.toast({html: response.message || 'Erro ao gerar faturas', classes: 'red'});
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro na requisição:', error);
                        console.error('Status:', status);
                        console.error('Resposta:', xhr.responseText);
                        M.toast({html: 'Erro ao conectar com o servidor. Tente novamente.', classes: 'red'});
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
</body>
</html>
