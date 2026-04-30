-- Script para agregar soporte de ventas directas a pedidos_productos
-- Fecha: 2025-03-25

-- Agregar campo tipo_pedido para diferenciar entre pedidos de habitación y ventas directas
ALTER TABLE `pedidos_productos` 
ADD COLUMN `tipo_pedido` ENUM('habitacion', 'directa') NOT NULL DEFAULT 'habitacion' AFTER `estado`;

-- Hacer habitacion_id nullable para permitir ventas directas
ALTER TABLE `pedidos_productos` 
MODIFY COLUMN `habitacion_id` INT(11) NULL;

-- Agregar campo referencia_venta para identificación interna de ventas directas
ALTER TABLE `pedidos_productos` 
ADD COLUMN `referencia_venta` VARCHAR(50) NULL AFTER `tipo_pedido`;

-- Actualizar registros existentes para que tengan tipo_pedido = 'habitacion'
UPDATE `pedidos_productos` 
SET `tipo_pedido` = 'habitacion' 
WHERE `tipo_pedido` IS NULL OR `tipo_pedido` = '';

-- Crear índice para mejor rendimiento en consultas por tipo
CREATE INDEX `idx_tipo_pedido` ON `pedidos_productos` (`tipo_pedido`);
CREATE INDEX `idx_referencia_venta` ON `pedidos_productos` (`referencia_venta`);

-- Comentario sobre los nuevos campos:
-- tipo_pedido: 'habitacion' para pedidos de huéspedes, 'directa' para ventas directas
-- referencia_venta: folio/ticket para ventas directas (ej: VTA-001, VTA-002)
-- habitacion_id: ahora nullable, NULL para ventas directas
