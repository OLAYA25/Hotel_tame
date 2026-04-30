-- Migración de usuarios existentes al nuevo sistema de roles
-- =============================================

-- Actualizar tabla de usuarios para que sea compatible con el nuevo sistema
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS rol_temp VARCHAR(50) AFTER rol;

-- Copiar valores actuales a la columna temporal
UPDATE usuarios SET rol_temp = rol WHERE rol_temp IS NULL;

-- Eliminar la columna ENUM antigua
ALTER TABLE usuarios DROP COLUMN rol;

-- Agregar nueva columna (sin restricción ENUM)
ALTER TABLE usuarios ADD COLUMN rol VARCHAR(50) AFTER email;

-- Restaurar valores desde la columna temporal
UPDATE usuarios SET rol = rol_temp WHERE rol IS NULL;

-- Eliminar columna temporal
ALTER TABLE usuarios DROP COLUMN rol_temp;

-- Insertar usuarios existentes en el sistema de roles múltiples
INSERT IGNORE INTO usuarios_roles (usuario_id, rol_id)
SELECT u.id, r.id 
FROM usuarios u 
JOIN roles r ON LOWER(u.rol) = LOWER(r.nombre)
WHERE u.activo = 1;

-- Actualizar roles existentes para que coincidan con los nuevos roles
UPDATE usuarios SET rol = 'Administrador' WHERE rol = 'admin';
UPDATE usuarios SET rol = 'Gerente' WHERE rol = 'gerente';
UPDATE usuarios SET rol = 'Recepcionista' WHERE rol = 'recepcionista';
UPDATE usuarios SET rol = 'Limpieza' WHERE rol = 'limpieza';
