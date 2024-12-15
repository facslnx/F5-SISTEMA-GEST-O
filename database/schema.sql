-- Criar o banco de dados
DROP DATABASE IF EXISTS f5_sistema;
CREATE DATABASE f5_sistema;
USE f5_sistema;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir usuário padrão (senha: admin123)
INSERT INTO users (name, email, password) VALUES 
('Administrador', 'admin@f5.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Tabela de serviços
CREATE TABLE IF NOT EXISTS services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir serviços padrão
INSERT INTO services (name, description, value) VALUES
('Hospedagem', 'Serviço de hospedagem web', 50.00),
('Domínio', 'Registro de domínio', 40.00),
('SSL', 'Certificado SSL', 30.00),
('Email', 'Serviço de email', 20.00);

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empresa VARCHAR(100) NOT NULL,
    documento VARCHAR(20),
    endereco TEXT,
    cidade VARCHAR(100),
    estado CHAR(2),
    cep VARCHAR(10),
    responsavel VARCHAR(100),
    telefone VARCHAR(20),
    email VARCHAR(100),
    valor_contrato DECIMAL(10,2) DEFAULT 0.00,
    contrato_url VARCHAR(255),
    status BOOLEAN DEFAULT TRUE,
    user_id INT,
    plano_meses INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabela de relacionamento entre clientes e serviços
CREATE TABLE IF NOT EXISTS client_services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    service_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    UNIQUE KEY unique_client_service (client_id, service_id)
);

-- Tabela de faturas
CREATE TABLE IF NOT EXISTS invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    desconto DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pendente',
    data_vencimento DATE NOT NULL,
    data_pagamento TIMESTAMP NULL,
    observacao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- Tabela de serviços das faturas
CREATE TABLE IF NOT EXISTS invoice_services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    service_id INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id),
    UNIQUE KEY unique_invoice_service (invoice_id, service_id)
);

-- Criar índices para melhor performance
ALTER TABLE clients ADD INDEX idx_clients_empresa (empresa);
ALTER TABLE clients ADD INDEX idx_clients_status (status);
ALTER TABLE services ADD INDEX idx_services_status (status);
ALTER TABLE invoices ADD INDEX idx_invoices_client (client_id);
ALTER TABLE invoices ADD INDEX idx_invoices_status (status);
ALTER TABLE invoices ADD INDEX idx_invoices_vencimento (data_vencimento);
