-- Crear tabla de eventos
CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    tipo_evento VARCHAR(100) NOT NULL,
    capacidad_maxima INT NOT NULL,
    precio_por_persona DECIMAL(10,2) NOT NULL,
    precio_total DECIMAL(10,2) NOT NULL,
    fecha_evento DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    imagen_url VARCHAR(500),
    estado ENUM('disponible', 'reservado', 'cancelado', 'completado') DEFAULT 'disponible',
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Crear tabla de reservas de eventos
CREATE TABLE IF NOT EXISTS reservas_eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    cliente_id INT NOT NULL,
    fecha_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cantidad_personas INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    precio_total DECIMAL(10,2) NOT NULL,
    estado ENUM('confirmada', 'pendiente', 'cancelada') DEFAULT 'pendiente',
    metodo_pago VARCHAR(50),
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- Insertar algunos eventos de ejemplo
INSERT INTO eventos (nombre, descripcion, tipo_evento, capacidad_maxima, precio_por_persona, precio_total, fecha_evento, hora_inicio, hora_fin, imagen_url) VALUES
('Boda Elegante', 'Paquete completo para bodas condecoración y catering', 'Boda', 150, 150.00, 22500.00, '2024-02-14', '18:00:00', '23:00:00', 'assets/images/events/wedding.jpg'),
('Cumpleaños Infantil', 'Fiesta de cumpleaños con animadores y juegos', 'Cumpleaños', 50, 25.00, 1250.00, '2024-01-20', '15:00:00', '18:00:00', 'assets/images/events/birthday.jpg'),
('Reunión Corporativa', 'Evento empresarial con equipo audiovisual completo', 'Corporativo', 100, 75.00, 7500.00, '2024-03-10', '09:00:00', '14:00:00', 'assets/images/events/corporate.jpg'),
('Conferencia Tech', 'Conferencia tecnológica con ponentes internacionales', 'Conferencia', 200, 120.00, 24000.00, '2024-04-15', '08:00:00', '18:00:00', 'assets/images/events/conference.jpg');
