-- Agregar campo motivo_viaje a la tabla reservas
ALTER TABLE reservas 
ADD COLUMN motivo_viaje VARCHAR(50) DEFAULT 'turismo' AFTER metodo_pago;
