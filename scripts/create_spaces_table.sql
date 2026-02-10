-- Crear tabla de espacios de eventos
CREATE TABLE IF NOT EXISTS espacios_eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    tipo_espacio VARCHAR(100) NOT NULL,
    capacidad_maxima INT NOT NULL,
    precio_hora DECIMAL(10,2) NOT NULL,
    precio_completo DECIMAL(10,2) NOT NULL,
    ubicacion VARCHAR(255),
    caracteristicas TEXT,
    imagen_url VARCHAR(500),
    estado ENUM('disponible', 'ocupado', 'mantenimiento') DEFAULT 'disponible',
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Insertar algunos espacios de ejemplo
INSERT INTO espacios_eventos (nombre, descripcion, tipo_espacio, capacidad_maxima, precio_hora, precio_completo, ubicacion, caracteristicas, imagen_url) VALUES
('Salón Principal', 'Amplio salón para eventos grandes con decoración elegante', 'Salón', 200, 150.00, 1200.00, 'Planta Baja', 'Aire acondicionado, sistema de sonido, iluminación profesional, baños privados', 'assets/images/spaces/main_hall.jpg'),
('Jardín Tropical', 'Espacio al aire libre con jardín y fuente central', 'Jardín', 150, 100.00, 800.00, 'Exterior', 'Jardín tropical, fuente, iluminación ambiental, área de barbacoa', 'assets/images/spaces/tropical_garden.jpg'),
('Terraza Panorámica', 'Terraza con vista a la ciudad, ideal para cócteles', 'Terraza', 80, 120.00, 960.00, 'Azotea', 'Vista panorámica, bar, iluminación LED, sombrillas', 'assets/images/spaces/terrace.jpg'),
('Sala de Conferencias', 'Espacio corporativo con equipo audiovisual completo', 'Sala', 50, 80.00, 640.00, 'Piso 2', 'Proyector, pantalla, sistema de videoconferencia, WiFi', 'assets/images/spaces/conference_room.jpg'),
('Salón de Fiestas Infantiles', 'Espacio colorido y seguro para eventos infantiles', 'Salón', 40, 60.00, 480.00, 'Planta Baja', 'Juegos infantiles, área de regalos, seguridad para niños', 'assets/images/spaces/kids_party.jpg');
