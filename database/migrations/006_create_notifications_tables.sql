-- Notifications System Tables
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('reserva_creada', 'reserva_cancelada', 'pago_recibido', 'checkin_hoy', 'checkout_hoy', 'habitacion_lista', 'mantenimiento_urgente', 'tarea_limpieza', 'review_nueva', 'stock_bajo', 'personalizado') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    icono VARCHAR(50) DEFAULT 'info',
    color VARCHAR(20) DEFAULT 'blue',
    link VARCHAR(255),
    datos_adicionales JSON,
    para_todos TINYINT(1) DEFAULT 0,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_tipo (tipo),
    INDEX idx_created_at (created_at),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- User Notifications (Many-to-Many relationship)
CREATE TABLE IF NOT EXISTS user_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    usuario_id INT NOT NULL,
    leida TINYINT(1) DEFAULT 0,
    fecha_leida TIMESTAMP NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_notification_user (notification_id, usuario_id),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_leida (leida),
    INDEX idx_fecha_envio (fecha_envio),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Notification Preferences
CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL UNIQUE,
    email_notifications TINYINT(1) DEFAULT 1,
    sms_notifications TINYINT(1) DEFAULT 0,
    push_notifications TINYINT(1) DEFAULT 1,
    tipos_notificaciones JSON,
    hotel_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES config_hotel(id) ON DELETE RESTRICT,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Notification Templates
CREATE TABLE IF NOT EXISTS notification_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL UNIQUE,
    titulo VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    icono VARCHAR(50) DEFAULT 'info',
    color VARCHAR(20) DEFAULT 'blue',
    variables JSON,
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

-- Insert default notification templates
INSERT INTO notification_templates (tipo, titulo, mensaje, icono, color, variables) VALUES
('reserva_creada', 'Nueva Reserva', 'Se ha creado una nueva reserva para la habitación {habitacion_numero} del {fecha_entrada} al {fecha_salida}. Cliente: {cliente_nombre}', 'calendar', 'green', '["habitacion_numero", "fecha_entrada", "fecha_salida", "cliente_nombre"]'),
('pago_recibido', 'Pago Recibido', 'Se ha recibido un pago de ${monto} por la reserva #{reserva_id}', 'credit-card', 'blue', '["monto", "reserva_id"]'),
('checkin_hoy', 'Check-in Hoy', 'Hay {cantidad} check-ins programados para hoy', 'log-in', 'orange', '["cantidad"]'),
('checkout_hoy', 'Checkout Hoy', 'Hay {cantidad} check-outs programados para hoy', 'log-out', 'purple', '["cantidad"]'),
('habitacion_lista', 'Habitación Lista', 'La habitación {habitacion_numero} está lista para el próximo huésped', 'check-circle', 'green', '["habitacion_numero"]'),
('mantenimiento_urgente', 'Mantenimiento Urgente', 'Se requiere mantenimiento urgente en la habitación {habitacion_numero}: {descripcion}', 'alert-triangle', 'red', '["habitacion_numero", "descripcion"]'),
('tarea_limpieza', 'Tarea de Limpieza', 'Nueva tarea de limpieza asignada para la habitación {habitacion_numero}', 'broom', 'blue', '["habitacion_numero"]'),
('review_nueva', 'Nueva Reseña', 'Se ha recibido una nueva reseña de {rating} estrellas para la habitación {habitacion_numero}', 'star', 'yellow', '["rating", "habitacion_numero"]'),
('stock_bajo', 'Stock Bajo', 'El producto {producto_nombre} tiene stock bajo ({stock_actual} unidades)', 'package', 'orange', '["producto_nombre", "stock_actual"]');

-- Create triggers for automatic notifications
DELIMITER //
CREATE TRIGGER notify_new_reservation
AFTER INSERT ON reservas
FOR EACH ROW
BEGIN
    INSERT INTO notifications (tipo, titulo, mensaje, datos_adicionales)
    VALUES ('reserva_creada', 'Nueva Reserva Creada', 
            CONCAT('Reserva #', NEW.id, ' creada para habitación ', NEW.habitacion_id),
            JSON_OBJECT('reserva_id', NEW.id, 'habitacion_id', NEW.habitacion_id, 'fecha_entrada', NEW.fecha_entrada));
END//

CREATE TRIGGER notify_payment_received
AFTER UPDATE ON pagos
FOR EACH ROW
BEGIN
    IF NEW.estado = 'pagado' AND OLD.estado != 'pagado' THEN
        INSERT INTO notifications (tipo, titulo, mensaje, datos_adicionales)
        VALUES ('pago_recibido', 'Pago Recibido', 
                CONCAT('Pago recibido por reserva #', NEW.reserva_id),
                JSON_OBJECT('reserva_id', NEW.reserva_id, 'monto', NEW.monto));
    END IF;
END//
DELIMITER ;
