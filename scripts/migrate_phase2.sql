-- Phase 2: Data migrations (coherencia de BD con código front-end)
-- Actualizar apellidos de usuarios
UPDATE usuarios SET apellido = 'González' WHERE email = 'admin@hotel.com';
UPDATE usuarios SET apellido = 'Gómez' WHERE email = 'maria@hotel.com';
UPDATE usuarios SET apellido = 'Ruiz' WHERE email = 'carlos@hotel.com';
UPDATE usuarios SET apellido = 'Martínez' WHERE email = 'ana@hotel.com';

-- Actualizar apellidos de clientes
UPDATE clientes SET apellido = 'Tibasosa' WHERE email = 'jhuliet@email.com';
UPDATE clientes SET apellido = 'Pérez' WHERE email = 'juan@email.com';
UPDATE clientes SET apellido = 'López' WHERE email = 'ana.lopez@email.com';
UPDATE clientes SET apellido = 'García' WHERE email = 'pedro@email.com';
UPDATE clientes SET apellido = 'Sánchez' WHERE email = 'laura@email.com';

-- Rellenar imagen_url por defecto para habitaciones existentes
UPDATE habitaciones SET imagen_url = '101.jpg' WHERE numero = '101';
UPDATE habitaciones SET imagen_url = '102.jpg' WHERE numero = '102';
UPDATE habitaciones SET imagen_url = '103.jpg' WHERE numero = '103';
UPDATE habitaciones SET imagen_url = '201.jpg' WHERE numero = '201';
UPDATE habitaciones SET imagen_url = '202.jpg' WHERE numero = '202';
UPDATE habitaciones SET imagen_url = '203.jpg' WHERE numero = '203';
UPDATE habitaciones SET imagen_url = '301.jpg' WHERE numero = '301';
UPDATE habitaciones SET imagen_url = '302.jpg' WHERE numero = '302';
