-- Adiciona coluna 'visto' na tabela disparo_pedidos para controle de notificações
-- 0 = não visto (nova notificação), 1 = já visto pelo prestador
ALTER TABLE disparo_pedidos ADD COLUMN visto TINYINT(1) DEFAULT 0;

-- Marca todos os registros existentes como já vistos (para não quebrar badges existentes)
UPDATE disparo_pedidos SET visto = 1 WHERE visto = 0;
