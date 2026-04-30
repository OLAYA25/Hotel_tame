-- Multi-Hotel System Tables
CREATE TABLE IF NOT EXISTS hoteles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    nombre_comercial VARCHAR(100),
    razon_social VARCHAR(100),
    nit VARCHAR(20) UNIQUE,
    direccion TEXT,
    ciudad VARCHAR(50),
    pais VARCHAR(50) DEFAULT 'Colombia',
    telefono VARCHAR(20),
    email VARCHAR(100),
    website VARCHAR(255),
    logo VARCHAR(255),
    categoria VARCHAR(50) DEFAULT '3 estrellas',
    tipo ENUM('hotel', 'hostal', 'apartamento', 'resort', 'boutique') DEFAULT 'hotel',
    capacidad_maxima INT DEFAULT 100,
    numero_habitaciones INT DEFAULT 50,
    timezone VARCHAR(50) DEFAULT 'America/Bogota',
    moneda VARCHAR(3) DEFAULT 'COP',
    idioma_principal VARCHAR(10) DEFAULT 'es',
    estado ENUM('activo', 'inactivo', 'mantenimiento', 'prueba') DEFAULT 'activo',
    plan_contrato ENUM('basico', 'profesional', 'enterprise') DEFAULT 'basico',
    fecha_contrato_inicio DATE,
    fecha_contrato_fin DATE,
    limite_habitaciones INT DEFAULT 100,
    limite_usuarios INT DEFAULT 10,
    limite_reservas_mes INT DEFAULT 1000,
    api_key VARCHAR(255) UNIQUE,
    api_secret VARCHAR(255),
    dominio_personalizado VARCHAR(255),
    subdominio VARCHAR(100),
    configuracion JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_estado (estado),
    INDEX idx_plan_contrato (plan_contrato),
    INDEX idx_api_key (api_key),
    INDEX idx_subdominio (subdominio),
    INDEX idx_deleted_at (deleted_at)
);

-- Hotel Users (Multi-tenancy)
CREATE TABLE IF NOT EXISTS hotel_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    usuario_id INT NOT NULL,
    rol ENUM('super_admin', 'admin', 'gerente', 'recepcion', 'limpieza', 'contabilidad', 'mantenimiento', 'personal') NOT NULL,
    permisos JSON,
    estado ENUM('activo', 'inactivo', 'suspendido') DEFAULT 'activo',
    fecha_contratacion DATE,
    salario DECIMAL(12,2),
    departamento VARCHAR(50),
    supervisor_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (hotel_id) REFERENCES hoteles(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (supervisor_id) REFERENCES hotel_usuarios(id) ON DELETE SET NULL,
    UNIQUE KEY unique_hotel_usuario (hotel_id, usuario_id),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_rol (rol),
    INDEX idx_estado (estado),
    INDEX idx_deleted_at (deleted_at)
);

-- Hotel Settings (per hotel)
CREATE TABLE IF NOT EXISTS hotel_configuraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    categoria VARCHAR(50),
    descripcion TEXT,
    editable TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (hotel_id) REFERENCES hoteles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_hotel_setting (hotel_id, setting_key),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_categoria (categoria),
    INDEX idx_deleted_at (deleted_at)
);

-- Hotel Statistics
CREATE TABLE IF NOT EXISTS hotel_estadisticas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    fecha DATE NOT NULL,
    metrica VARCHAR(50) NOT NULL,
    valor DECIMAL(15,2) NOT NULL,
    unidad VARCHAR(20),
    datos_adicionales JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hoteles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_hotel_fecha_metrica (hotel_id, fecha, metrica),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_fecha (fecha),
    INDEX idx_metrica (metrica)
);

-- Hotel Subscriptions/Billing
CREATE TABLE IF NOT EXISTS hotel_suscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL UNIQUE,
    plan_actual VARCHAR(50) NOT NULL,
    estado ENUM('activa', 'suspendida', 'cancelada', 'trial') DEFAULT 'trial',
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    fecha_ultima_facturacion DATE,
    proxima_facturacion DATE,
    monto_mensual DECIMAL(10,2) NOT NULL,
    moneda VARCHAR(3) DEFAULT 'COP',
    metodo_pago VARCHAR(50),
    datos_facturacion JSON,
    caracteristicas_incluidas JSON,
    uso_actual JSON,
    limite_excedido JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (hotel_id) REFERENCES hoteles(id) ON DELETE CASCADE,
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_estado (estado),
    INDEX idx_proxima_facturacion (proxima_facturacion),
    INDEX idx_deleted_at (deleted_at)
);

-- Hotel Activity Log
CREATE TABLE IF NOT EXISTS hotel_actividad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,
    modulo VARCHAR(50),
    detalles JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hoteles(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_created_at (created_at)
);

-- Update existing tables to support multi-hotel
ALTER TABLE usuarios ADD COLUMN hotel_id INT DEFAULT 1 AFTER id;
ALTER TABLE clientes ADD COLUMN hotel_id INT DEFAULT 1 AFTER id;
ALTER TABLE habitaciones ADD COLUMN hotel_id INT DEFAULT 1 AFTER id;
ALTER TABLE reservas ADD COLUMN hotel_id INT DEFAULT 1 AFTER id;
ALTER TABLE servicios ADD COLUMN hotel_id INT DEFAULT 1 AFTER id;
ALTER TABLE facturas ADD COLUMN hotel_id INT DEFAULT 1 AFTER id;
ALTER TABLE pagos ADD COLUMN hotel_id INT DEFAULT 1 AFTER id;

-- Add foreign keys for hotel_id
ALTER TABLE usuarios ADD CONSTRAINT fk_usuarios_hotel FOREIGN KEY (hotel_id) REFERENCES hoteles(id) ON DELETE RESTRICT;
ALTER TABLE clientes ADD CONSTRAINT fk_clientes_hotel FOREIGN KEY (hotel_id) REFERENCES hoteles(id) ON DELETE RESTRICT;
ALTER TABLE habitaciones ADD CONSTRAINT fk_habitaciones_hotel FOREIGN KEY (hotel_id) REFERENCES hoteles(id) ON DELETE RESTRICT;
ALTER TABLE reservas ADD CONSTRAINT fk_reservas_hotel FOREIGN KEY (hotel_id) REFERENCES hoteles(id) ON DELETE RESTRICT;
ALTER TABLE servicios ADD CONSTRAINT fk_servicios_hotel FOREIGN KEY (hotel_id) REFERENCES hoteles(id) ON DELETE RESTRICT;
ALTER TABLE facturas ADD CONSTRAINT fk_facturas_hotel FOREIGN KEY (hotel_id) REFERENCES hoteles(id) ON DELETE RESTRICT;
ALTER TABLE pagos ADD CONSTRAINT fk_pagos_hotel FOREIGN KEY (hotel_id) REFERENCES hoteles(id) ON DELETE RESTRICT;

-- Add indexes for hotel_id
ALTER TABLE usuarios ADD INDEX idx_hotel_id (hotel_id);
ALTER TABLE clientes ADD INDEX idx_hotel_id (hotel_id);
ALTER TABLE habitaciones ADD INDEX idx_hotel_id (hotel_id);
ALTER TABLE reservas ADD INDEX idx_hotel_id (hotel_id);
ALTER TABLE servicios ADD INDEX idx_hotel_id (hotel_id);
ALTER TABLE facturas ADD INDEX idx_hotel_id (hotel_id);
ALTER TABLE pagos ADD INDEX idx_hotel_id (hotel_id);

-- Update config_hotel to reference hoteles table
ALTER TABLE config_hotel ADD COLUMN hotel_id INT DEFAULT 1 AFTER id;
ALTER TABLE config_hotel ADD CONSTRAINT fk_config_hotel_hotel FOREIGN KEY (hotel_id) REFERENCES hoteles(id) ON DELETE CASCADE;
ALTER TABLE config_hotel ADD INDEX idx_hotel_id (hotel_id);

-- Insert default hotel
INSERT INTO hoteles (nombre, razon_social, nit, ciudad, pais, email, telefono, api_key, api_secret, subdominio) VALUES
('Hotel Tame', 'Hotel Tame S.A.S.', '900.123.456-7', 'Bogotá', 'Colombia', 'info@hoteltame.com', '+57 1 2345678', 'ht_demo_api_key_123456', 'ht_demo_api_secret_789012', 'hoteltame');

-- Update existing records to reference hotel 1
UPDATE usuarios SET hotel_id = 1 WHERE hotel_id IS NULL;
UPDATE clientes SET hotel_id = 1 WHERE hotel_id IS NULL;
UPDATE habitaciones SET hotel_id = 1 WHERE hotel_id IS NULL;
UPDATE reservas SET hotel_id = 1 WHERE hotel_id IS NULL;
UPDATE servicios SET hotel_id = 1 WHERE hotel_id IS NULL;
UPDATE facturas SET hotel_id = 1 WHERE hotel_id IS NULL;
UPDATE pagos SET hotel_id = 1 WHERE hotel_id IS NULL;
UPDATE config_hotel SET hotel_id = 1 WHERE hotel_id IS NULL;

-- Create hotel subscription record
INSERT INTO hotel_suscripciones (hotel_id, plan_actual, estado, fecha_inicio, monto_mensual, caracteristicas_incluidas) VALUES
(1, 'profesional', 'trial', CURDATE(), 299000.00, JSON_OBJECT('habitaciones', 100, 'usuarios', 20, 'reservas_mes', 5000, 'api_access', true, 'reports', true, 'multi_language', true));

-- Create hotel configuration for Hotel Tame
INSERT INTO hotel_configuraciones (hotel_id, setting_key, setting_value, setting_type, categoria, descripcion) VALUES
(1, 'nombre', 'Hotel Tame', 'string', 'general', 'Nombre del hotel'),
(1, 'moneda', 'COP', 'string', 'general', 'Moneda del hotel'),
(1, 'timezone', 'America/Bogota', 'string', 'general', 'Zona horaria'),
(1, 'idioma_principal', 'es', 'string', 'general', 'Idioma principal'),
(1, 'checkin_time', '15:00', 'string', 'operaciones', 'Hora de check-in'),
(1, 'checkout_time', '12:00', 'string', 'operaciones', 'Hora de check-out'),
(1, 'impuesto_iva', '19.00', 'number', 'fiscal', 'Porcentaje de IVA'),
(1, 'impuesto_turismo', '0.00', 'number', 'fiscal', 'Porcentaje de impuesto turístico'),
(1, 'politica_cancelacion', 'Cancelación gratuita hasta 48 horas', 'string', 'politicas', 'Política de cancelación'),
(1, 'wifi_incluido', 'true', 'boolean', 'servicios', 'WiFi incluido'),
(1, 'desayuno_incluido', 'false', 'boolean', 'servicios', 'Desayuno incluido'),
(1, 'parking_incluido', 'false', 'boolean', 'servicios', 'Estacionamiento incluido'),
(1, 'enable_online_booking', 'true', 'boolean', 'reservas', 'Habilitar reservas online'),
(1, 'enable_payment_online', 'true', 'boolean', 'pagos', 'Habilitar pagos online'),
(1, 'auto_send_confirmation', 'true', 'boolean', 'notificaciones', 'Enviar confirmación automática'),
(1, 'enable_reviews', 'true', 'boolean', 'reviews', 'Habilitar reseñas'),
(1, 'enable_loyalty_program', 'true', 'boolean', 'lealtad', 'Habilitar programa de lealtad');

-- Create hotel users for existing users
INSERT INTO hotel_usuarios (hotel_id, usuario_id, rol, estado) 
SELECT 1, id, 
       CASE 
           WHEN rol = 'admin' THEN 'super_admin'
           WHEN rol = 'gerente' THEN 'gerente'
           WHEN rol = 'recepcion' THEN 'recepcion'
           WHEN rol = 'limpieza' THEN 'limpieza'
           WHEN rol = 'contabilidad' THEN 'contabilidad'
           ELSE 'personal'
       END,
       'activo'
FROM usuarios 
WHERE deleted_at IS NULL AND hotel_id = 1;
