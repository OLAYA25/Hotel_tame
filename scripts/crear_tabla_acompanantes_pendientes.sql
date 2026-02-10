-- =============================================
-- TABLA: acompanantes_pendientes
-- Almacena acompañantes registrados con clientes pero sin reserva asignada aún
-- =============================================
CREATE TABLE acompanantes_pendientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    tipo_documento VARCHAR(20) NOT NULL,
    numero_documento VARCHAR(50) NOT NULL,
    fecha_nacimiento DATE,
    parentesco VARCHAR(50) DEFAULT 'Otro',
    edad INT COMMENT 'Edad calculada automáticamente',
    es_menor BOOLEAN DEFAULT FALSE COMMENT 'TRUE si es menor de edad',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_documento (numero_documento),
    INDEX idx_nombre (nombre, apellido),
    INDEX idx_menores (es_menor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TRIGGER: Calcular edad y determinar si es menor en pendientes
-- =============================================
DELIMITER //
CREATE TRIGGER before_acompanante_pendiente_insert 
BEFORE INSERT ON acompanantes_pendientes
FOR EACH ROW
BEGIN
    IF NEW.fecha_nacimiento IS NOT NULL THEN
        SET NEW.edad = TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE());
        SET NEW.es_menor = (NEW.edad < 18);
    END IF;
END//
DELIMITER ;

DELIMITER //
CREATE TRIGGER before_acompanante_pendiente_update 
BEFORE UPDATE ON acompanantes_pendientes
FOR EACH ROW
BEGIN
    IF NEW.fecha_nacimiento IS NOT NULL THEN
        SET NEW.edad = TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE());
        SET NEW.es_menor = (NEW.edad < 18);
    END IF;
END//
DELIMITER ;

-- =============================================
-- VISTA: Acompañantes por cliente
-- =============================================
CREATE VIEW v_acompanantes_cliente AS
SELECT 
    c.id as cliente_id,
    c.nombre as cliente_nombre,
    c.apellido as cliente_apellido,
    ap.id as acompanante_id,
    ap.nombre as acompanante_nombre,
    ap.apellido as acompanante_apellido,
    ap.tipo_documento,
    ap.numero_documento,
    ap.fecha_nacimiento,
    ap.edad,
    ap.es_menor,
    ap.parentesco,
    CASE 
        WHEN ap.edad < 18 THEN 'Menor'
        WHEN ap.edad BETWEEN 18 AND 65 THEN 'Adulto'
        ELSE 'Adulto Mayor'
    END as categoria_edad,
    ap.created_at as fecha_registro
FROM clientes c
LEFT JOIN acompanantes_pendientes ap ON c.id = ap.cliente_id
ORDER BY c.id, ap.created_at;
