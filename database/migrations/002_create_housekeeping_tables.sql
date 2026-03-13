-- Housekeeping Tasks Table
CREATE TABLE IF NOT EXISTS housekeeping_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habitacion_id INT NOT NULL,
    tipo_limpieza ENUM('regular', 'profunda', 'checkout', 'especial') NOT NULL DEFAULT 'regular',
    estado ENUM('pendiente', 'en_progreso', 'completada', 'inspeccion') NOT NULL DEFAULT 'pendiente',
    asignado_a INT,
    fecha_programada DATE NOT NULL,
    fecha_realizada TIMESTAMP NULL,
    fecha_inspeccion TIMESTAMP NULL,
    inspector_id INT,
    observaciones TEXT,
    tiempo_estimado_minutes INT DEFAULT 30,
    tiempo_real_minutes INT,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE RESTRICT,
    FOREIGN KEY (asignado_a) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (inspector_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_estado (estado),
    INDEX idx_fecha_programada (fecha_programada),
    INDEX idx_asignado_a (asignado_a),
    INDEX idx_habitacion (habitacion_id),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Housekeeping Supplies Table
CREATE TABLE IF NOT EXISTS housekeeping_supplies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    stock_actual INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 10,
    unidad_medida VARCHAR(20) DEFAULT 'unidad',
    costo_unitario DECIMAL(10,2) DEFAULT 0.00,
    categoria VARCHAR(50),
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_categoria (categoria),
    INDEX idx_stock_bajo (stock_actual, stock_minimo),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Housekeeping Task Supplies (Many-to-Many)
CREATE TABLE IF NOT EXISTS housekeeping_task_supplies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    supply_id INT NOT NULL,
    cantidad_usada INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES housekeeping_tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (supply_id) REFERENCES housekeeping_supplies(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_task_supply (task_id, supply_id),
    INDEX idx_task_id (task_id),
    INDEX idx_supply_id (supply_id)
);

-- Housekeeping Inspection Checklist
CREATE TABLE IF NOT EXISTS housekeeping_checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria VARCHAR(50),
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_categoria (categoria),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Housekeeping Task Checklist Items
CREATE TABLE IF NOT EXISTS housekeeping_task_checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    checklist_id INT NOT NULL,
    completado TINYINT(1) DEFAULT 0,
    observaciones TEXT,
    inspector_id INT,
    fecha_inspeccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES housekeeping_tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (checklist_id) REFERENCES housekeeping_checklist(id) ON DELETE RESTRICT,
    FOREIGN KEY (inspector_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_task_id (task_id),
    INDEX idx_checklist_id (checklist_id),
    INDEX idx_inspector_id (inspector_id)
);

-- Insert default housekeeping checklist items
INSERT INTO housekeeping_checklist (nombre, descripcion, categoria) VALUES
('Limpiar baño', 'Limpiar y desinfectar todas las superficies del baño', 'baño'),
('Cambiar sábanas', 'Cambiar todas las sábanas y fundas', 'cama'),
('Limpiar pisos', 'Barrer y trapear todos los pisos', 'piso'),
('Limpiar ventanas', 'Limpiar todas las ventanas y espejos', 'ventanas'),
('Reponer amenities', 'Reponer jabón, champú, etc.', 'amenities'),
('Limpiar cocina', 'Limpiar cocina y utensilios (si aplica)', 'cocina'),
('Verificar electrodomésticos', 'Verificar que todos funcionen correctamente', 'electrodomésticos'),
('Limpiar polvo', 'Limpiar polvo de todas las superficies', 'general');
