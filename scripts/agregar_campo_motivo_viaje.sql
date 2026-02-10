-- Agregar campo motivo de viaje a la tabla clientes
ALTER TABLE clientes 
ADD COLUMN motivo_viaje VARCHAR(100) DEFAULT 'turismo' COMMENT 'Motivo principal del viaje' AFTER pais;

-- Actualizar el modelo de Cliente para incluir el nuevo campo
-- Esto se reflejará en el modelo Cliente.php automáticamente
