-- =============================================
-- MIGRACIÓN A ESTRUCTURA UNIFICADA DE PERSONAS
-- =============================================
-- Este script migra de la estructura actual (clientes + acompanantes)
-- a una estructura unificada (personas + reserva_huespedes)

-- =============================================
-- PASO 1: Crear nueva tabla unificada de personas
-- =============================================
CREATE TABLE IF NOT EXISTS personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    tipo_documento VARCHAR(20) NOT NULL,
    numero_documento VARCHAR(50) NOT NULL,
    fecha_nacimiento DATE,
    email VARCHAR(150),
    telefono VARCHAR(20),
    direccion TEXT,
    ciudad VARCHAR(100),
    pais VARCHAR(100) DEFAULT 'Colombia',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
    tipo_persona ENUM('cliente_frecuente', 'ocasional') DEFAULT 'ocasional',
    preferencias JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    -- Índices
    INDEX idx_documento (numero_documento),
    INDEX idx_nombre (nombre, apellido),
    INDEX idx_email (email),
    INDEX idx_tipo_persona (tipo_persona),
    INDEX idx_deleted_at (deleted_at),
    
    -- Constraint único para documento
    UNIQUE KEY unique_documento (numero_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PASO 2: Crear tabla de relación reserva-persona
-- =============================================
CREATE TABLE IF NOT EXISTS reserva_huespedes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    persona_id INT NOT NULL,
    rol_en_reserva ENUM('principal', 'acompanante') NOT NULL,
    parentesco VARCHAR(50) COMMENT 'Solo para acompañantes',
    es_menor BOOLEAN DEFAULT FALSE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    
    -- Foreign Keys
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE,
    
    -- Constraints
    UNIQUE KEY unique_reserva_persona (reserva_id, persona_id),
    
    -- Índices
    INDEX idx_reserva (reserva_id),
    INDEX idx_persona (persona_id),
    INDEX idx_rol (rol_en_reserva),
    INDEX idx_parentesco (parentesco),
    INDEX idx_menor (es_menor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PASO 3: Migrar datos existentes de clientes a personas
-- =============================================
INSERT INTO personas (
    nombre, apellido, tipo_documento, numero_documento, 
    fecha_nacimiento, email, telefono, direccion, ciudad, pais,
    fecha_registro, tipo_persona, created_at, updated_at
)
SELECT 
    c.nombre,
    c.apellido,
    c.tipo_documento,
    c.documento,
    c.fecha_nacimiento,
    c.email,
    c.telefono,
    c.direccion,
    c.ciudad,
    c.pais,
    c.created_at as fecha_registro,
    CASE 
        WHEN COUNT(r.id) > 1 THEN 'cliente_frecuente'
        ELSE 'ocasional'
    END as tipo_persona,
    c.created_at,
    c.updated_at
FROM clientes c
LEFT JOIN reservas r ON c.id = r.cliente_id
WHERE c.deleted_at IS NULL
GROUP BY c.id
ORDER BY c.created_at;

-- =============================================
-- PASO 4: Crear relaciones reserva-huésped para clientes principales
-- =============================================
INSERT INTO reserva_huespedes (
    reserva_id, persona_id, rol_en_reserva, parentesco, es_menor, created_at, updated_at
)
SELECT 
    r.id as reserva_id,
    p.id as persona_id,
    'principal' as rol_en_reserva,
    NULL as parentesco,
    CASE 
        WHEN p.fecha_nacimiento IS NOT NULL AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) < 18 THEN TRUE
        ELSE FALSE
    END as es_menor,
    r.created_at,
    r.updated_at
FROM reservas r
JOIN clientes c ON r.cliente_id = c.id
JOIN personas p ON c.documento = p.numero_documento AND c.tipo_documento = p.tipo_documento
WHERE r.deleted_at IS NULL;

-- =============================================
-- PASO 5: Migrar acompañantes existentes (si la tabla existe)
-- =============================================
-- Primero, insertar acompañantes como personas si no existen
INSERT IGNORE INTO personas (
    nombre, apellido, tipo_documento, numero_documento,
    fecha_nacimiento, email, telefono, tipo_persona, created_at, updated_at
)
SELECT 
    a.nombre,
    a.apellido,
    a.tipo_documento,
    a.numero_documento,
    a.fecha_nacimiento,
    NULL as email, -- Los acompañantes usualmente no tienen email
    NULL as telefono, -- Los acompañantes usualmente no tienen teléfono propio
    'ocasional' as tipo_persona,
    a.created_at,
    a.updated_at
FROM acompanantes a
WHERE a.numero_documento NOT IN (
    SELECT numero_documento FROM personas
);

-- Luego, crear las relaciones reserva-huésped para acompañantes
INSERT INTO reserva_huespedes (
    reserva_id, persona_id, rol_en_reserva, parentesco, es_menor, created_at, updated_at
)
SELECT 
    a.reserva_id,
    p.id as persona_id,
    'acompanante' as rol_en_reserva,
    a.parentesco,
    a.es_menor,
    a.created_at,
    a.updated_at
FROM acompanantes a
JOIN personas p ON a.numero_documento = p.numero_documento AND a.tipo_documento = p.tipo_documento;

-- =============================================
-- PASO 6: Crear vistas para compatibilidad
-- =============================================

-- Vista para mantener compatibilidad con tabla clientes
CREATE OR REPLACE VIEW v_clientes AS
SELECT DISTINCT
    p.id,
    p.nombre,
    p.apellido,
    p.email,
    p.telefono,
    p.tipo_documento,
    p.numero_documento as documento,
    p.fecha_nacimiento,
    p.ciudad,
    p.pais,
    p.direccion,
    p.created_at,
    p.updated_at,
    p.deleted_at
FROM personas p
WHERE p.id IN (
    SELECT DISTINCT persona_id 
    FROM reserva_huespedes 
    WHERE rol_en_reserva = 'principal'
) AND p.deleted_at IS NULL;

-- Vista para acompañantes
CREATE OR REPLACE VIEW v_acompanantes AS
SELECT 
    p.id,
    rh.reserva_id,
    p.nombre,
    p.apellido,
    p.tipo_documento,
    p.numero_documento,
    p.fecha_nacimiento,
    rh.parentesco,
    rh.es_menor,
    rh.created_at,
    rh.updated_at
FROM personas p
JOIN reserva_huespedes rh ON p.id = rh.persona_id
WHERE rh.rol_en_reserva = 'acompanante';

-- =============================================
-- PASO 7: Crear procedimientos para gestión simplificada
-- =============================================

DELIMITER //

-- Procedimiento para obtener todos los huéspedes de una reserva
CREATE PROCEDURE sp_get_huespedes_reserva(IN p_reserva_id INT)
BEGIN
    SELECT 
        p.id,
        p.nombre,
        p.apellido,
        p.tipo_documento,
        p.numero_documento,
        p.email,
        p.telefono,
        rh.rol_en_reserva,
        rh.parentesco,
        rh.es_menor,
        TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) as edad_actual
    FROM personas p
    JOIN reserva_huespedes rh ON p.id = rh.persona_id
    WHERE rh.reserva_id = p_reserva_id
    ORDER BY 
        CASE WHEN rh.rol_en_reserva = 'principal' THEN 1 ELSE 2 END,
        p.nombre, p.apellido;
END//

-- Procedimiento para buscar persona por documento
CREATE PROCEDURE sp_buscar_persona(IN p_documento VARCHAR(50))
BEGIN
    SELECT 
        p.*,
        CASE 
            WHEN rh.rol_en_reserva = 'principal' THEN COUNT(*) OVER ()
            ELSE 0
        END as total_reservas_principal,
        CASE 
            WHEN rh.rol_en_reserva = 'acompanante' THEN COUNT(*) OVER ()
            ELSE 0
        END as total_veces_acompanante
    FROM personas p
    LEFT JOIN reserva_huespedes rh ON p.id = rh.persona_id
    WHERE p.numero_documento = p_documento AND p.deleted_at IS NULL
    GROUP BY p.id;
END//

DELIMITER ;

-- =============================================
-- PASO 8: Actualizar estadísticas y reportes
-- =============================================

-- Vista mejorada de ocupación real
CREATE OR REPLACE VIEW v_ocupacion_real_mejorada AS
SELECT 
    r.id as reserva_id,
    r.fecha_entrada,
    r.fecha_salida,
    h.numero as habitacion_numero,
    h.tipo as habitacion_tipo,
    h.capacidad as capacidad_maxima,
    COUNT(rh.id) as total_huespedes,
    SUM(CASE WHEN rh.rol_en_reserva = 'principal' THEN 1 ELSE 0 END) as principales,
    SUM(CASE WHEN rh.rol_en_reserva = 'acompanante' THEN 1 ELSE 0 END) as acompanantes,
    SUM(CASE WHEN rh.es_menor = TRUE THEN 1 ELSE 0 END) as menores,
    SUM(CASE WHEN rh.es_menor = FALSE THEN 1 ELSE 0 END) as adultos,
    r.estado as estado_reserva
FROM reservas r
JOIN habitaciones h ON r.habitacion_id = h.id
JOIN reserva_huespedes rh ON r.id = rh.reserva_id
JOIN personas p ON rh.persona_id = p.id
WHERE r.deleted_at IS NULL AND p.deleted_at IS NULL
GROUP BY r.id, h.numero, h.tipo, h.capacidad, r.fecha_entrada, r.fecha_salida, r.estado
ORDER BY r.fecha_entrada DESC;

-- =============================================
-- PASO 9: Resumen de migración
-- =============================================
SELECT 'MIGRACIÓN COMPLETADA' as estado,
       (SELECT COUNT(*) FROM personas) as total_personas,
       (SELECT COUNT(*) FROM reserva_huespedes WHERE rol_en_reserva = 'principal') as clientes_principales,
       (SELECT COUNT(*) FROM reserva_huespedes WHERE rol_en_reserva = 'acompanante') as acompanantes_migrados,
       (SELECT COUNT(*) FROM reservas) as total_reservas,
       (SELECT COUNT(DISTINCT reserva_id) FROM reserva_huespedes) as reservas_con_huespedes;

-- =============================================
-- NOTAS IMPORTANTES
-- =============================================
/*
1. Las tablas originales (clientes, acompanantes) NO se eliminan automáticamente
2. Se crean vistas de compatibilidad para no romper el código existente
3. Los datos migrados mantienen todas las relaciones originales
4. La nueva estructura permite que una persona sea principal en una reserva 
   y acompañante en otra
5. Para activar completamente la nueva estructura:
   - Actualizar modelos PHP para usar las nuevas tablas
   - Actualizar endpoints para usar la nueva lógica
   - Probar exhaustivamente
   - Una vez confirmado, se pueden eliminar las tablas antiguas
*/
