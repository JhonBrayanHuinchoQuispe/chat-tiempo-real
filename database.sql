
-- ==========================================
-- CHAT EN TIEMPO REAL - BASE DE DATOS
-- ==========================================
-- Proyecto: Sistema de Chat con MySQL
-- Fecha: 2025-01-18
-- ==========================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS chat_tiempo_real;
USE chat_tiempo_real;

-- Tabla principal para mensajes del chat
CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT
);

-- Índices para mejor rendimiento
CREATE INDEX idx_timestamp ON mensajes (timestamp);
CREATE INDEX idx_usuario ON mensajes (usuario);

-- Vista para consultar mensajes recientes
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

-- Base de datos lista para el chat