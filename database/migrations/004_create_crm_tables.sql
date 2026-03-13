-- Guest Preferences Table
CREATE TABLE IF NOT EXISTS clientes_preferencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    tipo_habitacion_preferida VARCHAR(50),
    piso_preferido INT,
    cama_preferida ENUM('matrimonial', 'individual', 'queen', 'king') DEFAULT 'matrimonial',
    vista_preferida ENUM('ciudad', 'jardin', 'montaña', 'piscina', 'ninguna') DEFAULT 'ninguna',
    fumar TINYINT(1) DEFAULT 0,
    mascotas TINYINT(1) DEFAULT 0,
    alergias TEXT,
    necesidades_especiales TEXT,
    preferencias_comida TEXT,
    preferencias_temperatura VARCHAR(20) DEFAULT 'normal',
    ruido_preferido ENUM('silencio', 'moderado', 'normal') DEFAULT 'normal',
    frecuencia_visita ENUM('primera_vez', 'ocasional', 'frecuente', 'muy_frecuente') DEFAULT 'ocasional',
    motivo_viaje VARCHAR(100),
    observaciones TEXT,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_cliente_id (cliente_id),
    INDEX idx_tipo_habitacion (tipo_habitacion_preferida),
    INDEX idx_frecuencia_visita (frecuencia_visita),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Guest Loyalty Program
CREATE TABLE IF NOT EXISTS clientes_loyalty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL UNIQUE,
    nivel VARCHAR(20) DEFAULT 'bronze',
    puntos_acumulados INT DEFAULT 0,
    puntos_expiran INT DEFAULT 0,
    total_estanias INT DEFAULT 0,
    total_noches INT DEFAULT 0,
    total_gastado DECIMAL(12,2) DEFAULT 0.00,
    ultima_estancia DATE NULL,
    proxima_estancia DATE NULL,
    fecha_ultimo_punto DATE NULL,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_cliente_id (cliente_id),
    INDEX idx_nivel (nivel),
    INDEX idx_puntos_acumulados (puntos_acumulados),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Guest Reviews and Feedback
CREATE TABLE IF NOT EXISTS habitacion_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    cliente_id INT NOT NULL,
    habitacion_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    titulo VARCHAR(200),
    comentario TEXT,
    limpieza_rating TINYINT CHECK (limpieza_rating >= 1 AND limpieza_rating <= 5),
    servicio_rating TINYINT CHECK (servicio_rating >= 1 AND servicio_rating <= 5),
    comodidad_rating TINYINT CHECK (comodidad_rating >= 1 AND comodidad_rating <= 5),
    ubicacion_rating TINYINT CHECK (ubicacion_rating >= 1 AND ubicacion_rating <= 5),
    precio_rating TINYINT CHECK (precio_rating >= 1 AND precio_rating <= 5),
    respuesta_hotel TEXT,
    fecha_respuesta TIMESTAMP NULL,
    respondido_por INT,
    fecha_review TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE RESTRICT,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE RESTRICT,
    FOREIGN KEY (respondido_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_reserva_id (reserva_id),
    INDEX idx_cliente_id (cliente_id),
    INDEX idx_habitacion_id (habitacion_id),
    INDEX idx_rating (rating),
    INDEX idx_fecha_review (fecha_review),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Guest Communication History
CREATE TABLE IF NOT EXISTS clientes_comunicaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    tipo_comunicacion ENUM('email', 'telefono', 'whatsapp', 'sms', 'presencial') NOT NULL,
    asunto VARCHAR(200),
    mensaje TEXT,
    enviado_por INT,
    fecha_comunicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    proximo_seguimiento DATE NULL,
    categoria VARCHAR(50),
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (enviado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_cliente_id (cliente_id),
    INDEX idx_tipo_comunicacion (tipo_comunicacion),
    INDEX idx_fecha_comunicacion (fecha_comunicacion),
    INDEX idx_proximo_seguimiento (proximo_seguimiento),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Guest Special Requests
CREATE TABLE IF NOT EXISTS clientes_solicitudes_especiales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    reserva_id INT,
    tipo_solicitud VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado ENUM('pendiente', 'aprobada', 'rechazada', 'completada') DEFAULT 'pendiente',
    costo_adicional DECIMAL(10,2) DEFAULT 0.00,
    respondido_por INT,
    fecha_respuesta TIMESTAMP NULL,
    observaciones TEXT,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (respondido_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_cliente_id (cliente_id),
    INDEX idx_reserva_id (reserva_id),
    INDEX idx_estado (estado),
    INDEX idx_tipo_solicitud (tipo_solicitud),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Insert default loyalty levels
INSERT INTO clientes_loyalty (cliente_id, nivel) VALUES (1, 'bronze');

-- Create trigger to update loyalty points on reservation completion
DELIMITER //
CREATE TRIGGER update_loyalty_on_reservation
AFTER UPDATE ON reservas
FOR EACH ROW
BEGIN
    IF NEW.estado = 'finalizada' AND OLD.estado != 'finalizada' THEN
        INSERT INTO clientes_loyalty (cliente_id, nivel, puntos_acumulados, total_estanias, total_noches, total_gastado, ultima_estancia)
        VALUES (NEW.id, 'bronze', 100, 1, DATEDIFF(NEW.fecha_salida, NEW.fecha_entrada), NEW.precio_total, NEW.fecha_entrada)
        ON DUPLICATE KEY UPDATE 
            puntos_acumulados = puntos_acumulados + 100,
            total_estanias = total_estanias + 1,
            total_noches = total_noches + DATEDIFF(NEW.fecha_salida, NEW.fecha_entrada),
            total_gastado = total_gastado + NEW.precio_total,
            ultima_estancia = NEW.fecha_entrada,
            nivel = CASE 
                WHEN total_gastado >= 10000 THEN 'platinum'
                WHEN total_gastado >= 5000 THEN 'gold'
                WHEN total_gastado >= 2000 THEN 'silver'
                ELSE 'bronze'
            END;
    END IF;
END//
DELIMITER ;
