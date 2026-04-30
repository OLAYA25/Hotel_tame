-- Alter table to make tipo_documento flexible (optional)
-- Ejecutar en la base de datos `hotel_management_system` si prefieres aceptar valores distintos al ENUM.
-- Opciones:
-- A) Cambiar a VARCHAR(50) (más flexible):
--    ALTER TABLE clientes MODIFY tipo_documento VARCHAR(50) DEFAULT 'DNI';
-- B) Agregar un nuevo valor al ENUM (si sólo quieres añadir una opción concreta):
--    ALTER TABLE clientes MODIFY tipo_documento ENUM('DNI','Pasaporte','Cedula','Otro') DEFAULT 'DNI';

-- Comando recomendado (A):
ALTER TABLE clientes MODIFY tipo_documento VARCHAR(50) DEFAULT 'DNI';
