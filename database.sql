
-- ==========================================
-- CHAT EN TIEMPO REAL - BASE DE DATOS
-- ==========================================
-- Proyecto: Sistema de Chat con PostgreSQL
-- Fecha: 2025-01-18
-- ==========================================

-- Tabla principal para mensajes del chat
CREATE TABLE IF NOT EXISTS mensajes (
    id SERIAL PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT
);

-- Índices para mejor rendimiento
CREATE INDEX IF NOT EXISTS idx_timestamp ON mensajes (timestamp);
CREATE INDEX IF NOT EXISTS idx_usuario ON mensajes (usuario);

-- Vista para consultar mensajes recientes
CREATE OR REPLACE VIEW mensajes_recientes AS
SELECT 
    id,
    usuario,
    mensaje,
    timestamp,
    TO_CHAR(timestamp, 'HH24:MI') as hora
FROM mensajes 
ORDER BY timestamp DESC 
LIMIT 50;

-- Base de datos lista para el chat