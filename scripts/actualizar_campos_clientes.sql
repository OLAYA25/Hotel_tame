-- =============================================
-- ACTUALIZACIÓN COMPLETA DE CAMPOS DE CLIENTES
-- Ejecutar este script en phpMyAdmin para agregar los campos faltantes
-- =============================================

-- 1. Agregar campo motivo_viaje si no existe
ALTER TABLE clientes 
ADD COLUMN IF NOT EXISTS motivo_viaje VARCHAR(100) DEFAULT 'turismo' COMMENT 'Motivo principal del viaje' AFTER pais;

-- 2. Agregar campo acompanantes_info si no existe  
ALTER TABLE clientes 
ADD COLUMN IF NOT EXISTS acompanantes_info JSON COMMENT 'Información de acompañantes frecuentes' AFTER direccion;

-- 3. Verificar que los campos se hayan agregado correctamente
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clientes' 
    AND COLUMN_NAME IN ('motivo_viaje', 'acompanantes_info')
ORDER BY COLUMN_NAME;

-- 4. Mostrar estructura actual de la tabla clientes
DESCRIBE clientes;
