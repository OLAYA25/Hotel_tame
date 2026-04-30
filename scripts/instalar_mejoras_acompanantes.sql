-- =============================================
-- SCRIPT DE INSTALACIÓN: MEJORAS DE ACOMPAÑANTES
-- Basado en análisis de informes reales del hotel
-- =============================================

-- Ejecutar este script en phpMyAdmin para instalar las mejoras

-- 1. Crear tabla de acompañantes
SOURCE crear_tabla_acompanantes.sql;

-- 2. Crear tabla de acompañantes pendientes
SOURCE crear_tabla_acompanantes_pendientes.sql;

-- 3. Agregar campo de acompañantes a la tabla clientes (opcional)
ALTER TABLE clientes 
ADD COLUMN acompanantes_info JSON COMMENT 'Información de acompañantes frecuentes' AFTER direccion;

-- 4. Agregar campo de número de huéspedes a reservas
ALTER TABLE reservas 
ADD COLUMN numero_huespedes INT DEFAULT 1 COMMENT 'Número total de huéspedes incluyendo acompañantes' AFTER noches,
ADD COLUMN numero_menores INT DEFAULT 0 COMMENT 'Número de menores de edad' AFTER numero_huespedes,
ADD COLUMN numero_adultos INT DEFAULT 1 COMMENT 'Número de adultos (18+ años)' AFTER numero_menores;

-- 5. Crear índices para mejor rendimiento
CREATE INDEX idx_reservas_huespedes ON reservas(numero_huespedes, numero_menores, numero_adultos);
CREATE INDEX idx_reservas_ocupacion ON reservas(habitacion_id, fecha_entrada, fecha_salida, numero_huespedes);

-- 6. Crear vista de ocupación real mejorada
CREATE OR REPLACE VIEW v_ocupacion_real_detallada AS
SELECT 
    r.id as reserva_id,
    r.fecha_entrada,
    r.fecha_salida,
    r.numero_huespedes,
    r.numero_menores,
    r.numero_adultos,
    h.numero as habitacion_numero,
    h.tipo as habitacion_tipo,
    h.capacidad as capacidad_maxima,
    h.precio as precio_noche,
    r.total as total_reserva,
    c.nombre as cliente_principal,
    c.apellido as cliente_principal_apellido,
    c.tipo_documento as cliente_tipo_doc,
    c.documento as cliente_documento,
    -- Contar acompañantes reales
    (SELECT COUNT(*) FROM acompanantes a WHERE a.reserva_id = r.id) as acompanantes_registrados,
    -- Calcular ocupación real
    (SELECT COUNT(*) FROM acompanantes a WHERE a.reserva_id = r.id) + 1 as ocupacion_real,
    -- Calcular porcentaje de ocupación
    ROUND(((SELECT COUNT(*) FROM acompanantes a WHERE a.reserva_id = r.id) + 1) * 100.0 / h.capacidad, 2) as porcentaje_ocupacion,
    -- Determinar si hay sobreocupación
    CASE 
        WHEN ((SELECT COUNT(*) FROM acompanantes a WHERE a.reserva_id = r.id) + 1) > h.capacidad THEN 'SOBREOCUPADA'
        WHEN ((SELECT COUNT(*) FROM acompanantes a WHERE a.reserva_id = r.id) + 1) = h.capacidad THEN 'LLENA'
        ELSE 'DISPONIBLE'
    END as estado_ocupacion,
    r.estado as estado_reserva,
    r.created_at as fecha_creacion
FROM reservas r
JOIN habitaciones h ON r.habitacion_id = h.id
JOIN clientes c ON r.cliente_id = c.id
WHERE r.deleted_at IS NULL
ORDER BY r.fecha_entrada DESC, h.numero;

-- 7. Crear procedimiento para actualizar ocupación real
DELIMITER //
CREATE PROCEDURE sp_actualizar_ocupacion_reserva(IN reserva_id_param INT)
BEGIN
    DECLARE total_huespedes INT DEFAULT 1;
    DECLARE total_menores INT DEFAULT 0;
    DECLARE total_adultos INT DEFAULT 1;
    
    -- Contar acompañantes de la reserva
    SELECT 
        COUNT(*) INTO total_acompanantes,
        SUM(CASE WHEN es_menor = TRUE THEN 1 ELSE 0 END) INTO total_menores,
        SUM(CASE WHEN es_menor = FALSE THEN 1 ELSE 0 END) INTO total_adultos
    FROM acompanantes 
    WHERE reserva_id = reserva_id_param;
    
    -- Calcular totales incluyendo el cliente principal
    SET total_huespedes = total_acompanantes + 1;
    SET total_adultos = total_adultos + 1; // +1 por el cliente principal
    
    -- Actualizar la reserva
    UPDATE reservas 
    SET numero_huespedes = total_huespedes,
        numero_menores = total_menores,
        numero_adultos = total_adultos
    WHERE id = reserva_id_param;
    
    SELECT 
        total_huespedes as huespedes_actualizados,
        total_menores as menores_actualizados,
        total_adultos as adultos_actualizados;
END//
DELIMITER ;

-- 8. Crear trigger para actualizar ocupación automáticamente
DELIMITER //
CREATE TRIGGER after_acompanante_insert 
AFTER INSERT ON acompanantes
FOR EACH ROW
BEGIN
    CALL sp_actualizar_ocupacion_reserva(NEW.reserva_id);
END//

CREATE TRIGGER after_acompanante_delete 
AFTER DELETE ON acompanantes
FOR EACH ROW
BEGIN
    CALL sp_actualizar_ocupacion_reserva(OLD.reserva_id);
END//

CREATE TRIGGER after_acompanante_update 
AFTER UPDATE ON acompanantes
FOR EACH ROW
BEGIN
    CALL sp_actualizar_ocupacion_reserva(NEW.reserva_id);
END//
DELIMITER ;

-- 9. Insertar datos de ejemplo (opcional)
INSERT INTO acompanantes_pendientes (cliente_id, nombre, apellido, tipo_documento, numero_documento, fecha_nacimiento, parentesco) VALUES
(1, 'Juan Carlos', 'Pérez', 'DNI', '12345678', '2010-05-15', 'Hijo'),
(1, 'María', 'Pérez', 'DNI', '87654321', '2012-08-20', 'Hija'),
(2, 'Ana', 'García', 'Pasaporte', 'P1234567', '2008-03-10', 'Hija');

-- 10. Crear reporte de ocupación real
CREATE OR REPLACE VIEW v_reporte_ocupacion_mensual AS
SELECT 
    DATE_FORMAT(fecha_entrada, '%Y-%m') as mes,
    DATE_FORMAT(fecha_entrada, '%Y') as anio,
    DATE_FORMAT(fecha_entrada, '%M') as nombre_mes,
    COUNT(*) as total_reservas,
    SUM(numero_huespedes) as total_huespedes,
    SUM(numero_menores) as total_menores,
    SUM(numero_adultos) as total_adultos,
    AVG(numero_huespedes) as promedio_huespedes,
    MAX(numero_huespedes) as maximo_huespedes,
    SUM(total) as ingresos_totales,
    AVG(total) as promedio_ingreso,
    -- Distribución porcentual
    ROUND(SUM(numero_menores) * 100.0 / SUM(numero_huespedes), 2) as porcentaje_menores,
    ROUND(SUM(numero_adultos) * 100.0 / SUM(numero_huespedes), 2) as porcentaje_adultos
FROM v_ocupacion_real_detallada
WHERE estado_reserva = 'confirmada'
GROUP BY DATE_FORMAT(fecha_entrada, '%Y-%m'), DATE_FORMAT(fecha_entrada, '%Y'), DATE_FORMAT(fecha_entrada, '%M')
ORDER BY anio DESC, mes DESC;

-- Mensaje de finalización
SELECT 'Mejoras de acompañantes instaladas exitosamente' as mensaje,
       NOW() as fecha_instalacion,
       'Sistema listo para registrar acompañantes en reservas' as estado;
