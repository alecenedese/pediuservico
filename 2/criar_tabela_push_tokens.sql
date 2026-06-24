-- Tabela para armazenar tokens de push notification do Expo
CREATE TABLE IF NOT EXISTS push_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    tipo ENUM('prestador', 'cliente') NOT NULL,
    id_usuario VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_token (token, tipo, id_usuario),
    INDEX idx_tipo_usuario (tipo, id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;