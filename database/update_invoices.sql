-- Adicionar coluna valor_total à tabela invoices
ALTER TABLE invoices ADD COLUMN valor_total DECIMAL(10,2) AS (valor_original - desconto) STORED;

-- Criar view para calcular o total geral das faturas
CREATE OR REPLACE VIEW view_total_faturas AS
SELECT 
    COUNT(*) as total_faturas,
    SUM(valor_original) as total_valor_original,
    SUM(desconto) as total_descontos,
    SUM(valor_total) as total_geral,
    SUM(CASE WHEN status = 'pago' THEN valor_total ELSE 0 END) as total_pago,
    SUM(CASE WHEN status = 'pendente' THEN valor_total ELSE 0 END) as total_pendente
FROM invoices;

-- Criar índice para melhor performance nas consultas de valor
ALTER TABLE invoices ADD INDEX idx_invoices_valor_total (valor_total);
