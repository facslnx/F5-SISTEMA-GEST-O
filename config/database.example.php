<?php
// Configurações do Banco de Dados
return [
    'host' => 'localhost',
    'dbname' => 'nome_do_banco',
    'username' => 'seu_usuario',
    'password' => 'sua_senha',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
