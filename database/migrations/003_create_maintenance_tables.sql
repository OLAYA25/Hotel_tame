-- Maintenance Requests Table
CREATE TABLE IF NOT EXISTS mantenimiento_habitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habitacion_id INT NOT NULL,
    descripcion TEXT NOT NULL,
    prioridad ENUM('baja', 'media', 'alta', 'urgente') NOT NULL DEFAULT 'media',
    estado ENUM('abierto', 'en_progreso', 'resuelto', 'cancelado') NOT NULL DEFAULT 'abierto',
    reportado_por INT NOT NULL,
    asignado_a INT,
    fecha_reporte TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_asignacion TIMESTAMP NULL,
    fecha_resolucion TIMESTAMP NULL,
    tipo_mantenimiento ENUM('preventivo', 'correctivo', 'emergencia') NOT NULL DEFAULT 'correctivo',
    categoria VARCHAR(50),
    costo_estimado DECIMAL(10,2) DEFAULT 0.00,
    costo_real DECIMAL(10,2) DEFAULT 0.00,
    observaciones TEXT,
    resolucion TEXT,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE RESTRICT,
    FOREIGN KEY (reportado_por) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (asignado_a) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_estado (estado),
    INDEX idx_prioridad (prioridad),
    INDEX idx_fecha_reporte (fecha_reporte),
    INDEX idx_habitacion (habitacion_id),
    INDEX idx_reportado_por (reportado_por),
    INDEX idx_asignado_a (asignado_a),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Maintenance Materials/Parts
CREATE TABLE IF NOT EXISTS mantenimiento_materiales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    codigo VARCHAR(50) UNIQUE,
    stock_actual INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 5,
    unidad_medida VARCHAR(20) DEFAULT 'unidad',
    costo_unitario DECIMAL(10,2) DEFAULT 0.00,
    proveedor VARCHAR(100),
    categoria VARCHAR(50),
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_categoria (categoria),
    INDEX idx_stock_bajo (stock_actual, stock_minimo),
    INDEX idx_codigo (codigo),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Maintenance Task Materials (Many-to-Many)
CREATE TABLE IF NOT EXISTS mantenimiento_tarea_materiales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mantenimiento_id INT NOT NULL,
    material_id INT NOT NULL,
    cantidad_usada INT NOT NULL DEFAULT 1,
    costo_unitario DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mantenimiento_id) REFERENCES mantenimiento_habitaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES mantenimiento_materiales(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_task_material (mantenimiento_id, material_id),
    INDEX idx_mantenimiento_id (mantenimiento_id),
    INDEX idx_material_id (material_id)
);

-- Maintenance Schedule (Preventive)
CREATE TABLE IF NOT EXISTS mantenimiento_programado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habitacion_id INT NOT NULL,
    tipo_mantenimiento VARCHAR(100) NOT NULL,
    frecuencia ENUM('diario', 'semanal', 'mensual', 'trimestral', 'semestral', 'anual') NOT NULL,
    ultima_ejecucion TIMESTAMP NULL,
    proxima_ejecucion DATE NOT NULL,
    observaciones TEXT,
    activo TINYINT(1) DEFAULT 1,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE RESTRICT,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_proxima_ejecucion (proxima_ejecucion),
    INDEX idx_habitacion (habitacion_id),
    INDEX idx_activo (activo),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Insert default maintenance categories and scheduled tasks
INSERT INTO mantenimiento_programado (habitacion_id, tipo_mantenimiento, frecuencia, proxima_ejecucion, observaciones) 
SELECT 
    h.id,
    'Inspección general de aire acondicionado',
    'trimestral',
    DATE_ADD(CURDATE(), INTERVAL 3 MONTH),
    'Revisar filtros, limpieza general'
FROM habitaciones h;

INSERT INTO mantenimiento_programado (habitacion_id, tipo_mantenimiento, frecuencia, proxima_ejecucion, observaciones) 
SELECT 
    h.id,
    'Inspección eléctrica general',
    'semestral',
    DATE_ADD(CURDATE(), INTERVAL 6 MONTH),
    'Revisar conexiones, interruptores, toma corrientes'
FROM habitaciones h;

INSERT INTO mantenimiento_programado (habitacion_id, tipo_mantenimiento, frecuencia, proxima_ejecucion, observaciones) 
SELECT 
    h.id,
    'Revisión de plomería',
    'trimestral',
    DATE_ADD(CURDATE(), INTERVAL 3 MONTH),
    'Revisar fugas, desagües, grifos'
FROM habitaciones h;
