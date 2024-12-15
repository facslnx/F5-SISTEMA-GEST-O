CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir usuário de teste (senha: fernanduh123)
INSERT INTO users (name, email, password) VALUES 
('Fernando', 'fernando@f5desenvolve.com.br', '$2y$10$kKjFBTXVOPZCxvQM9LFcwOQZYLOVSsKmE6GVoC3UxSZmBQkZXQxDa');
