-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 19-11-2025 a las 16:25:54
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `hotel_management_system`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `documento` varchar(50) NOT NULL,
  `tipo_documento` enum('DNI','Pasaporte','Cedula') DEFAULT 'DNI',
  `direccion` text DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT 'Colombia',
  `fecha_nacimiento` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `apellido`, `email`, `telefono`, `documento`, `tipo_documento`, `direccion`, `ciudad`, `pais`, `fecha_nacimiento`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Jhuliet Anghelica', 'Tibasosa', 'JhulietTibasosa30@gmail.com', '+573224125100', '1010150914', 'Cedula', 'Calle 123 #45-67', 'Bogotá', 'Colombia', '2003-09-07', '2025-11-18 15:24:33', '2025-11-18 21:16:25', NULL),
(2, 'Juan', 'Pérez', 'juan.perez@email.com', '3107654321', '0987654321', 'Cedula', 'Carrera 45 #12-34', 'Medellín', 'Colombia', '1985-08-20', '2025-11-18 15:24:33', '2025-11-18 21:16:36', NULL),
(3, 'Ana', 'López', 'ana.lopez@email.com', '3159876543', '153051152', 'Cedula', 'Avenida 68 #89-01', 'Cali', 'Colombia', '1992-12-10', '2025-11-18 15:24:33', '2025-11-18 20:42:42', NULL),
(4, 'Pedro', 'Martínez', 'pedro.martinez@email.com', '3201239876', '5544332211', 'Pasaporte', 'Diagonal 27 #34-56', 'Cartagena', 'Colombia', '1988-03-25', '2025-11-18 15:24:33', '2025-11-18 15:24:33', NULL),
(5, 'Camila', 'Caceres', 'Cami_caceres30@gmail.com', '3218381092', '105546588', 'Cedula', 'Calle 22#16-34', 'Cùcuta', 'Colombia', '2003-10-20', '2025-11-18 20:35:54', '2025-11-18 21:16:14', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitaciones`
--

CREATE TABLE `habitaciones` (
  `id` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `tipo` enum('simple','doble','suite','presidencial') NOT NULL,
  `precio_noche` decimal(10,2) NOT NULL,
  `capacidad` int(11) NOT NULL DEFAULT 1,
  `estado` enum('disponible','ocupada','mantenimiento','limpieza') DEFAULT 'disponible',
  `piso` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `amenidades` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenidades`)),
  `imagen_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `habitaciones`
--

INSERT INTO `habitaciones` (`id`, `numero`, `tipo`, `precio_noche`, `capacidad`, `estado`, `piso`, `descripcion`, `amenidades`, `imagen_url`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '101', 'simple', 150000.00, 1, 'ocupada', 1, 'Habitación simple con cama individual, baño privado y TV', '[\"WiFi\", \"TV\", \"Aire acondicionado\"]', NULL, '2025-11-18 15:24:33', '2025-11-18 21:42:32', NULL),
(2, '102', 'doble', 250000.00, 2, 'disponible', 1, 'Habitación doble con dos camas, baño privado, TV y minibar', '[\"WiFi\", \"TV\", \"Minibar\", \"Aire acondicionado\"]', NULL, '2025-11-18 15:24:33', '2025-11-18 15:24:33', NULL),
(3, '201', 'suite', 450000.00, 3, 'ocupada', 2, 'Suite amplia con sala, dormitorio, baño de lujo y balcón', '[\"WiFi\", \"TV\", \"Minibar\", \"Jacuzzi\", \"Balcón\"]', NULL, '2025-11-18 15:24:33', '2025-11-18 21:42:57', NULL),
(4, '301', 'presidencial', 800000.00, 6, 'disponible', 3, 'Suite presidencial con 2 habitaciones, sala, comedor, cocina y terraza', '[\"WiFi\", \"TV\", \"Minibar\", \"Cocina\", \"Terraza\", \"Jacuzzi\"]', NULL, '2025-11-18 15:24:33', '2025-11-18 15:24:33', NULL),
(5, '103', 'simple', 150000.00, 1, 'disponible', 1, 'Habitación simple económica', '[\"WiFi\", \"TV\"]', NULL, '2025-11-18 15:24:33', '2025-11-18 15:24:33', NULL),
(6, '104', 'doble', 250000.00, 2, 'mantenimiento', 1, 'Habitación doble estándar', '[\"WiFi\", \"TV\", \"Aire acondicionado\"]', NULL, '2025-11-18 15:24:33', '2025-11-18 15:24:33', NULL),
(7, '202', 'suite', 250000.00, 4, 'disponible', 2, 'Suite elegante con cama king, sala pequeña, minibar, jacuzzi y vista a la ciudad.', NULL, NULL, '2025-11-18 21:44:58', '2025-11-18 21:44:58', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `reserva_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo` enum('efectivo','tarjeta','transferencia') NOT NULL,
  `estado` enum('pendiente','completado','rechazado') DEFAULT 'completado',
  `referencia` varchar(100) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `reserva_id`, `monto`, `metodo`, `estado`, `referencia`, `notas`, `fecha_pago`, `created_at`) VALUES
(1, 1, 900000.00, 'tarjeta', 'completado', 'TRX-2025-001', NULL, '2025-11-18 15:24:33', '2025-11-18 15:24:33'),
(2, 2, 500000.00, 'efectivo', 'completado', 'EFE-2025-002', NULL, '2025-11-18 15:24:33', '2025-11-18 15:24:33'),
(3, 3, 300000.00, 'transferencia', 'completado', 'TRANS-2025-003', NULL, '2025-11-18 15:24:33', '2025-11-18 15:24:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `habitacion_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_entrada` date NOT NULL,
  `fecha_salida` date NOT NULL,
  `num_huespedes` int(11) DEFAULT 1,
  `estado` enum('pendiente','confirmada','checkin','checkout','cancelada') DEFAULT 'pendiente',
  `precio_noche` decimal(10,2) NOT NULL,
  `num_noches` int(11) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `metodo_pago` enum('efectivo','tarjeta','transferencia') DEFAULT 'efectivo',
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `cliente_id`, `habitacion_id`, `usuario_id`, `fecha_entrada`, `fecha_salida`, `num_huespedes`, `estado`, `precio_noche`, `num_noches`, `precio_total`, `metodo_pago`, `notas`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 3, 2, '2025-01-13', '2025-01-15', 2, 'confirmada', 450000.00, 2, 900000.00, 'tarjeta', NULL, '2025-11-18 15:24:33', '2025-11-19 15:05:28', '2025-11-19 15:05:28'),
(2, 2, 2, 2, '2025-01-14', '2025-01-16', 2, 'confirmada', 250000.00, 2, 500000.00, 'efectivo', NULL, '2025-11-18 15:24:33', '2025-11-18 15:24:33', NULL),
(3, 3, 5, 1, '2025-01-12', '2025-01-14', 1, 'confirmada', 150000.00, 2, 300000.00, 'transferencia', NULL, '2025-11-18 15:24:33', '2025-11-19 15:16:03', NULL),
(4, 4, 4, 1, '2025-01-15', '2025-01-18', 4, 'pendiente', 800000.00, 3, 2400000.00, 'tarjeta', NULL, '2025-11-18 15:24:33', '2025-11-19 15:16:30', '2025-11-19 15:16:30'),
(5, 1, 2, NULL, '2025-12-01', '2025-12-03', 1, 'confirmada', 250000.00, 2, 500000.00, 'tarjeta', NULL, '2025-11-19 14:33:37', '2025-11-19 14:59:33', NULL),
(6, 5, 5, NULL, '2025-11-25', '2025-12-12', 1, 'confirmada', 150000.00, 17, 2550000.00, 'efectivo', NULL, '2025-11-19 14:36:40', '2025-11-19 14:36:40', NULL),
(7, 2, 7, NULL, '2025-11-28', '2025-12-20', 1, 'confirmada', 250000.00, 22, 5500000.00, 'transferencia', NULL, '2025-11-19 14:47:24', '2025-11-19 14:47:24', NULL),
(8, 4, 2, NULL, '2025-12-12', '2025-12-25', 1, 'confirmada', 250000.00, 13, 3250000.00, 'transferencia', NULL, '2025-11-19 14:54:24', '2025-11-19 14:54:24', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','recepcionista','gerente') DEFAULT 'recepcionista',
  `telefono` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `password`, `rol`, `telefono`, `activo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Marcos', 'Salazar', 'admin@hotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+573152123745', 1, '2025-11-18 15:24:33', '2025-11-18 20:14:09', NULL),
(2, 'Maria', 'Recepcionista', 'recepcion@hotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recepcionista', '3007654321', 1, '2025-11-18 15:24:33', '2025-11-18 15:24:33', NULL),
(3, 'Juan', 'Gerente', 'gerente@hotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gerente', '3009876543', 1, '2025-11-18 15:24:33', '2025-11-18 15:24:33', NULL),
(4, 'Jhuliet Anghelica', 'Tibasosa Suescun', 'JhulietTibsosa30@gmail.com', '$2y$10$745wuvbaYE2K3/erCt0zXueQQT50MdzwCE8CEFomy1jcXPQxglMGe', 'recepcionista', '+573224125100', 1, '2025-11-18 16:54:59', '2025-11-18 20:13:30', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `documento` (`documento`),
  ADD KEY `idx_documento` (`documento`),
  ADD KEY `idx_email` (`email`);

--
-- Indices de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`),
  ADD KEY `idx_numero` (`numero`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reserva` (`reserva_id`),
  ADD KEY `idx_fecha` (`fecha_pago`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_habitacion` (`habitacion_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fechas` (`fecha_entrada`,`fecha_salida`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_rol` (`rol`),
  ADD KEY `idx_activo` (`activo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
