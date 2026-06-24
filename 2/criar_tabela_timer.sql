-- Tabela para controlar o timer de 10 minutos para o prestador firmar acordo
CREATE TABLE IF NOT EXISTS timer_acordo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codpedido VARCHAR(50) NOT NULL,
    codcadastro VARCHAR(50) NOT NULL,
    tempo_expiracao DATETIME NOT NULL,
    status VARCHAR(20) DEFAULT 'aguardando',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_codpedido (codpedido),
    INDEX idx_codcadastro (codcadastro),
    INDEX idx_status (status)
);
