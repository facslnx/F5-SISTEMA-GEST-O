<?php
require_once '../utils/functions.php';
require_once '../database/connection.php';

session_start();
checkLogin();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Cliente - Sistema F5</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .file-field .btn {
            height: 36px;
            line-height: 36px;
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
                        <span class="card-title">Novo Cliente</span>
                        
                        <form id="clientForm" enctype="multipart/form-data">
                            <div class="row">
                                <!-- Dados do Cliente -->
                                <div class="col s12">
                                    <h5>Dados do Cliente</h5>
                                    
                                    <div class="input-field">
                                        <input type="text" id="empresa" name="empresa" required>
                                        <label for="empresa">Nome da Empresa *</label>
                                    </div>
                                    
                                    <div class="input-field">
                                        <input type="text" id="documento" name="documento">
                                        <label for="documento">CNPJ/CPF</label>
                                    </div>
                                    
                                    <div class="input-field">
                                        <input type="text" id="endereco" name="endereco">
                                        <label for="endereco">Endereço</label>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col s6">
                                            <div class="input-field">
                                                <input type="text" id="cidade" name="cidade">
                                                <label for="cidade">Cidade</label>
                                            </div>
                                        </div>
                                        <div class="col s3">
                                            <div class="input-field">
                                                <input type="text" id="estado" name="estado" maxlength="2">
                                                <label for="estado">Estado</label>
                                            </div>
                                        </div>
                                        <div class="col s3">
                                            <div class="input-field">
                                                <input type="text" id="cep" name="cep">
                                                <label for="cep">CEP</label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="input-field">
                                        <input type="text" id="responsavel" name="responsavel">
                                        <label for="responsavel">Responsável</label>
                                    </div>
                                    
                                    <div class="input-field">
                                        <input type="text" id="telefone" name="telefone">
                                        <label for="telefone">Telefone</label>
                                    </div>
                                    
                                    <div class="input-field">
                                        <input type="email" id="email" name="email">
                                        <label for="email">Email</label>
                                    </div>

                                    <div class="file-field input-field">
                                        <div class="btn red darken-1">
                                            <span>Contrato</span>
                                            <input type="file" name="contrato" id="contrato" accept=".pdf,.png,.jpg,.jpeg">
                                        </div>
                                        <div class="file-path-wrapper">
                                            <input class="file-path validate" type="text" placeholder="Faça upload do contrato (PDF ou Imagem)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col s12">
                                    <button type="submit" class="btn red darken-1 waves-effect waves-light">
                                        <i class="material-icons left">save</i>
                                        Salvar
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
            // Enviar formulário
            $('#clientForm').submit(function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'create');
                
                $.ajax({
                    url: '../api/clients.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            M.toast({html: 'Cliente cadastrado com sucesso!'});
                            window.location.href = 'index.php';
                        } else {
                            M.toast({html: response.message || 'Erro ao cadastrar cliente'});
                        }
                    },
                    error: function() {
                        M.toast({html: 'Erro ao conectar com o servidor'});
                    }
                });
            });
        });
    </script>
</body>
</html>
