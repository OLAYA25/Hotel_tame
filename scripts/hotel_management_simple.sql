-- =====================================================
-- SISTEMA DE GESTIÓN HOTELERA - SQL SIMPLIFICADO
-- Compatible con MySQL 5.x y MariaDB 10.x
-- =====================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS hotel_management_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_management_system;

-- =====================================================
-- TABLA: usuarios
-- =====================================================
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'recepcionista', 'gerente') DEFAULT 'recepcionista',
    telefono VARCHAR(20),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_rol (rol),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: clientes
-- =====================================================
DROP TABLE IF EXISTS clientes;
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    documento VARCHAR(50) NOT NULL UNIQUE,
    tipo_documento ENUM('DNI', 'Pasaporte', 'Cedula') DEFAULT 'DNI',
    direccion TEXT,
    ciudad VARCHAR(100),
    pais VARCHAR(100) DEFAULT 'Colombia',
    fecha_nacimiento DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_documento (documento),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: habitaciones
-- =====================================================
DROP TABLE IF EXISTS habitaciones;
CREATE TABLE habitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(10) NOT NULL UNIQUE,
    tipo ENUM('simple', 'doble', 'suite', 'presidencial') NOT NULL,
    precio_noche DECIMAL(10,2) NOT NULL,
    capacidad INT NOT NULL DEFAULT 1,
    estado ENUM('disponible', 'ocupada', 'mantenimiento', 'limpieza') DEFAULT 'disponible',
    piso INT NOT NULL,
    descripcion TEXT,
    amenidades JSON,
    imagen_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_numero (numero),
    INDEX idx_tipo (tipo),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: reservas
-- =====================================================
DROP TABLE IF EXISTS reservas;
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    habitacion_id INT NOT NULL,
    usuario_id INT,
    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,
    num_huespedes INT DEFAULT 1,
    estado ENUM('pendiente', 'confirmada', 'checkin', 'checkout', 'cancelada') DEFAULT 'pendiente',
    precio_noche DECIMAL(10,2) NOT NULL,
    num_noches INT NOT NULL,
    precio_total DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') DEFAULT 'efectivo',
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_cliente (cliente_id),
    INDEX idx_habitacion (habitacion_id),
    INDEX idx_estado (estado),
    INDEX idx_fechas (fecha_entrada, fecha_salida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: pagos
-- =====================================================
DROP TABLE IF EXISTS pagos;
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    metodo ENUM('efectivo', 'tarjeta', 'transferencia') NOT NULL,
    estado ENUM('pendiente', 'completado', 'rechazado') DEFAULT 'completado',
    referencia VARCHAR(100),
    notas TEXT,
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    INDEX idx_reserva (reserva_id),
    INDEX idx_fecha (fecha_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERTAR DATOS DE PRUEBA
-- =====================================================

-- Usuarios (password: admin123)
INSERT INTO usuarios (nombre, apellido, email, password, rol, telefono) VALUES
('Carlos', 'Administrador', 'admin@hotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '3001234567'),
('Maria', 'Recepcionista', 'recepcion@hotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recepcionista', '3007654321'),
('Juan', 'Gerente', 'gerente@hotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gerente', '3009876543');

-- Clientes
INSERT INTO clientes (nombre, apellido, email, telefono, documento, tipo_documento, direccion, ciudad, pais, fecha_nacimiento) VALUES
('Jhuliet', 'Tibasosa', 'jhuliet@gmail.com', '3101234567', '1234567890', 'DNI', 'Calle 123 #45-67', 'Bogotá', 'Colombia', '1990-05-15'),
('Juan', 'Pérez', 'juan.perez@email.com', '3107654321', '0987654321', 'DNI', 'Carrera 45 #12-34', 'Medellín', 'Colombia', '1985-08-20'),
('Ana', 'López', 'ana.lopez@email.com', '3159876543', '1122334455', 'Cedula', 'Avenida 68 #89-01', 'Cali', 'Colombia', '1992-12-10'),
('Pedro', 'Martínez', 'pedro.martinez@email.com', '3201239876', '5544332211', 'Pasaporte', 'Diagonal 27 #34-56', 'Cartagena', 'Colombia', '1988-03-25');

-- Habitaciones
INSERT INTO habitaciones (numero, tipo, precio_noche, capacidad, descripcion, estado, piso, amenidades) VALUES
('101', 'simple', 150000, 1, 'Habitación simple con cama individual, baño privado y TV', 'disponible', 1, '["WiFi", "TV", "Aire acondicionado"]'),
('102', 'doble', 250000, 2, 'Habitación doble con dos camas, baño privado, TV y minibar', 'disponible', 1, '["WiFi", "TV", "Minibar", "Aire acondicionado"]'),
('201', 'suite', 450000, 4, 'Suite amplia con sala, dormitorio, baño de lujo y balcón', 'ocupada', 2, '["WiFi", "TV", "Minibar", "Jacuzzi", "Balcón"]'),
('301', 'presidencial', 800000, 6, 'Suite presidencial con 2 habitaciones, sala, comedor, cocina y terraza', 'disponible', 3, '["WiFi", "TV", "Minibar", "Cocina", "Terraza", "Jacuzzi"]'),
('103', 'simple', 150000, 1, 'Habitación simple económica', 'disponible', 1, '["WiFi", "TV"]'),
('104', 'doble', 250000, 2, 'Habitación doble estándar', 'mantenimiento', 1, '["WiFi", "TV", "Aire acondicionado"]');

-- Reservas
INSERT INTO reservas (cliente_id, habitacion_id, usuario_id, fecha_entrada, fecha_salida, num_huespedes, estado, precio_noche, num_noches, precio_total, metodo_pago) VALUES
(1, 3, 2, '2025-01-13', '2025-01-15', 2, 'confirmada', 450000, 2, 900000, 'tarjeta'),
(2, 2, 2, '2025-01-14', '2025-01-16', 2, 'confirmada', 250000, 2, 500000, 'efectivo'),
(3, 1, 1, '2025-01-12', '2025-01-14', 1, 'checkout', 150000, 2, 300000, 'transferencia'),
(4, 4, 1, '2025-01-15', '2025-01-18', 4, 'pendiente', 800000, 3, 2400000, 'tarjeta');

-- Pagos
INSERT INTO pagos (reserva_id, monto, metodo, estado, referencia) VALUES
(1, 900000, 'tarjeta', 'completado', 'TRX-2025-001'),
(2, 500000, 'efectivo', 'completado', 'EFE-2025-002'),
(3, 300000, 'transferencia', 'completado', 'TRANS-2025-003');

-- =====================================================
-- MENSAJE DE ÉXITO
-- =====================================================
SELECT 'Base de datos hotel_management_system creada exitosamente!' AS Mensaje;
SELECT COUNT(*) AS TotalUsuarios FROM usuarios;
SELECT COUNT(*) AS TotalClientes FROM clientes;
SELECT COUNT(*) AS TotalHabitaciones FROM habitaciones;
SELECT COUNT(*) AS TotalReservas FROM reservas;
SELECT COUNT(*) AS TotalPagos FROM pagos;
