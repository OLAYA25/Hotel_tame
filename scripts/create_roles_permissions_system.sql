-- =============================================
-- Sistema de Roles y Permisos Dinámicos
-- =============================================

-- Tabla de roles (dinámica)
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    nivel_acceso INT DEFAULT 1, -- 1=básico, 5=intermedio, 10=avanzado, 100=admin
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Tabla de módulos del sistema
CREATE TABLE IF NOT EXISTS modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    icono VARCHAR(50), -- clase de FontAwesome
    ruta VARCHAR(100), -- ruta del archivo PHP
    orden INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de permisos
CREATE TABLE IF NOT EXISTS permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modulo_id INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    clave VARCHAR(50) NOT NULL UNIQUE, -- ej: dashboard_ver, habitaciones_crear
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
);

-- Tabla pivote: roles_permisos
CREATE TABLE IF NOT EXISTS roles_permisos (
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (rol_id, permiso_id),
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
);

-- Tabla pivote: usuarios_roles (para múltiples roles por usuario)
CREATE TABLE IF NOT EXISTS usuarios_roles (
    usuario_id INT NOT NULL,
    rol_id INT NOT NULL,
    asignado_por INT, -- usuario que asignó el rol
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, rol_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (asignado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- =============================================
-- Módulos Contables
-- =============================================

-- Cuentas contables
CREATE TABLE IF NOT EXISTS cuentas_contables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('activo', 'pasivo', 'patrimonio', 'ingreso', 'egreso') NOT NULL,
    nivel INT DEFAULT 1, -- nivel de jerarquía
    cuenta_padre_id INT NULL,
    descripcion TEXT,
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cuenta_padre_id) REFERENCES cuentas_contables(id) ON DELETE SET NULL
);

-- Transacciones contables
CREATE TABLE IF NOT EXISTS transacciones_contables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_comprobante VARCHAR(50) NOT NULL UNIQUE,
    fecha DATE NOT NULL,
    descripcion TEXT NOT NULL,
    tipo_transaccion ENUM('ingreso', 'egreso', 'traspaso') NOT NULL,
    monto_total DECIMAL(15,2) NOT NULL,
    usuario_id INT NOT NULL,
    referencia_tipo ENUM('reserva', 'pedido', 'evento', 'gasto', 'ajuste') NULL,
    referencia_id INT NULL,
    estado ENUM('borrador', 'confirmada', 'anulada') DEFAULT 'borrador',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- Detalles de transacciones (partida doble)
CREATE TABLE IF NOT EXISTS transaccion_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaccion_id INT NOT NULL,
    cuenta_id INT NOT NULL,
    tipo_movimiento ENUM('debe', 'haber') NOT NULL,
    monto DECIMAL(15,2) NOT NULL,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaccion_id) REFERENCES transacciones_contables(id) ON DELETE CASCADE,
    FOREIGN KEY (cuenta_id) REFERENCES cuentas_contables(id) ON DELETE RESTRICT
);

-- =============================================
-- Sistema de Turnos
-- =============================================

-- Tipos de turnos
CREATE TABLE IF NOT EXISTS tipos_turno (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    descripcion TEXT,
    color VARCHAR(7) DEFAULT '#007bff', -- color para calendario
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Turnos asignados
CREATE TABLE IF NOT EXISTS turnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_turno_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora_entrada_real TIME NULL,
    hora_salida_real TIME NULL,
    estado ENUM('programado', 'en_curso', 'completado', 'ausente') DEFAULT 'programado',
    notas TEXT,
    supervisor_id INT NULL, -- usuario que supervisa el turno
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_turno_id) REFERENCES tipos_turno(id) ON DELETE RESTRICT,
    FOREIGN KEY (supervisor_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- =============================================
-- Insertar datos iniciales
-- =============================================

-- Insertar módulos del sistema
INSERT INTO modulos (nombre, descripcion, icono, ruta, orden) VALUES
('dashboard', 'Panel principal', 'fas fa-home', 'index.php', 1),
('habitaciones', 'Gestión de habitaciones', 'fas fa-bed', 'habitaciones.php', 2),
('usuarios', 'Gestión de usuarios', 'fas fa-users', 'usuarios.php', 3),
('clientes', 'Gestión de clientes', 'fas fa-user-tie', 'clientes.php', 4),
('productos', 'Gestión de productos', 'fas fa-box', 'productos.php', 5),
('pedidos', 'Gestión de pedidos', 'fas fa-shopping-cart', 'pedidos_productos.php', 6),
('reservas', 'Gestión de reservas', 'fas fa-calendar-check', 'reservas.php', 7),
('eventos', 'Gestión de eventos', 'fas fa-calendar-alt', 'eventos.php', 8),
('espacios_eventos', 'Espacios para eventos', 'fas fa-door-open', 'espacios_eventos.php', 9),
('reservas_eventos', 'Reservas de eventos', 'fas fa-calendar-plus', 'reservas_eventos.php', 10),
('contabilidad', 'Módulo contable', 'fas fa-calculator', 'contabilidad.php', 11),
('reportes', 'Reportes financieros', 'fas fa-chart-line', 'reportes.php', 12),
('turnos', 'Gestión de turnos', 'fas fa-clock', 'turnos.php', 13);

-- Insertar permisos básicos para cada módulo
INSERT INTO permisos (modulo_id, nombre, descripcion, clave) VALUES
-- Dashboard
(1, 'Ver dashboard', 'Permite ver el panel principal', 'dashboard_ver'),

-- Habitaciones
(2, 'Ver habitaciones', 'Permite ver la lista de habitaciones', 'habitaciones_ver'),
(2, 'Crear habitaciones', 'Permite crear nuevas habitaciones', 'habitaciones_crear'),
(2, 'Editar habitaciones', 'Permite editar habitaciones existentes', 'habitaciones_editar'),
(2, 'Eliminar habitaciones', 'Permite eliminar habitaciones', 'habitaciones_eliminar'),

-- Usuarios
(3, 'Ver usuarios', 'Permite ver la lista de usuarios', 'usuarios_ver'),
(3, 'Crear usuarios', 'Permite crear nuevos usuarios', 'usuarios_crear'),
(3, 'Editar usuarios', 'Permite editar usuarios existentes', 'usuarios_editar'),
(3, 'Eliminar usuarios', 'Permite eliminar usuarios', 'usuarios_eliminar'),
(3, 'Gestionar roles', 'Permite asignar y gestionar roles', 'usuarios_gestionar_roles'),

-- Clientes
(4, 'Ver clientes', 'Permite ver la lista de clientes', 'clientes_ver'),
(4, 'Crear clientes', 'Permite crear nuevos clientes', 'clientes_crear'),
(4, 'Editar clientes', 'Permite editar clientes existentes', 'clientes_editar'),
(4, 'Eliminar clientes', 'Permite eliminar clientes', 'clientes_eliminar'),

-- Productos
(5, 'Ver productos', 'Permite ver la lista de productos', 'productos_ver'),
(5, 'Crear productos', 'Permite crear nuevos productos', 'productos_crear'),
(5, 'Editar productos', 'Permite editar productos existentes', 'productos_editar'),
(5, 'Eliminar productos', 'Permite eliminar productos', 'productos_eliminar'),

-- Pedidos
(6, 'Ver pedidos', 'Permite ver la lista de pedidos', 'pedidos_ver'),
(6, 'Crear pedidos', 'Permite crear nuevos pedidos', 'pedidos_crear'),
(6, 'Editar pedidos', 'Permite editar pedidos existentes', 'pedidos_editar'),
(6, 'Eliminar pedidos', 'Permite eliminar pedidos', 'pedidos_eliminar'),
(6, 'Cambiar estado pedidos', 'Permite cambiar el estado de los pedidos', 'pedidos_cambiar_estado'),

-- Reservas
(7, 'Ver reservas', 'Permite ver la lista de reservas', 'reservas_ver'),
(7, 'Crear reservas', 'Permite crear nuevas reservas', 'reservas_crear'),
(7, 'Editar reservas', 'Permite editar reservas existentes', 'reservas_editar'),
(7, 'Eliminar reservas', 'Permite eliminar reservas', 'reservas_eliminar'),
(7, 'Cambiar estado reservas', 'Permite cambiar el estado de las reservas', 'reservas_cambiar_estado'),

-- Eventos
(8, 'Ver eventos', 'Permite ver la lista de eventos', 'eventos_ver'),
(8, 'Crear eventos', 'Permite crear nuevos eventos', 'eventos_crear'),
(8, 'Editar eventos', 'Permite editar eventos existentes', 'eventos_editar'),
(8, 'Eliminar eventos', 'Permite eliminar eventos', 'eventos_eliminar'),

-- Espacios de eventos
(9, 'Ver espacios', 'Permite ver la lista de espacios', 'espacios_ver'),
(9, 'Crear espacios', 'Permite crear nuevos espacios', 'espacios_crear'),
(9, 'Editar espacios', 'Permite editar espacios existentes', 'espacios_editar'),
(9, 'Eliminar espacios', 'Permite eliminar espacios', 'espacios_eliminar'),

-- Reservas de eventos
(10, 'Ver reservas eventos', 'Permite ver reservas de eventos', 'reservas_eventos_ver'),
(10, 'Crear reservas eventos', 'Permite crear reservas de eventos', 'reservas_eventos_crear'),
(10, 'Editar reservas eventos', 'Permite editar reservas de eventos', 'reservas_eventos_editar'),
(10, 'Eliminar reservas eventos', 'Permite eliminar reservas de eventos', 'reservas_eventos_eliminar'),

-- Contabilidad
(11, 'Ver contabilidad', 'Permite ver el módulo contable', 'contabilidad_ver'),
(11, 'Crear transacciones', 'Permite crear transacciones contables', 'contabilidad_crear'),
(11, 'Editar transacciones', 'Permite editar transacciones', 'contabilidad_editar'),
(11, 'Eliminar transacciones', 'Permite eliminar transacciones', 'contabilidad_eliminar'),
(11, 'Confirmar transacciones', 'Permite confirmar transacciones', 'contabilidad_confirmar'),
(11, 'Ver reportes', 'Permite ver reportes financieros', 'contabilidad_reportes'),

-- Reportes
(12, 'Ver reportes', 'Permite ver reportes financieros', 'reportes_ver'),
(12, 'Exportar reportes', 'Permite exportar reportes', 'reportes_exportar'),

-- Turnos
(13, 'Ver turnos', 'Permite ver la gestión de turnos', 'turnos_ver'),
(13, 'Crear turnos', 'Permite crear turnos', 'turnos_crear'),
(13, 'Editar turnos', 'Permite editar turnos', 'turnos_editar'),
(13, 'Eliminar turnos', 'Permite eliminar turnos', 'turnos_eliminar'),
(13, 'Asignar turnos', 'Permite asignar turnos a usuarios', 'turnos_asignar'),
(13, 'Aprobar turnos', 'Permite aprobar turnos', 'turnos_aprobar');

-- Insertar roles básicos
INSERT INTO roles (nombre, descripcion, nivel_acceso) VALUES
('Administrador', 'Acceso completo al sistema', 100),
('Gerente', 'Acceso gerencial a la mayoría de módulos', 50),
('Contador', 'Acceso completo al área contable', 40),
('Auxiliar Contable', 'Acceso limitado al área contable', 30),
('Recepcionista', 'Gestión de recepción y reservas', 20),
('Limpieza', 'Gestión de limpieza y habitaciones', 10);

-- Insertar tipos de turnos
INSERT INTO tipos_turno (nombre, hora_inicio, hora_fin, descripcion, color) VALUES
('Mañana', '07:00:00', '15:00:00', 'Turno de mañana (7am - 3pm)', '#28a745'),
('Tarde', '15:00:00', '23:00:00', 'Turno de tarde (3pm - 11pm)', '#ffc107'),
('Noche', '23:00:00', '07:00:00', 'Turno de noche (11pm - 7am)', '#6f42c1'),
('Partido', '06:00:00', '14:00:00', 'Turno partido mañana (6am - 2pm)', '#17a2b8'),
('Partido Tarde', '14:00:00', '22:00:00', 'Turno partido tarde (2pm - 10pm)', '#fd7e14');

-- Insertar cuentas contables básicas
INSERT INTO cuentas_contables (codigo, nombre, tipo, nivel, descripcion) VALUES
-- Activos
('1.1.0', 'Caja y Bancos', 'activo', 2, 'Dinero en efectivo y cuentas bancarias'),
('1.1.1', 'Caja', 'activo', 3, 'Dinero en efectivo'),
('1.1.2', 'Cuenta Bancaria', 'activo', 3, 'Cuentas bancarias del hotel'),
('1.2.0', 'Cuentas por Cobrar', 'activo', 2, 'Dinero que los clientes nos deben'),
('1.2.1', 'Habitaciones por Cobrar', 'activo', 3, 'Ingresos de habitaciones pendientes de pago'),
('1.2.2', 'Servicios por Cobrar', 'activo', 3, 'Servicios varios pendientes de pago'),

-- Pasivos
('2.1.0', 'Cuentas por Pagar', 'pasivo', 2, 'Dinero que debemos a proveedores'),
('2.1.1', 'Proveedores', 'pasivo', 3, 'Deudas con proveedores'),
('2.1.2', 'Sueldos por Pagar', 'pasivo', 3, 'Sueldos pendientes de pago'),

-- Ingresos
('4.1.0', 'Ingresos de Habitaciones', 'ingreso', 2, 'Ingresos por alquiler de habitaciones'),
('4.1.1', 'Habitaciones Standard', 'ingreso', 3, 'Ingresos habitaciones standard'),
('4.1.2', 'Habitaciones Suite', 'ingreso', 3, 'Ingresos habitaciones suite'),
('4.2.0', 'Ingresos de Servicios', 'ingreso', 2, 'Ingresos por servicios adicionales'),
('4.2.1', 'Restaurant y Bar', 'ingreso', 3, 'Ingresos por alimentos y bebidas'),
('4.2.2', 'Eventos', 'ingreso', 3, 'Ingresos por organización de eventos'),

-- Egresos
('5.1.0', 'Costos de Operación', 'egreso', 2, 'Costos operativos del hotel'),
('5.1.1', 'Personal', 'egreso', 3, 'Sueldos y salarios'),
('5.1.2', 'Suministros', 'egreso', 3, 'Compras de suministros'),
('5.1.3', 'Servicios', 'egreso', 3, 'Contratación de servicios'),
('5.2.0', 'Gastos Administrativos', 'egreso', 2, 'Gastos de administración'),
('5.2.1', 'Oficina', 'egreso', 3, 'Gastos de oficina'),
('5.2.2', 'Marketing', 'egreso', 3, 'Gastos de marketing y publicidad');

-- Asignar permisos al rol Administrador (todos los permisos)
INSERT INTO roles_permisos (rol_id, permiso_id)
SELECT r.id, p.id 
FROM roles r, permisos p 
WHERE r.nombre = 'Administrador';
