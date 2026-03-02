-- Agregar campo nacionalidad a la tabla usuarios
ALTER TABLE usuarios 
ADD COLUMN nacionalidad VARCHAR(100) DEFAULT 'Colombia' AFTER telefono;
