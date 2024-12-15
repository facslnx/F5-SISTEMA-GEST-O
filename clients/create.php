<?php
require_once '../utils/functions.php';
require_once '../database/connection.php';

session_start();
checkLogin();

$pdo = getConnection();

// Buscar serviços disponíveis
$stmt = $pdo->query("SELECT * FROM services WHERE status = 1 ORDER BY name");
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Cliente</title>
    <?php require_once '../includes/head.php'; ?>
</head>
<body>
    <?php require_once '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="row">
            <div class="col s12">
                <h4>Novo Cliente</h4>
                
                <form id="clientForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="input-field col s12 m6">
                            <input type="text" id="empresa" name="empresa" required>
                            <label for="empresa">Nome da Empresa</label>
                        </div>
                        
                        <div class="input-field col s12 m6">
                            <input type="text" id="documento" name="documento">
                            <label for="documento">CNPJ/CPF</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <input type="text" id="endereco" name="endereco">
                            <label for="endereco">Endereço</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12 m6">
                            <input type="text" id="cidade" name="cidade">
                            <label for="cidade">Cidade</label>
                        </div>
                        
                        <div class="input-field col s12 m2">
                            <input type="text" id="estado" name="estado" maxlength="2">
                            <label for="estado">UF</label>
                        </div>
                        
                        <div class="input-field col s12 m4">
                            <input type="text" id="cep" name="cep">
                            <label for="cep">CEP</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12 m6">
                            <input type="text" id="responsavel" name="responsavel">
                            <label for="responsavel">Nome do Responsável</label>
                        </div>
                        
                        <div class="input-field col s12 m6">
                            <input type="text" id="telefone" name="telefone">
                            <label for="telefone">Telefone</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12 m6">
                            <input type="email" id="email" name="email">
                            <label for="email">E-mail</label>
                        </div>
                        
                        <div class="input-field col s12 m6">
                            <input type="number" id="valor_contrato" name="valor_contrato" step="0.01" min="0">
                            <label for="valor_contrato">Valor do Contrato</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12 m6">
                            <select id="plano_meses" name="plano_meses">
                                <option value="1">Mensal</option>
                                <option value="3">Trimestral</option>
                                <option value="6">Semestral</option>
                                <option value="12">Anual</option>
                            </select>
                            <label>Plano de Pagamento</label>
                        </div>
                        
                        <div class="file-field input-field col s12 m6">
                            <div class="btn">
                                <span>Contrato</span>
                                <input type="file" name="contrato" accept=".pdf,.doc,.docx">
                            </div>
                            <div class="file-path-wrapper">
                                <input class="file-path validate" type="text" placeholder="Faça upload do contrato">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12">
                            <h5>Serviços</h5>
                            <?php foreach ($services as $service): ?>
                                <p>
                                    <label>
                                        <input type="checkbox" name="services[]" value="<?php echo $service['id']; ?>" 
                                               class="filled-in service-checkbox" 
                                               data-value="<?php echo $service['value']; ?>">
                                        <span>
                                            <?php echo htmlspecialchars($service['name']); ?> - 
                                            R$ <?php echo number_format($service['value'], 2, ',', '.'); ?>
                                        </span>
                                    </label>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12">
                            <button type="submit" class="btn waves-effect waves-light">
                                Salvar
                                <i class="material-icons right">send</i>
                            </button>
                            <a href="index.php" class="btn waves-effect waves-light red">
                                Cancelar
                                <i class="material-icons right">cancel</i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once '../includes/scripts.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar select
            var elems = document.querySelectorAll('select');
            M.FormSelect.init(elems);

            // Máscara para telefone
            var telefone = document.getElementById('telefone');
            IMask(telefone, {
                mask: '(00) 00000-0000'
            });

            // Máscara para CEP
            var cep = document.getElementById('cep');
            IMask(cep, {
                mask: '00000-000'
            });

            // Máscara para CNPJ/CPF
            var documento = document.getElementById('documento');
            var mascaraDocumento = IMask(documento, {
                mask: [{
                    mask: '000.000.000-00',
                    maxLength: 11
                }, {
                    mask: '00.000.000/0000-00',
                    maxLength: 14
                }],
                dispatch: function (appended, dynamicMasked) {
                    var number = (dynamicMasked.value + appended).replace(/\D/g, '');
                    return number.length <= 11 ? dynamicMasked.compiledMasks[0] : dynamicMasked.compiledMasks[1];
                }
            });

            // Manipular envio do formulário
            document.getElementById('clientForm').addEventListener('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);
                formData.append('action', 'create');

                fetch('../api/clients.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        M.toast({html: 'Cliente criado com sucesso!', classes: 'green'});
                        setTimeout(() => {
                            window.location.href = 'index.php';
                        }, 2000);
                    } else {
                        M.toast({html: data.message || 'Erro ao criar cliente', classes: 'red'});
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    M.toast({html: 'Erro ao criar cliente', classes: 'red'});
                });
            });

            // Atualizar valor total ao selecionar serviços
            var checkboxes = document.querySelectorAll('.service-checkbox');
            var valorContratoInput = document.getElementById('valor_contrato');

            function updateTotal() {
                var total = 0;
                checkboxes.forEach(function(checkbox) {
                    if (checkbox.checked) {
                        total += parseFloat(checkbox.dataset.value);
                    }
                });
                valorContratoInput.value = total.toFixed(2);
            }

            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', updateTotal);
            });
        });
    </script>
</body>
</html>
