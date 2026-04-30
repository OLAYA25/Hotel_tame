-- =============================================
-- TABLA: acompanantes
-- Almacena información de acompañantes en reservas
-- =============================================
CREATE TABLE acompanantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    tipo_documento VARCHAR(20) NOT NULL,
    numero_documento VARCHAR(50) NOT NULL,
    fecha_nacimiento DATE,
    parentesco VARCHAR(50) COMMENT 'Relación con el huésped principal',
    edad INT COMMENT 'Edad calculada automáticamente',
    es_menor BOOLEAN DEFAULT FALSE COMMENT 'TRUE si es menor de edad',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    INDEX idx_reserva (reserva_id),
    INDEX idx_documento (numero_documento),
    INDEX idx_nombre (nombre, apellido),
    INDEX idx_menores (es_menor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TRIGGER: Calcular edad y determinar si es menor
-- =============================================
DELIMITER //
CREATE TRIGGER before_acompanante_insert 
BEFORE INSERT ON acompanantes
FOR EACH ROW
BEGIN
    IF NEW.fecha_nacimiento IS NOT NULL THEN
        SET NEW.edad = TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE());
        SET NEW.es_menor = (NEW.edad < 18);
    END IF;
END//
DELIMITER ;

DELIMITER //
CREATE TRIGGER before_acompanante_update 
BEFORE UPDATE ON acompanantes
FOR EACH ROW
BEGIN
    IF NEW.fecha_nacimiento IS NOT NULL THEN
        SET NEW.edad = TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE());
        SET NEW.es_menor = (NEW.edad < 18);
    END IF;
END//
DELIMITER ;

-- =============================================
-- VISTA: Resumen de ocupación real
-- =============================================
CREATE VIEW v_resumen_ocupacion AS
SELECT 
    r.id as reserva_id,
    r.fecha_entrada,
    r.fecha_salida,
    h.numero as habitacion_numero,
    h.tipo as habitacion_tipo,
    h.capacidad as capacidad_maxima,
    c.nombre as cliente_principal,
    c.apellido as cliente_principal_apellido,
    COUNT(a.id) as numero_acompanantes,
    COUNT(a.id) + 1 as total_huespedes,
    SUM(CASE WHEN a.es_menor = TRUE THEN 1 ELSE 0 END) as menores,
    SUM(CASE WHEN a.es_menor = FALSE THEN 1 ELSE 0 END) as adultos,
    r.estado as estado_reserva
FROM reservas r
JOIN habitaciones h ON r.habitacion_id = h.id
JOIN clientes c ON r.cliente_id = c.id
LEFT JOIN acompanantes a ON r.id = a.reserva_id
GROUP BY r.id, h.numero, h.tipo, h.capacidad, c.nombre, c.apellido, r.fecha_entrada, r.fecha_salida, r.estado
ORDER BY r.fecha_entrada DESC;

-- =============================================
-- PROCEDIMIENTO: Obtener ocupación real
-- =============================================
DELIMITER //
CREATE PROCEDURE sp_ocupacion_real(IN fecha_inicio DATE, IN fecha_fin DATE)
BEGIN
    SELECT 
        h.numero,
        h.tipo,
        h.capacidad,
        COUNT(DISTINCT r.id) as total_reservas,
        SUM(COUNT(a.id) + 1) as total_huespedes,
        SUM(CASE WHEN a.es_menor = TRUE THEN 1 ELSE 0 END) as total_menores,
        SUM(CASE WHEN a.es_menor = FALSE THEN 1 ELSE 0 END) as total_adultos,
        ROUND(AVG(COUNT(a.id) + 1), 2) as promedio_huespedes,
        MAX(COUNT(a.id) + 1) as max_huespedes
    FROM habitaciones h
    LEFT JOIN reservas r ON h.id = r.habitacion_id 
        AND r.fecha_entrada BETWEEN fecha_inicio AND fecha_fin
        AND r.estado = 'confirmada'
    LEFT JOIN acompanantes a ON r.id = a.reserva_id
    GROUP BY h.id, h.numero, h.tipo, h.capacidad
    ORDER BY h.numero;
END//
DELIMITER ;
