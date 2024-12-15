-- Adicionar coluna role à tabela users se ela não existir
ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('admin', 'user') DEFAULT 'user';

-- Atualizar o primeiro usuário para ser admin (opcional)
UPDATE users SET role = 'admin' WHERE id = 1;
