-- Tabela para registrar extrato de moedas (créditos e débitos)
CREATE TABLE IF NOT EXISTS moedas_extrato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codcadastro INT NOT NULL,
    tipo ENUM('credito','debito') NOT NULL,
    quantidade INT NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    codpedido INT DEFAULT NULL,
    data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
