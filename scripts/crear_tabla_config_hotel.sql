-- Crear tabla de configuración del hotel
CREATE TABLE IF NOT EXISTS hotel_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    descripcion TEXT,
    tipo VARCHAR(20) DEFAULT 'string',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_clave (clave)
);

-- Insertar configuración inicial
INSERT IGNORE INTO hotel_config (clave, valor, descripcion, tipo) VALUES
('nombre_hotel', 'Hotel Tame', 'Nombre del hotel', 'string'),
('direccion', 'Calle Principal #123', 'Dirección del hotel', 'string'),
('telefono', '+57 1 234 5678', 'Teléfono principal', 'string'),
('email', 'info@hotel-tame.com', 'Email principal', 'string'),
('moneda', 'COP', 'Moneda por defecto', 'string'),
('idioma', 'es', 'Idioma por defecto', 'string'),
('timezone', 'America/Bogota', 'Zona horaria', 'string'),
('checkin_time', '15:00', 'Hora de check-in', 'time'),
('checkout_time', '12:00', 'Hora de check-out', 'time'),
('max_huespedes_habitacion', 4, 'Máximo de huéspedes por habitación', 'integer'),
('politica_cancelacion', '24', 'Horas de política de cancelación', 'integer'),
('deposito_porcentaje', 20, 'Porcentaje de depósito requerido', 'decimal'),
('impuesto_hospedaje', 19, 'Porcentaje de impuesto de alojamiento', 'decimal'),
('servicios_incluidos', 'WiFi, Desayuno, Aire Acondicionado', 'Servicios incluidos', 'text'),
('logo_url', 'assets/images/logo-hotel.png', 'URL del logo', 'string'),
('activo', 1, 'Configuración activa', 'boolean');

-- Crear tabla de metas del hotel
CREATE TABLE IF NOT EXISTS metas_hotel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mes INT NOT NULL,
    anio INT NOT NULL,
    meta_revenue DECIMAL(12,2),
    meta_ocupacion DECIMAL(5,2),
    meta_reservas INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_mes_anio (mes, anio),
    INDEX idx_periodo (mes, anio)
);

-- Insertar metas para el mes actual
INSERT IGNORE INTO metas_hotel (mes, anio, meta_revenue, meta_ocupacion, meta_reservas) VALUES
(MONTH(CURRENT_DATE), YEAR(CURRENT_DATE), 50000000, 75.0, 150);

-- Crear tabla de logs del sistema
CREATE TABLE IF NOT EXISTS sistema_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nivel VARCHAR(20) NOT NULL,
    mensaje TEXT NOT NULL,
    contexto JSON,
    usuario_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nivel (nivel),
    INDEX idx_usuario (usuario_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);
