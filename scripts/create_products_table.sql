-- Tabla de productos para el servicio de habitaciones
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    categoria ENUM('comida', 'bebida', 'snack', 'higiene', 'otros') NOT NULL DEFAULT 'otros',
    precio DECIMAL(10,2) NOT NULL,
    imagen_url VARCHAR(500),
    stock INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Tabla para pedidos de productos
CREATE TABLE pedidos_productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habitacion_id INT NOT NULL,
    cliente_id INT,
    usuario_id INT NOT NULL,
    estado ENUM('pendiente', 'en_preparacion', 'entregado', 'cancelado') DEFAULT 'pendiente',
    subtotal DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    notas TEXT,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_entrega TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de detalles de pedidos
CREATE TABLE pedido_productos_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos_productos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- Insertar productos de ejemplo
INSERT INTO productos (nombre, descripcion, categoria, precio, imagen_url, stock) VALUES
('Hamburguesa Clásica', 'Hamburguesa de carne con lechuga, tomate, cebolla y salsa especial', 'comida', 25000.00, 'assets/images/products/hamburguesa.jpg', 50),
('Pizza Personal', 'Pizza pepperoni tamaño personal', 'comida', 22000.00, 'assets/images/products/pizza.jpg', 30),
('Ensalada César', 'Ensalada fresca con pollo, crutones y aderezo césar', 'comida', 18000.00, 'assets/images/products/ensalada.jpg', 25),
('Agua Mineral 500ml', 'Agua mineral natural', 'bebida', 3000.00, 'assets/images/products/agua.jpg', 100),
('Refresco Cola 600ml', 'Bebida gaseosa cola', 'bebida', 5000.00, 'assets/images/products/cola.jpg', 80),
('Cerveza Nacional 330ml', 'Cerveza nacional botella', 'bebida', 8000.00, 'assets/images/products/cerveza.jpg', 60),
('Papas Fritas', 'Porción de papas fritas con ketchup', 'snack', 12000.00, 'assets/images/products/papas.jpg', 40),
('Chocolate Bar', 'Barra de chocolate con leche', 'snack', 7000.00, 'assets/images/products/chocolate.jpg', 70),
('Jabón de Manos', 'Jabón líquido antibacterial', 'higiene', 15000.00, 'assets/images/products/jabon.jpg', 50),
('Toallas Papel', 'Paquete de toallas de papel', 'higiene', 8000.00, 'assets/images/products/toallas.jpg', 40);
