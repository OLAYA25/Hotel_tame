-- Script para agregar soporte de ventas directas a pedidos_productos (versión corregida)
-- Fecha: 2025-03-25

-- Verificar si la columna tipo_pedido ya existe antes de agregarla
SET @exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'hotel_management_system'
    AND TABLE_NAME = 'pedidos_productos'
    AND COLUMN_NAME = 'tipo_pedido'
);

SET @sql = IF(@exists = 0,
    'ALTER TABLE `pedidos_productos` ADD COLUMN `tipo_pedido` ENUM(''habitacion'', ''directa') NOT NULL DEFAULT ''habitacion'' AFTER `estado`',
    'SELECT ''Column `tipo_pedido` already exists''');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar si la columna referencia_venta ya existe antes de agregarla
SET @exists_ref = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'hotel_management_system'
    AND TABLE_NAME = 'pedidos_productos'
    AND COLUMN_NAME = 'referencia_venta'
);

SET @sql_ref = IF(@exists_ref = 0,
    'ALTER TABLE `pedidos_productos` ADD COLUMN `referencia_venta` VARCHAR(50) NULL AFTER `tipo_pedido`',
    'SELECT ''Column `referencia_venta` already exists''');

PREPARE stmt_ref FROM @sql_ref;
EXECUTE stmt_ref;
DEALLOCATE PREPARE stmt_ref;

-- Hacer habitacion_id nullable para permitir ventas directas
ALTER TABLE `pedidos_productos` 
MODIFY COLUMN `habitacion_id` INT(11) NULL;

-- Actualizar registros existentes para que tengan tipo_pedido = 'habitacion'
UPDATE `pedidos_productos` 
SET `tipo_pedido` = 'habitacion' 
WHERE `tipo_pedido` IS NULL OR `tipo_pedido` = '';

-- Crear índice para mejor rendimiento en consultas por tipo
SET @index_exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = 'hotel_management_system'
    AND TABLE_NAME = 'pedidos_productos'
    AND INDEX_NAME = 'idx_tipo_pedido'
);

SET @sql_index = IF(@index_exists = 0,
    'CREATE INDEX `idx_tipo_pedido` ON `pedidos_productos` (`tipo_pedido`)',
    'SELECT ''Index `idx_tipo_pedido` already exists''');

PREPARE stmt_index FROM @sql_index;
EXECUTE stmt_index;
DEALLOCATE PREPARE stmt_index;

SET @index_ref_exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = 'hotel_management_system'
    AND TABLE_NAME = 'pedidos_productos'
    AND INDEX_NAME = 'idx_referencia_venta'
);

SET @sql_index_ref = IF(@index_ref_exists = 0,
    'CREATE INDEX `idx_referencia_venta` ON `pedidos_productos` (`referencia_venta`)',
    'SELECT ''Index `idx_referencia_venta` already exists''');

PREPARE stmt_index_ref FROM @sql_index_ref;
EXECUTE stmt_index_ref;
DEALLOCATE PREPARE stmt_index_ref;

-- Comentario sobre los nuevos campos:
-- tipo_pedido: 'habitacion' para pedidos de huéspedes, 'directa' para ventas directas
-- referencia_venta: folio/ticket para ventas directas (ej: VTA-001, VTA-002)
-- habitacion_id: ahora nullable, NULL para ventas directas
