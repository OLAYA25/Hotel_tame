-- ========================================
-- Hotel Tame PMS - Estructura de Base de Datos Completa
-- ========================================

-- Tabla de configuración del hotel
CREATE TABLE IF NOT EXISTS config_hotel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion TEXT,
    telefono VARCHAR(20),
    email VARCHAR(100),
    ciudad VARCHAR(50),
    pais VARCHAR(50) DEFAULT 'Colombia',
    nit VARCHAR(20),
    logo VARCHAR(255),
    checkin_time TIME DEFAULT '15:00:00',
    checkout_time TIME DEFAULT '12:00:00',
    moneda VARCHAR(3) DEFAULT 'COP',
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_hotel_id (hotel_id)
);

-- Tabla de roles del sistema
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    permisos JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    rol_id INT NOT NULL,
    hotel_id INT DEFAULT 1,
    activo TINYINT(1) DEFAULT 1,
    ultimo_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_email (email),
    INDEX idx_rol (rol_id),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_activo (activo),
    INDEX idx_deleted_at (deleted_at)
);

-- Tabla de clientes (fuente única de personas)
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    documento VARCHAR(20) UNIQUE,
    tipo_documento VARCHAR(20) DEFAULT 'CC',
    direccion TEXT,
    ciudad VARCHAR(50),
    pais VARCHAR(50) DEFAULT 'Colombia',
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_documento (documento),
    INDEX idx_email (email),
    INDEX idx_nombre (nombre, apellido),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Tabla de habitaciones
CREATE TABLE IF NOT EXISTS habitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(10) NOT NULL UNIQUE,
    tipo VARCHAR(50) NOT NULL,
    capacidad INT NOT NULL DEFAULT 2,
    precio_base DECIMAL(10,2) NOT NULL,
    estado ENUM('disponible', 'reservada', 'ocupada', 'limpieza', 'mantenimiento', 'fuera_servicio') DEFAULT 'disponible',
    piso INT,
    descripcion TEXT,
    amenities JSON,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_estado (estado),
    INDEX idx_tipo (tipo),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_numero (numero),
    INDEX idx_deleted_at (deleted_at)
);

-- Tabla de reservas
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habitacion_id INT NOT NULL,
    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'ocupada', 'finalizada', 'cancelada') DEFAULT 'pendiente',
    precio_total DECIMAL(10,2) NOT NULL,
    observaciones TEXT,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE RESTRICT,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_fechas (fecha_entrada, fecha_salida),
    INDEX idx_estado (estado),
    INDEX idx_habitacion (habitacion_id),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Tabla de relación entre reservas y clientes (pivot)
CREATE TABLE IF NOT EXISTS reserva_clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    cliente_id INT NOT NULL,
    rol ENUM('titular', 'acompanante') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_reserva_cliente (reserva_id, cliente_id),
    INDEX idx_reserva (reserva_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_rol (rol)
);

-- Tabla de check-in/check-out
CREATE TABLE IF NOT EXISTS checkin_checkout (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    fecha_checkin TIMESTAMP NULL,
    fecha_checkout TIMESTAMP NULL,
    usuario_checkin INT,
    usuario_checkout INT,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_checkin) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_checkout) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_reserva (reserva_id),
    INDEX idx_usuario_checkin (usuario_checkin),
    INDEX idx_usuario_checkout (usuario_checkout)
);

-- Tabla de servicios adicionales
CREATE TABLE IF NOT EXISTS servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    tipo ENUM('habitacion', 'persona', 'evento', 'otro') DEFAULT 'habitacion',
    activo TINYINT(1) DEFAULT 1,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_tipo (tipo),
    INDEX idx_activo (activo),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Tabla de consumo de servicios en reservas
CREATE TABLE IF NOT EXISTS reserva_servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    servicio_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    fecha_consumo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT,
    observaciones TEXT,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_reserva (reserva_id),
    INDEX idx_servicio (servicio_id),
    INDEX idx_usuario (usuario_id)
);

-- Tabla de productos (minibar/inventario)
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    stock_actual INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 5,
    categoria VARCHAR(50),
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_categoria (categoria),
    INDEX idx_stock (stock_actual),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Tabla de consumo de productos en habitaciones
CREATE TABLE IF NOT EXISTS consumo_habitacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    fecha_consumo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT,
    observaciones TEXT,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_reserva (reserva_id),
    INDEX idx_producto (producto_id),
    INDEX idx_usuario (usuario_id)
);

-- Tabla de facturas
CREATE TABLE IF NOT EXISTS facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    numero_factura VARCHAR(50) NOT NULL UNIQUE,
    subtotal DECIMAL(10,2) NOT NULL,
    impuestos DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('emitida', 'pagada', 'cancelada') DEFAULT 'emitida',
    fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_pago TIMESTAMP NULL,
    metodo_pago VARCHAR(50),
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE RESTRICT,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_numero (numero_factura),
    INDEX idx_estado (estado),
    INDEX idx_reserva (reserva_id),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Tabla de historial de cambios de estado de habitaciones
CREATE TABLE IF NOT EXISTS historial_habitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habitacion_id INT NOT NULL,
    estado_anterior VARCHAR(20) NOT NULL,
    estado_nuevo VARCHAR(20) NOT NULL,
    usuario_id INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observaciones TEXT,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_habitacion (habitacion_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha)
);

-- Tabla de logs de auditoría
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,
    tabla VARCHAR(50) NOT NULL,
    registro_id INT,
    datos_anteriores JSON,
    datos_nuevos JSON,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(45),
    user_agent TEXT,
    hotel_id INT DEFAULT 1,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_usuario (usuario_id),
    INDEX idx_tabla (tabla),
    INDEX idx_fecha (fecha),
    INDEX idx_hotel_id (hotel_id)
);

-- Tabla de migraciones
CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar datos iniciales
INSERT INTO roles (nombre, descripcion, permisos) VALUES
('admin', 'Administrador del sistema', '{"all": true}'),
('recepcion', 'Personal de recepción', '{"reservas": ["read", "write"], "clientes": ["read", "write"], "checkin": true}'),
('limpieza', 'Personal de limpieza', '{"habitaciones": ["read", "update_status"]}'),
('gerente', 'Gerente del hotel', '{"reports": true, "reservas": ["read", "write", "delete"], "finanzas": true}'),
('contabilidad', 'Contador', '{"facturas": ["read", "write"], "reports": true, "finanzas": true}');

INSERT INTO config_hotel (nombre, direccion, telefono, email, ciudad, pais, nit) VALUES
('Hotel Tame', 'Calle Principal #123', '+57 1 2345678', 'info@hoteltame.com', 'Bogotá', 'Colombia', '900.123.456-7');
