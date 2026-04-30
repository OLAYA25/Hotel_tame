-- Dynamic Pricing Tables
CREATE TABLE IF NOT EXISTS tarifas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_habitacion_id VARCHAR(50) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    temporada ENUM('baja', 'media', 'alta', 'especial') NOT NULL,
    descripcion VARCHAR(200),
    minimo_noches INT DEFAULT 1,
    maximo_noches INT DEFAULT 30,
    politica_cancelacion ENUM('flexible', 'moderada', 'estricta') DEFAULT 'moderada',
    porcentaje_anticipo DECIMAL(5,2) DEFAULT 20.00,
    activa TINYINT(1) DEFAULT 1,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_tipo_habitacion (tipo_habitacion_id),
    INDEX idx_fechas (fecha_inicio, fecha_fin),
    INDEX idx_temporada (temporada),
    INDEX idx_activa (activa),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Pricing Rules
CREATE TABLE IF NOT EXISTS pricing_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    tipo_regla ENUM('aumento_porcentaje', 'disminucion_porcentaje', 'monto_fijo', 'ocupacion_alta', 'ocupacion_baja', 'ultimo_minuto', 'anticipado') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    condiciones JSON,
    tipo_habitacion_id VARCHAR(50),
    dias_anticipacion_min INT DEFAULT 0,
    dias_anticipacion_max INT DEFAULT 365,
    umbral_ocupacion_min DECIMAL(5,2) DEFAULT 0.00,
    umbral_ocupacion_max DECIMAL(5,2) DEFAULT 100.00,
    activa TINYINT(1) DEFAULT 1,
    prioridad INT DEFAULT 0,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_tipo_regla (tipo_regla),
    INDEX idx_activa (activa),
    INDEX idx_prioridad (prioridad),
    INDEX idx_tipo_habitacion (tipo_habitacion_id),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Competitor Pricing
CREATE TABLE IF NOT EXISTS competitor_pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    competidor VARCHAR(100) NOT NULL,
    tipo_habitacion VARCHAR(50) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    fecha_registro DATE NOT NULL,
    fuente VARCHAR(50),
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_competidor (competidor),
    INDEX idx_tipo_habitacion (tipo_habitacion),
    INDEX idx_fecha_registro (fecha_registro),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Pricing History
CREATE TABLE IF NOT EXISTS pricing_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habitacion_id INT NOT NULL,
    fecha DATE NOT NULL,
    precio_base DECIMAL(10,2) NOT NULL,
    precio_final DECIMAL(10,2) NOT NULL,
    reglas_aplicadas JSON,
    ocupacion_diaria DECIMAL(5,2) DEFAULT 0.00,
    reservas_diarias INT DEFAULT 0,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE RESTRICT,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_habitacion_fecha (habitacion_id, fecha),
    INDEX idx_fecha (fecha),
    INDEX idx_ocupacion (ocupacion_diaria),
    INDEX idx_hotel_id (hotel_id)
);

-- Insert default seasonal pricing
INSERT INTO tarifas (tipo_habitacion_id, fecha_inicio, fecha_fin, precio, temporada, descripcion, minimo_noches, politica_cancelacion) VALUES
('estandar', '2024-01-01', '2024-03-31', 80000.00, 'baja', 'Temporada baja - Estandar', 2, 'flexible'),
('estandar', '2024-04-01', '2024-06-30', 120000.00, 'media', 'Temporada media - Estandar', 1, 'moderada'),
('estandar', '2024-07-01', '2024-08-31', 180000.00, 'alta', 'Temporada alta - Estandar', 3, 'estricta'),
('estandar', '2024-09-01', '2024-12-31', 100000.00, 'media', 'Temporada media - Estandar', 2, 'moderada'),
('suite', '2024-01-01', '2024-03-31', 150000.00, 'baja', 'Temporada baja - Suite', 2, 'flexible'),
('suite', '2024-04-01', '2024-06-30', 200000.00, 'media', 'Temporada media - Suite', 1, 'moderada'),
('suite', '2024-07-01', '2024-08-31', 300000.00, 'alta', 'Temporada alta - Suite', 3, 'estricta'),
('suite', '2024-09-01', '2024-12-31', 180000.00, 'media', 'Temporada media - Suite', 2, 'moderada');

-- Insert default pricing rules
INSERT INTO pricing_rules (nombre, descripcion, tipo_regla, valor, dias_anticipacion_min, dias_anticipacion_max, prioridad) VALUES
('Último minuto', 'Aumento para reservas de último minuto', 'ultimo_minuto', 15.00, 0, 3, 10),
('Anticipado', 'Descuento para reservas con mucha anticipación', 'anticipado', -10.00, 30, 365, 10),
('Ocupación alta', 'Aumento cuando la ocupación es alta', 'ocupacion_alta', 20.00, 0, 365, 5),
('Ocupación baja', 'Descuento cuando la ocupación es baja', 'ocupacion_baja', -15.00, 0, 365, 5),
('Fin de semana', 'Aumento para fines de semana', 'aumento_porcentaje', 25.00, 0, 365, 8);
