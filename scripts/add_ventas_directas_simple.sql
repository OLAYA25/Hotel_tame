-- Script para agregar soporte de ventas directas a pedidos_productos (versión simplificada)
-- Fecha: 2025-03-25

-- Agregar campo tipo_pedido si no existe
ALTER TABLE `pedidos_productos` 
ADD COLUMN `tipo_pedido` ENUM('habitacion', 'directa') NOT NULL DEFAULT 'habitacion' AFTER `estado`;

-- Agregar campo referencia_venta si no existe
ALTER TABLE `pedidos_productos` 
ADD COLUMN `referencia_venta` VARCHAR(50) NULL AFTER `tipo_pedido`;

-- Hacer habitacion_id nullable para permitir ventas directas
ALTER TABLE `pedidos_productos` 
MODIFY COLUMN `habitacion_id` INT(11) NULL;

-- Actualizar registros existentes para que tengan tipo_pedido = 'habitacion'
UPDATE `pedidos_productos` 
SET `tipo_pedido` = 'habitacion' 
WHERE `tipo_pedido` IS NULL OR `tipo_pedido` = '';

-- Crear índices para mejor rendimiento
CREATE INDEX `idx_tipo_pedido` ON `pedidos_productos` (`tipo_pedido`);
CREATE INDEX `idx_referencia_venta` ON `pedidos_productos` (`referencia_venta`);
