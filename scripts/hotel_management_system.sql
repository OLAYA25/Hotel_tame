-- =============================================
-- Script de Base de Datos: Hotel Management System
-- Sistema de Gestión Hotelera
-- Versión: 1.0
-- Motor: MySQL 5.7+ / MariaDB 10.3+
-- =============================================

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS hotel_management_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE hotel_management_system;

-- =============================================
-- TABLA: usuarios
-- Almacena los usuarios del sistema (staff, administradores)
-- =============================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'staff', 'viewer') NOT NULL DEFAULT 'staff',
    telefono VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_rol (rol),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: clientes
-- Almacena información de los clientes del hotel
-- =============================================
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    documento VARCHAR(50) NOT NULL UNIQUE,
    direccion TEXT,
    ciudad VARCHAR(100),
    pais VARCHAR(100) DEFAULT 'Colombia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_documento (documento),
    INDEX idx_email (email),
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: habitaciones
-- Almacena información de las habitaciones del hotel
-- =============================================
CREATE TABLE habitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(10) NOT NULL UNIQUE,
    tipo ENUM('simple', 'doble', 'suite', 'presidencial') NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    estado ENUM('disponible', 'ocupada', 'mantenimiento', 'reservada') NOT NULL DEFAULT 'disponible',
    piso INT NOT NULL,
    capacidad INT NOT NULL,
    descripcion TEXT,
    imagen_url VARCHAR(255),
    amenidades JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_numero (numero),
    INDEX idx_estado (estado),
    INDEX idx_tipo (tipo),
    INDEX idx_piso (piso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: reservas
-- Almacena las reservas realizadas por los clientes
-- =============================================
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    habitacion_id INT NOT NULL,
    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,
    estado ENUM('confirmada', 'pendiente', 'cancelada', 'completada') NOT NULL DEFAULT 'pendiente',
    total DECIMAL(10, 2) NOT NULL,
    metodo_pago VARCHAR(50),
    noches INT NOT NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE RESTRICT,
    INDEX idx_cliente (cliente_id),
    INDEX idx_habitacion (habitacion_id),
    INDEX idx_estado (estado),
    INDEX idx_fechas (fecha_entrada, fecha_salida),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: pagos
-- Registro de pagos asociados a reservas
-- =============================================
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta_credito', 'tarjeta_debito', 'transferencia', 'paypal') NOT NULL,
    estado ENUM('pendiente', 'completado', 'rechazado', 'reembolsado') NOT NULL DEFAULT 'pendiente',
    referencia VARCHAR(100),
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    INDEX idx_reserva (reserva_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: servicios_adicionales
-- Catálogo de servicios adicionales del hotel
-- =============================================
CREATE TABLE servicios_adicionales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: reserva_servicios
-- Relación entre reservas y servicios adicionales
-- =============================================
CREATE TABLE reserva_servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    servicio_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios_adicionales(id) ON DELETE RESTRICT,
    INDEX idx_reserva (reserva_id),
    INDEX idx_servicio (servicio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: mantenimiento
-- Registro de mantenimiento de habitaciones
-- =============================================
CREATE TABLE mantenimiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habitacion_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo ENUM('preventivo', 'correctivo', 'limpieza') NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('pendiente', 'en_proceso', 'completado') NOT NULL DEFAULT 'pendiente',
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_fin TIMESTAMP NULL,
    costo DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_habitacion (habitacion_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: auditoria
-- Registro de auditoría del sistema
-- =============================================
CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(50) NOT NULL,
    tabla VARCHAR(50) NOT NULL,
    registro_id INT,
    datos_anteriores JSON,
    datos_nuevos JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_tabla (tabla),
    INDEX idx_accion (accion),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DATOS INICIALES
-- =============================================

-- Insertar usuarios (password: hotel123 - en producción usar hash bcrypt)
INSERT INTO usuarios (nombre, email, password, rol, telefono, activo) VALUES
('Admin Principal', 'admin@hotel.com', '$2b$10$YourHashHere', 'admin', '+57 300 1234567', TRUE),
('María González', 'maria@hotel.com', '$2b$10$YourHashHere', 'staff', '+57 300 1234568', TRUE),
('Carlos Ruiz', 'carlos@hotel.com', '$2b$10$YourHashHere', 'staff', '+57 300 1234569', TRUE),
('Ana Martínez', 'ana@hotel.com', '$2b$10$YourHashHere', 'viewer', '+57 300 1234570', TRUE);

-- Insertar clientes
INSERT INTO clientes (nombre, email, telefono, documento, direccion, ciudad, pais) VALUES
('Jhuliet Tibasosa', 'jhuliet@email.com', '+57 301 1111111', '1234567890', 'Calle Principal 123', 'Bogotá', 'Colombia'),
('Juan Pérez', 'juan@email.com', '+57 302 2222222', '0987654321', 'Avenida Central 456', 'Medellín', 'Colombia'),
('Ana López', 'ana.lopez@email.com', '+57 303 3333333', '1122334455', 'Plaza Mayor 789', 'Cali', 'Colombia'),
('Pedro García', 'pedro@email.com', '+57 304 4444444', '5544332211', 'Carrera 10 #20-30', 'Cartagena', 'Colombia'),
('Laura Sánchez', 'laura@email.com', '+57 305 5555555', '9988776655', 'Calle 50 #15-20', 'Barranquilla', 'Colombia');

-- Insertar habitaciones
INSERT INTO habitaciones (numero, tipo, precio, estado, piso, capacidad, descripcion, amenidades) VALUES
('101', 'suite', 450000.00, 'ocupada', 1, 4, 'Suite de lujo con vista al mar, jacuzzi privado y sala de estar', '["wifi", "tv", "minibar", "jacuzzi", "aire_acondicionado"]'),
('102', 'simple', 150000.00, 'ocupada', 1, 2, 'Habitación simple confortable con cama queen', '["wifi", "tv", "aire_acondicionado"]'),
('103', 'doble', 200000.00, 'disponible', 1, 3, 'Habitación doble con dos camas dobles', '["wifi", "tv", "minibar", "aire_acondicionado"]'),
('201', 'doble', 220000.00, 'reservada', 2, 3, 'Habitación doble con balcón y vista a la ciudad', '["wifi", "tv", "minibar", "balcon", "aire_acondicionado"]'),
('202', 'simple', 130000.00, 'disponible', 2, 2, 'Habitación simple económica', '["wifi", "tv", "ventilador"]'),
('203', 'doble', 200000.00, 'disponible', 2, 3, 'Habitación doble estándar', '["wifi", "tv", "aire_acondicionado"]'),
('301', 'presidencial', 800000.00, 'disponible', 3, 6, 'Suite presidencial de lujo con sala de estar, comedor y terraza privada', '["wifi", "tv", "minibar", "jacuzzi", "terraza", "aire_acondicionado", "servicio_habitacion"]'),
('302', 'suite', 500000.00, 'mantenimiento', 3, 4, 'Suite ejecutiva con oficina integrada', '["wifi", "tv", "minibar", "escritorio", "aire_acondicionado"]');

-- Insertar reservas
INSERT INTO reservas (cliente_id, habitacion_id, fecha_entrada, fecha_salida, estado, total, metodo_pago, noches, observaciones) VALUES
(1, 1, '2024-12-01', '2024-12-05', 'confirmada', 1800000.00, 'tarjeta_credito', 4, 'Cliente VIP - Solicita late checkout'),
(2, 2, '2024-12-10', '2024-12-12', 'confirmada', 300000.00, 'efectivo', 2, 'Reserva para viaje de negocios'),
(3, 4, '2024-12-15', '2024-12-18', 'pendiente', 660000.00, 'transferencia', 3, 'Luna de miel - Decoración especial'),
(4, 1, '2024-12-20', '2024-12-23', 'confirmada', 1350000.00, 'tarjeta_debito', 3, NULL),
(5, 7, '2024-12-25', '2024-12-30', 'confirmada', 4000000.00, 'tarjeta_credito', 5, 'Celebración familiar - Grupo de 6 personas');

-- Insertar servicios adicionales
INSERT INTO servicios_adicionales (nombre, descripcion, precio, activo) VALUES
('Desayuno Buffet', 'Desayuno continental con variedad de opciones', 35000.00, TRUE),
('Spa y Masajes', 'Sesión de spa de 60 minutos', 120000.00, TRUE),
('Transporte Aeropuerto', 'Servicio de transporte desde/hacia el aeropuerto', 80000.00, TRUE),
('Lavandería Express', 'Servicio de lavandería en 24 horas', 45000.00, TRUE),
('Cena Romántica', 'Cena especial para dos personas', 180000.00, TRUE),
('Tour Ciudad', 'Tour guiado por la ciudad (4 horas)', 150000.00, TRUE);

-- Insertar relación reserva-servicios
INSERT INTO reserva_servicios (reserva_id, servicio_id, cantidad, precio_unitario, subtotal) VALUES
(1, 1, 4, 35000.00, 140000.00),
(1, 2, 1, 120000.00, 120000.00),
(3, 5, 1, 180000.00, 180000.00),
(5, 1, 5, 35000.00, 175000.00),
(5, 6, 1, 150000.00, 150000.00);

-- Insertar pagos
INSERT INTO pagos (reserva_id, monto, metodo_pago, estado, referencia) VALUES
(1, 1800000.00, 'tarjeta_credito', 'completado', 'REF-2024-001'),
(2, 300000.00, 'efectivo', 'completado', 'REF-2024-002'),
(4, 1350000.00, 'tarjeta_debito', 'completado', 'REF-2024-003'),
(5, 2000000.00, 'tarjeta_credito', 'completado', 'REF-2024-004');

-- Insertar registros de mantenimiento
INSERT INTO mantenimiento (habitacion_id, usuario_id, tipo, descripcion, estado, costo) VALUES
(8, 2, 'correctivo', 'Reparación de aire acondicionado', 'en_proceso', 250000.00),
(3, 3, 'limpieza', 'Limpieza profunda post-checkout', 'completado', 50000.00);

-- =============================================
-- VISTAS ÚTILES
-- =============================================

-- Vista: Resumen de reservas con información completa
CREATE VIEW vista_reservas_completa AS
SELECT 
    r.id,
    r.fecha_entrada,
    r.fecha_salida,
    r.estado,
    r.total,
    r.noches,
    c.nombre AS cliente_nombre,
    c.email AS cliente_email,
    c.telefono AS cliente_telefono,
    h.numero AS habitacion_numero,
    h.tipo AS habitacion_tipo,
    h.precio AS habitacion_precio,
    r.created_at
FROM reservas r
JOIN clientes c ON r.cliente_id = c.id
JOIN habitaciones h ON r.habitacion_id = h.id;

-- Vista: Estadísticas del dashboard
CREATE VIEW vista_estadisticas_dashboard AS
SELECT
    (SELECT COUNT(*) FROM habitaciones) AS total_habitaciones,
    (SELECT COUNT(*) FROM habitaciones WHERE estado = 'disponible') AS habitaciones_disponibles,
    (SELECT COUNT(*) FROM habitaciones WHERE estado = 'ocupada') AS habitaciones_ocupadas,
    (SELECT COUNT(*) FROM reservas WHERE estado IN ('confirmada', 'pendiente')) AS reservas_activas,
    (SELECT COUNT(*) FROM clientes) AS total_clientes,
    (SELECT COALESCE(SUM(total), 0) FROM reservas WHERE estado = 'confirmada' AND MONTH(created_at) = MONTH(CURRENT_DATE)) AS ingresos_mes_actual;

-- Vista: Habitaciones disponibles con detalles
CREATE VIEW vista_habitaciones_disponibles AS
SELECT 
    id,
    numero,
    tipo,
    precio,
    piso,
    capacidad,
    descripcion,
    amenidades
FROM habitaciones
WHERE estado = 'disponible'
ORDER BY piso, numero;

-- =============================================
-- PROCEDIMIENTOS ALMACENADOS
-- =============================================

DELIMITER //

-- Procedimiento: Crear nueva reserva y actualizar estado de habitación
CREATE PROCEDURE sp_crear_reserva(
    IN p_cliente_id INT,
    IN p_habitacion_id INT,
    IN p_fecha_entrada DATE,
    IN p_fecha_salida DATE,
    IN p_metodo_pago VARCHAR(50),
    OUT p_reserva_id INT
)
BEGIN
    DECLARE v_precio DECIMAL(10,2);
    DECLARE v_noches INT;
    DECLARE v_total DECIMAL(10,2);
    
    -- Calcular noches y total
    SET v_noches = DATEDIFF(p_fecha_salida, p_fecha_entrada);
    SELECT precio INTO v_precio FROM habitaciones WHERE id = p_habitacion_id;
    SET v_total = v_precio * v_noches;
    
    -- Crear reserva
    INSERT INTO reservas (cliente_id, habitacion_id, fecha_entrada, fecha_salida, total, metodo_pago, noches, estado)
    VALUES (p_cliente_id, p_habitacion_id, p_fecha_entrada, p_fecha_salida, v_total, p_metodo_pago, v_noches, 'confirmada');
    
    SET p_reserva_id = LAST_INSERT_ID();
    
    -- Actualizar estado de habitación
    UPDATE habitaciones SET estado = 'reservada' WHERE id = p_habitacion_id;
END //

-- Procedimiento: Cancelar reserva
CREATE PROCEDURE sp_cancelar_reserva(IN p_reserva_id INT)
BEGIN
    DECLARE v_habitacion_id INT;
    
    -- Obtener ID de habitación
    SELECT habitacion_id INTO v_habitacion_id FROM reservas WHERE id = p_reserva_id;
    
    -- Actualizar estado de reserva
    UPDATE reservas SET estado = 'cancelada' WHERE id = p_reserva_id;
    
    -- Liberar habitación
    UPDATE habitaciones SET estado = 'disponible' WHERE id = v_habitacion_id;
END //

-- Procedimiento: Check-in
CREATE PROCEDURE sp_check_in(IN p_reserva_id INT)
BEGIN
    DECLARE v_habitacion_id INT;
    
    SELECT habitacion_id INTO v_habitacion_id FROM reservas WHERE id = p_reserva_id;
    
    UPDATE reservas SET estado = 'confirmada' WHERE id = p_reserva_id;
    UPDATE habitaciones SET estado = 'ocupada' WHERE id = v_habitacion_id;
END //

-- Procedimiento: Check-out
CREATE PROCEDURE sp_check_out(IN p_reserva_id INT)
BEGIN
    DECLARE v_habitacion_id INT;
    
    SELECT habitacion_id INTO v_habitacion_id FROM reservas WHERE id = p_reserva_id;
    
    UPDATE reservas SET estado = 'completada' WHERE id = p_reserva_id;
    UPDATE habitaciones SET estado = 'disponible' WHERE id = v_habitacion_id;
END //

DELIMITER ;

-- =============================================
-- TRIGGERS
-- =============================================

DELIMITER //

-- Trigger: Auditar cambios en reservas
CREATE TRIGGER trg_auditoria_reservas_update
AFTER UPDATE ON reservas
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (usuario_id, accion, tabla, registro_id, datos_anteriores, datos_nuevos)
    VALUES (
        NULL,
        'UPDATE',
        'reservas',
        NEW.id,
        JSON_OBJECT('estado', OLD.estado, 'total', OLD.total),
        JSON_OBJECT('estado', NEW.estado, 'total', NEW.total)
    );
END //

-- Trigger: Auditar eliminación de reservas
CREATE TRIGGER trg_auditoria_reservas_delete
BEFORE DELETE ON reservas
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (usuario_id, accion, tabla, registro_id, datos_anteriores)
    VALUES (
        NULL,
        'DELETE',
        'reservas',
        OLD.id,
        JSON_OBJECT('cliente_id', OLD.cliente_id, 'habitacion_id', OLD.habitacion_id, 'total', OLD.total)
    );
END //

DELIMITER ;

-- =============================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- =============================================

-- Índice compuesto para búsquedas de disponibilidad
CREATE INDEX idx_habitaciones_disponibilidad ON habitaciones(estado, tipo, precio);

-- Índice para reportes de ingresos
CREATE INDEX idx_reservas_ingresos ON reservas(estado, created_at, total);

-- =============================================
-- PERMISOS Y USUARIOS (Opcional - Ajustar según necesidad)
-- =============================================

-- Crear usuario para la aplicación
-- CREATE USER 'hotel_app'@'localhost' IDENTIFIED BY 'tu_password_seguro';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON hotel_management_system.* TO 'hotel_app'@'localhost';
-- FLUSH PRIVILEGES;

-- =============================================
-- FIN DEL SCRIPT
-- =============================================
