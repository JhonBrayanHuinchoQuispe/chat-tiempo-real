
CREATE DATABASE IF NOT EXISTS chat_tiempo_real;
USE chat_tiempo_real;

CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT
);

CREATE INDEX idx_timestamp ON mensajes (timestamp);
CREATE INDEX idx_usuario ON mensajes (usuario);

-- Vista par mensajes recientes
CREATE VIEW mensajes_recientes AS
SELECT 
    id,
    usuario,
    mensaje,
    timestamp,
    DATE_FORMAT(timestamp, '%H:%i') as hora
FROM mensajes 
ORDER BY timestamp DESC 
LIMIT 50;

