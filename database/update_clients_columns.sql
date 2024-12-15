ALTER TABLE clients
CHANGE COLUMN name responsavel VARCHAR(255) NOT NULL,
CHANGE COLUMN phone telefone VARCHAR(20),
CHANGE COLUMN address endereco TEXT;
