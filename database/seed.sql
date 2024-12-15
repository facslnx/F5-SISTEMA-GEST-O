USE f5_sistema;

-- Inserir usuário admin padrão (senha: admin123)
INSERT INTO users (name, email, password) VALUES
('Admin', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Inserir alguns serviços de exemplo
INSERT INTO services (name, description, value, status) VALUES
('Desenvolvimento Web', 'Desenvolvimento de sites e aplicações web', 5000.00, true),
('Design Gráfico', 'Criação de identidade visual e materiais gráficos', 2500.00, true),
('Marketing Digital', 'Gestão de redes sociais e campanhas online', 1800.00, true),
('Consultoria', 'Consultoria em tecnologia e processos', 3000.00, true);

-- Inserir alguns clientes de exemplo
INSERT INTO clients (empresa, documento, endereco, cidade, estado, cep, responsavel, telefone, email, valor_contrato, status, user_id) VALUES
('Empresa A', '12.345.678/0001-90', 'Rua A, 123', 'São Paulo', 'SP', '01234-567', 'João Silva', '(11) 98765-4321', 'joao@empresaa.com', 5000.00, true, 1),
('Empresa B', '98.765.432/0001-10', 'Rua B, 456', 'Rio de Janeiro', 'RJ', '12345-678', 'Maria Santos', '(21) 98765-4321', 'maria@empresab.com', 3500.00, true, 1);

-- Vincular alguns serviços aos clientes
INSERT INTO client_services (client_id, service_id) VALUES
(1, 1),
(1, 2),
(2, 3),
(2, 4);
