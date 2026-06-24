-- Adiciona coluna custo_moedas na tabela grupos para configurar quantas moedas debitar por categoria
-- Valor padrão = 1 moeda
ALTER TABLE grupos ADD COLUMN IF NOT EXISTS custo_moedas INT NOT NULL DEFAULT 1;
