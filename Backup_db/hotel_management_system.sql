-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 20-03-2026 a las 15:31:24
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
-- Estructura de tabla para la tabla `backups`
--

CREATE TABLE `backups` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `size` bigint(20) NOT NULL,
  `status` enum('pending','completed','failed','restoring') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `icono` varchar(50) DEFAULT 'fas fa-box',
  `color` varchar(20) DEFAULT '#007bff',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `icono`, `color`, `activo`, `created_at`) VALUES
(1, 'comida', 'fas fa-utensils', '#28a745', 1, '2026-03-13 20:22:14'),
(2, 'bebida', 'fas fa-coffee', '#17a2b8', 1, '2026-03-13 20:22:14'),
(3, 'snack', 'fas fa-cookie', '#ffc107', 1, '2026-03-13 20:22:14'),
(4, 'higiene', 'fas fa-soap', '#6f42c1', 1, '2026-03-13 20:22:14'),
(5, 'otros', 'fas fa-box', '#6c757d', 1, '2026-03-13 20:22:14'),
(6, 'Postres', 'fas fa-cookie', '#ffa200', 1, '2026-03-13 20:35:09'),
(7, 'Postresitos', 'fas fa-ice-cream', '#a7c3e2', 1, '2026-03-13 20:38:25'),
(8, 'Helado', 'fas fa-ice-cream', '#2373c7', 1, '2026-03-13 20:40:24'),
(9, 'Comida Marina', 'fas fa-fish', '#007bff', 1, '2026-03-13 20:47:19'),
(10, 'blog', 'fas fa-book', '#00ff33', 1, '2026-03-13 20:57:13');

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
(2, 'Juan', 'Pérez', 'juan.perez@email.com', '3107654321', '0987654321', 'Cedula', 'Carrera 45 #12-34', 'Medellín', 'CO', '1985-08-20', '2025-11-18 15:24:33', '2026-03-03 13:55:09', NULL),
(3, 'Ana', 'López', 'ana.lopez@email.com', '3159876543', '153051152', 'Cedula', 'Avenida 68 #89-01', 'Cali', 'VE', '1992-12-10', '2025-11-18 15:24:33', '2026-03-03 13:55:45', NULL),
(4, 'Pedro', 'Martínez', 'pedro.martinez@email.com', '3201239876', '5544332211', 'Pasaporte', 'Diagonal 27 #34-56', 'Cartagena', 'CO', '1988-03-25', '2025-11-18 15:24:33', '2026-03-03 13:56:38', NULL),
(5, 'Camila', 'Caceres', 'Cami_caceres30@gmail.com', '3218381092', '105546588', 'Cedula', 'Calle 22#16-34', 'Cùcuta', 'Colombia', '2003-10-20', '2025-11-18 20:35:54', '2025-11-18 21:16:14', NULL),
(6, 'AFDSF', 'ADSFADS', 'admin@hotel.com', '3203564489', '444444555', 'Cedula', 'DFD', 'TAME', 'Colombia', '2025-12-26', '2025-12-26 14:32:07', '2026-03-03 02:02:58', NULL),
(7, 'OLAYA', 'SHELKIER', 'cheolaya11@gmail.com', '3203564488', '111823782', 'Cedula', 'CALLE X', 'TAMEE', 'Colombia', '2025-12-26', '2025-12-26 22:56:05', '2026-03-03 02:02:58', NULL),
(8, 'pepito ', 'PEREZ', 'pepito@gmail.com', '23424234', '23423424234', 'DNI', 'fdgdfgdf', 'TAME', 'colombia', '2006-07-26', '2025-12-27 02:53:07', '2025-12-27 02:53:07', NULL),
(9, 'PEDRITO', 'PEREZ', 'pedrito@gmail.com', '3203564480', '111111111114', 'Cedula', 'kk', 'TAME', 'Colombia', '2026-01-08', '2025-12-29 22:55:34', '2026-03-03 02:02:58', NULL),
(10, 'eeeeeeeeeeee', 'ñññññññññññ', 'pedrito@gmail.com', '3124589774', '58456456415641', 'Cedula', 'ĺkl', 'TAME', 'Colombia', '2025-12-31', '2025-12-29 22:59:33', '2026-03-03 02:02:58', NULL),
(11, 'Juanito', 'Rodriguez', 'juanito@gmail.com', '312345867', '10985672', 'Cedula', 'Calle 16', 'Tame', 'Colombia', '2025-01-01', '2026-01-14 16:29:05', '2026-01-23 22:38:22', NULL),
(12, 'juan', 'cardenas', 'jcardenas.@gmail.com', '320234234234', '342342342', 'Cedula', '123123', 'TAME', 'colombia', '1979-01-28', '2026-01-28 22:06:15', '2026-01-28 22:06:15', NULL),
(13, 'pepito', 'PEREZ', 'pei95@hotel.com', '31023454345', '2342342', 'Cedula', '234324', 'TAME', 'COLOMBIA', '2005-09-28', '2026-01-28 22:49:23', '2026-01-28 22:53:55', '2026-01-28 22:53:55'),
(14, 'TESEO', 'PASEO', 'PASEO@hotel.com', '2434234234', '1243214234', 'DNI', 'WREFSEDF', 'TAME', 'COLOMBIA', '1985-12-10', '2026-01-29 00:18:10', '2026-03-03 02:21:28', NULL),
(15, 'dell', 'APP', 'DELL@hotel.com', '123123123', '43324234', 'Cedula', 'DGSFGFDG', 'TAME', 'Colombia', '1990-02-02', '2026-02-11 22:30:54', '2026-03-03 02:02:58', NULL),
(17, 'pipx', 'pithon', 'pipx@hotel.com', '124214234', '143234234', NULL, '23423542345', 'TAME', 'colombia', NULL, '2026-02-11 22:35:44', '2026-02-11 22:35:44', NULL),
(20, 'PEDRO', 'PERRITO', 'pedroperrito@gmail.com', '31456788987', '1117628828', 'Cedula', 'direcciòn de pedro perrito', 'Tame', 'CO', '1994-11-11', '2026-03-13 16:41:27', '2026-03-13 16:41:27', NULL),
(21, 'CARLITOS', 'NIÑO', 'carlitos@gmail.com', '31436789098', '1050462222', NULL, 'adfasdfafa', 'Tame', '', NULL, '2026-03-13 16:54:06', '2026-03-13 16:54:06', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas_contables`
--

CREATE TABLE `cuentas_contables` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('activo','pasivo','patrimonio','ingreso','egreso') NOT NULL,
  `nivel` int(11) DEFAULT 1,
  `cuenta_padre_id` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `cuentas_contables`
--

INSERT INTO `cuentas_contables` (`id`, `codigo`, `nombre`, `tipo`, `nivel`, `cuenta_padre_id`, `descripcion`, `activa`, `created_at`, `updated_at`) VALUES
(1, '1.1.0', 'Caja y Bancos', 'activo', 2, NULL, 'Dinero en efectivo y cuentas bancarias', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(2, '1.1.1', 'Caja', 'activo', 3, NULL, 'Dinero en efectivo', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(3, '1.1.2', 'Cuenta Bancaria', 'activo', 3, NULL, 'Cuentas bancarias del hotel', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(4, '1.2.0', 'Cuentas por Cobrar', 'activo', 2, NULL, 'Dinero que los clientes nos deben', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(5, '1.2.1', 'Habitaciones por Cobrar', 'activo', 3, NULL, 'Ingresos de habitaciones pendientes de pago', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(6, '1.2.2', 'Servicios por Cobrar', 'activo', 3, NULL, 'Servicios varios pendientes de pago', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(7, '2.1.0', 'Cuentas por Pagar', 'pasivo', 2, NULL, 'Dinero que debemos a proveedores', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(8, '2.1.1', 'Proveedores', 'pasivo', 3, NULL, 'Deudas con proveedores', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(9, '2.1.2', 'Sueldos por Pagar', 'pasivo', 3, NULL, 'Sueldos pendientes de pago', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(10, '4.1.0', 'Ingresos de Habitaciones', 'ingreso', 2, NULL, 'Ingresos por alquiler de habitaciones', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(11, '4.1.1', 'Habitaciones Standard', 'ingreso', 3, NULL, 'Ingresos habitaciones standard', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(12, '4.1.2', 'Habitaciones Suite', 'ingreso', 3, NULL, 'Ingresos habitaciones suite', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(13, '4.2.0', 'Ingresos de Servicios', 'ingreso', 2, NULL, 'Ingresos por servicios adicionales', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(14, '4.2.1', 'Restaurant y Bar', 'ingreso', 3, NULL, 'Ingresos por alimentos y bebidas', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(15, '4.2.2', 'Eventos', 'ingreso', 3, NULL, 'Ingresos por organización de eventos', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(16, '5.1.0', 'Costos de Operación', 'egreso', 2, NULL, 'Costos operativos del hotel', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(17, '5.1.1', 'Personal', 'egreso', 3, NULL, 'Sueldos y salarios', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(18, '5.1.2', 'Suministros', 'egreso', 3, NULL, 'Compras de suministros', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(19, '5.1.3', 'Servicios', 'egreso', 3, NULL, 'Contratación de servicios', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(20, '5.2.0', 'Gastos Administrativos', 'egreso', 2, NULL, 'Gastos de administración', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(21, '5.2.1', 'Oficina', 'egreso', 3, NULL, 'Gastos de oficina', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(22, '5.2.2', 'Marketing', 'egreso', 3, NULL, 'Gastos de marketing y publicidad', 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `espacios_eventos`
--

CREATE TABLE `espacios_eventos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_espacio` varchar(100) NOT NULL,
  `capacidad_maxima` int(11) NOT NULL,
  `precio_hora` decimal(10,2) NOT NULL,
  `precio_completo` decimal(10,2) NOT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `caracteristicas` text DEFAULT NULL,
  `imagen_url` varchar(500) DEFAULT NULL,
  `estado` enum('disponible','ocupado','mantenimiento') DEFAULT 'disponible',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `espacios_eventos`
--

INSERT INTO `espacios_eventos` (`id`, `nombre`, `descripcion`, `tipo_espacio`, `capacidad_maxima`, `precio_hora`, `precio_completo`, `ubicacion`, `caracteristicas`, `imagen_url`, `estado`, `activo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Salón Principal', 'Amplio salón para eventos grandes con decoración elegante', 'Salón', 200, 150.00, 1200.00, 'Planta Baja', 'Aire acondicionado, sistema de sonido, iluminación profesional, baños privados', 'assets/images/spaces/main_hall.jpg', 'disponible', 1, '2025-12-27 02:23:04', '2025-12-27 02:23:04', NULL),
(2, 'Jardín Tropical', 'Espacio al aire libre con jardín y fuente central', 'Jardín', 150, 100.00, 800.00, 'Exterior', 'Jardín tropical, fuente, iluminación ambiental, área de barbacoa', 'assets/images/spaces/tropical_garden.jpg', 'disponible', 1, '2025-12-27 02:23:04', '2025-12-27 02:23:04', NULL),
(3, 'Terraza Panorámica', 'Terraza con vista a la ciudad, ideal para cócteles', 'Terraza', 80, 120.00, 960.00, 'Azotea', 'Vista panorámica, bar, iluminación LED, sombrillas', 'assets/images/spaces/terrace.jpg', 'disponible', 1, '2025-12-27 02:23:04', '2025-12-27 02:23:04', NULL),
(4, 'Sala de Conferencias', 'Espacio corporativo con equipo audiovisual completo', 'Sala', 50, 80.00, 640.00, 'Piso 2', 'Proyector, pantalla, sistema de videoconferencia, WiFi', 'assets/images/spaces/conference_room.jpg', 'disponible', 1, '2025-12-27 02:23:04', '2025-12-27 02:23:04', NULL),
(5, 'Salón de Fiestas Infantiles', 'Espacio colorido y seguro para eventos infantiles', 'Salón', 40, 60.00, 480.00, 'Planta Baja', 'Juegos infantiles, área de regalos, seguridad para niños', 'assets/images/spaces/kids_party.jpg', 'disponible', 1, '2025-12-27 02:23:04', '2025-12-27 02:23:04', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_evento` varchar(100) NOT NULL,
  `capacidad_maxima` int(11) NOT NULL,
  `precio_por_persona` decimal(10,2) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `fecha_evento` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `imagen_url` varchar(500) DEFAULT NULL,
  `estado` enum('disponible','reservado','cancelado','completado') DEFAULT 'disponible',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `nombre`, `descripcion`, `tipo_evento`, `capacidad_maxima`, `precio_por_persona`, `precio_total`, `fecha_evento`, `hora_inicio`, `hora_fin`, `imagen_url`, `estado`, `activo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Boda Elegante', 'Paquete completo para bodas condecoración y catering', 'Boda', 150, 150.00, 22500.00, '2024-02-14', '18:00:00', '23:00:00', 'assets/images/events/wedding.jpg', 'disponible', 1, '2025-12-27 02:17:00', '2025-12-27 02:17:00', NULL),
(2, 'Cumpleaños Infantil', 'Fiesta de cumpleaños con animadores y juegos', 'Cumpleaños', 50, 25.00, 1250.00, '2024-01-20', '15:00:00', '18:00:00', 'assets/images/events/birthday.jpg', 'disponible', 1, '2025-12-27 02:17:00', '2025-12-27 02:17:00', NULL),
(3, 'Reunión Corporativa', 'Evento empresarial con equipo audiovisual completo', 'Corporativo', 100, 75.00, 7500.00, '2024-03-10', '09:00:00', '14:00:00', 'assets/images/events/corporate.jpg', 'disponible', 1, '2025-12-27 02:17:00', '2025-12-27 02:17:00', NULL),
(4, 'Conferencia Tech', 'Conferencia tecnológica con ponentes internacionales', 'Conferencia', 200, 120.00, 24000.00, '2024-04-15', '08:00:00', '18:00:00', 'assets/images/events/conference.jpg', 'disponible', 1, '2025-12-27 02:17:00', '2025-12-27 02:17:00', NULL);

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
(1, '106', 'simple', 150000.00, 1, 'disponible', 1, 'Habitación simple con cama individual, baño privado y TV', '[\"WiFi\", \"TV\", \"Aire acondicionado\"]', '', '2025-11-18 15:24:33', '2026-03-18 02:41:41', NULL),
(2, '102', 'doble', 250000.00, 2, 'mantenimiento', 1, 'Habitación doble con dos camas, baño privado, TV y minibar', '[\"WiFi\", \"TV\", \"Minibar\", \"Aire acondicionado\"]', 'uploads/rooms/habitacion_1773838030_4911.jpg', '2025-11-18 15:24:33', '2026-03-18 12:47:10', NULL),
(3, '', 'simple', 0.00, 1, 'disponible', 1, '', '[\"WiFi\", \"TV\", \"Minibar\", \"Jacuzzi\", \"Balcón\"]', NULL, '2025-11-18 15:24:33', '2025-12-29 23:50:53', '2025-12-26 14:53:19'),
(4, '301', 'presidencial', 800000.00, 6, 'disponible', 3, 'Suite presidencial con 2 habitaciones, sala, comedor, cocina y terraza', '[\"WiFi\", \"TV\", \"Minibar\", \"Cocina\", \"Terraza\", \"Jacuzzi\"]', NULL, '2025-11-18 15:24:33', '2026-02-17 21:23:30', NULL),
(5, '103', 'doble', 150000.00, 3, 'disponible', 1, 'Habitación simple económica', '[\"WiFi\", \"TV\"]', NULL, '2025-11-18 15:24:33', '2026-03-18 02:33:28', NULL),
(6, '104', 'doble', 250000.00, 2, 'disponible', 1, 'Habitación disponible nuevamente', '[\"WiFi\", \"TV\", \"Aire acondicionado\"]', '', '2025-11-18 15:24:33', '2026-01-28 22:28:09', '2026-01-28 22:28:09'),
(7, '202', 'suite', 250000.00, 4, 'disponible', 2, 'Suite elegante con cama king, sala pequeña, minibar, jacuzzi y vista a la ciudad.', NULL, '', '2025-11-18 21:44:58', '2026-03-16 21:55:51', NULL),
(8, '101', 'simple', 50000.00, 2, 'disponible', 1, 'Habitación de prueba', NULL, NULL, '2025-12-26 19:12:16', '2026-01-29 22:43:04', '2026-01-29 22:42:51'),
(9, '105', 'simple', 100000.00, 2, 'disponible', 1, '', NULL, '', '2025-12-26 19:13:13', '2026-03-18 02:21:00', NULL),
(10, '110', 'doble', 50000.00, 4, 'disponible', 2, 'PRUEBAAAA', NULL, '', '2026-01-14 16:46:14', '2026-02-17 21:08:55', NULL),
(13, '107', 'doble', 250000.00, 2, 'disponible', 1, 'nueva', NULL, '', '2026-01-28 21:09:27', '2026-03-16 21:55:40', NULL),
(14, '305', 'simple', 50000.00, 1, 'disponible', 1, 'agua caliente ', NULL, '', '2026-01-28 21:32:24', '2026-01-28 22:27:11', '2026-01-28 22:27:11'),
(17, '404', 'simple', 50000.00, 1, 'disponible', 1, '', NULL, '', '2026-01-29 22:48:52', '2026-01-29 22:48:52', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hotel_config`
--

CREATE TABLE `hotel_config` (
  `id` int(11) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` varchar(20) DEFAULT 'string',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `hotel_config`
--

INSERT INTO `hotel_config` (`id`, `clave`, `valor`, `descripcion`, `tipo`, `created_at`, `updated_at`) VALUES
(1, 'nombre_hotel', 'Hotel Tame', 'Nombre del hotel', 'string', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(2, 'direccion', 'Calle Principal #123', 'Dirección del hotel', 'string', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(3, 'telefono', '+57 1 234 5678', 'Teléfono principal', 'string', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(4, 'email', 'info@hotel-tame.com', 'Email principal', 'string', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(5, 'moneda', 'COP', 'Moneda por defecto', 'string', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(6, 'idioma', 'es', 'Idioma por defecto', 'string', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(7, 'timezone', 'America/Bogota', 'Zona horaria', 'string', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(8, 'checkin_time', '15:00', 'Hora de check-in', 'time', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(9, 'checkout_time', '12:00', 'Hora de check-out', 'time', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(10, 'max_huespedes_habitacion', '4', 'Máximo de huéspedes por habitación', 'integer', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(11, 'politica_cancelacion', '24', 'Horas de política de cancelación', 'integer', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(12, 'deposito_porcentaje', '20', 'Porcentaje de depósito requerido', 'decimal', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(13, 'impuesto_hospedaje', '19', 'Porcentaje de impuesto de alojamiento', 'decimal', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(14, 'servicios_incluidos', 'WiFi, Desayuno, Aire Acondicionado', 'Servicios incluidos', 'text', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(15, 'logo_url', 'assets/images/logo-hotel.png', 'URL del logo', 'string', '2026-03-03 02:55:52', '2026-03-03 02:55:52'),
(16, 'activo', '1', 'Configuración activa', 'boolean', '2026-03-03 02:55:52', '2026-03-03 02:55:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metas_hotel`
--

CREATE TABLE `metas_hotel` (
  `id` int(11) NOT NULL,
  `mes` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `meta_revenue` decimal(12,2) DEFAULT NULL,
  `meta_ocupacion` decimal(5,2) DEFAULT NULL,
  `meta_reservas` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `metas_hotel`
--

INSERT INTO `metas_hotel` (`id`, `mes`, `anio`, `meta_revenue`, `meta_ocupacion`, `meta_reservas`, `created_at`, `updated_at`) VALUES
(1, 3, 2026, 50000000.00, 75.00, 150, '2026-03-03 02:55:52', '2026-03-03 02:55:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulos`
--

CREATE TABLE `modulos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(50) DEFAULT NULL,
  `ruta` varchar(100) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `modulos`
--

INSERT INTO `modulos` (`id`, `nombre`, `descripcion`, `icono`, `ruta`, `orden`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'dashboard', 'Panel principal', 'fas fa-home', 'index.php', 1, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(2, 'habitaciones', 'Gestión de habitaciones', 'fas fa-bed', 'habitaciones.php', 2, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(3, 'usuarios', 'Gestión de usuarios', 'fas fa-users', 'usuarios.php', 3, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(4, 'clientes', 'Gestión de clientes', 'fas fa-user-tie', 'clientes.php', 4, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(5, 'productos', 'Gestión de productos', 'fas fa-box', 'productos.php', 5, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(6, 'pedidos', 'Gestión de pedidos', 'fas fa-shopping-cart', 'pedidos_productos.php', 6, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(7, 'reservas', 'Gestión de reservas', 'fas fa-calendar-check', 'reservas.php', 7, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(8, 'eventos', 'Gestión de eventos', 'fas fa-calendar-alt', 'eventos.php', 8, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(9, 'espacios_eventos', 'Espacios para eventos', 'fas fa-door-open', 'espacios_eventos.php', 9, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(10, 'reservas_eventos', 'Reservas de eventos', 'fas fa-calendar-plus', 'reservas_eventos.php', 10, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(11, 'contabilidad', 'Módulo contable', 'fas fa-calculator', 'contabilidad.php', 11, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(12, 'reportes', 'Reportes financieros', 'fas fa-chart-line', 'reportes.php', 12, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27'),
(13, 'turnos', 'Gestión de turnos', 'fas fa-clock', 'turnos.php', 13, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27');

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
-- Estructura de tabla para la tabla `pedidos_productos`
--

CREATE TABLE `pedidos_productos` (
  `id` int(11) NOT NULL,
  `habitacion_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `estado` enum('pendiente','en_preparacion','entregado','cancelado') DEFAULT 'pendiente',
  `subtotal` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `notas` text DEFAULT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_entrega` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `pedidos_productos`
--

INSERT INTO `pedidos_productos` (`id`, `habitacion_id`, `cliente_id`, `usuario_id`, `estado`, `subtotal`, `total`, `notas`, `fecha_pedido`, `fecha_entrega`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 1, 'entregado', 25000.00, 25000.00, '', '2025-12-26 19:45:21', '2025-12-27 01:46:55', '2025-12-26 19:45:21', '2025-12-26 19:46:55', NULL),
(2, 1, NULL, 1, 'entregado', 30000.00, 30000.00, '', '2025-12-26 19:46:23', '2025-12-27 01:47:10', '2025-12-26 19:46:23', '2025-12-26 19:47:10', NULL),
(3, 1, NULL, 1, 'entregado', 15000.00, 15000.00, '', '2025-12-26 21:22:59', '2025-12-27 03:23:12', '2025-12-26 21:22:59', '2025-12-26 21:23:12', NULL),
(4, 1, NULL, 1, 'entregado', 33000.00, 33000.00, '', '2025-12-26 23:38:52', '2025-12-27 05:39:06', '2025-12-26 23:38:52', '2025-12-26 23:39:06', NULL),
(5, 1, NULL, 1, 'cancelado', 50000.00, 50000.00, '', '2025-12-27 00:22:59', NULL, '2025-12-27 00:22:59', '2025-12-27 16:28:02', NULL),
(6, 7, 6, 1, 'entregado', 6000.00, 6000.00, 'vvc cv bcv', '2026-01-20 16:15:05', '2026-02-13 05:24:36', '2026-01-20 16:15:05', '2026-02-12 23:24:36', NULL),
(7, 6, 1, 1, 'cancelado', 11000.00, 11000.00, 'wefwefrw', '2026-01-28 22:17:05', NULL, '2026-01-28 22:17:05', '2026-02-12 23:24:24', NULL),
(8, 5, 8, 1, 'cancelado', 27000.00, 27000.00, '', '2026-02-12 23:10:36', NULL, '2026-02-12 23:10:36', '2026-02-12 23:13:55', NULL),
(9, 5, 8, 1, 'entregado', 8000.00, 8000.00, '', '2026-02-12 23:26:10', '2026-02-13 05:26:19', '2026-02-12 23:26:10', '2026-02-12 23:26:19', NULL),
(10, 9, 14, 1, 'pendiente', 5000.00, 5000.00, '', '2026-02-12 23:26:52', NULL, '2026-02-12 23:26:52', '2026-02-12 23:26:52', NULL),
(11, 5, 17, 1, 'pendiente', 24000.00, 24000.00, '', '2026-02-13 21:24:00', NULL, '2026-02-13 21:24:00', '2026-02-13 21:24:00', NULL),
(12, 5, 17, 1, 'pendiente', 40000.00, 40000.00, '', '2026-02-13 21:33:16', NULL, '2026-02-13 21:33:16', '2026-02-13 21:33:16', '2026-02-13 21:33:16'),
(13, 5, 17, 1, 'pendiente', 35000.00, 35000.00, '', '2026-02-13 21:43:02', NULL, '2026-02-13 21:43:02', '2026-02-13 21:43:02', NULL),
(14, 5, NULL, 1, 'pendiente', 45000.00, 45000.00, '', '2026-02-13 22:27:27', NULL, '2026-02-13 22:27:27', '2026-02-13 22:27:27', NULL),
(15, 5, NULL, 1, 'pendiente', 49000.00, 49000.00, '', '2026-02-13 23:15:27', NULL, '2026-02-13 23:15:27', '2026-02-13 23:15:27', NULL),
(16, 5, NULL, 1, 'pendiente', 24000.00, 24000.00, '', '2026-02-13 23:19:51', NULL, '2026-02-13 23:19:51', '2026-02-13 23:19:51', NULL),
(17, 10, NULL, 1, 'pendiente', 20000.00, 20000.00, '', '2026-02-13 23:27:15', NULL, '2026-02-13 23:27:15', '2026-02-13 23:27:15', NULL),
(18, 5, NULL, 1, 'pendiente', 27000.00, 27000.00, '', '2026-02-13 23:30:38', NULL, '2026-02-13 23:30:38', '2026-02-13 23:30:38', NULL),
(19, 5, NULL, 1, 'pendiente', 4000.00, 4000.00, '', '2026-02-13 23:33:22', NULL, '2026-02-13 23:33:22', '2026-02-13 23:33:22', NULL),
(20, 5, NULL, 1, 'pendiente', 62000.00, 62000.00, '', '2026-02-13 23:34:11', NULL, '2026-02-13 23:34:11', '2026-02-13 23:34:11', NULL),
(21, 5, NULL, 1, 'pendiente', 17000.00, 17000.00, '', '2026-02-13 23:50:50', NULL, '2026-02-13 23:50:50', '2026-02-13 23:50:50', NULL),
(22, 5, NULL, 1, 'pendiente', 13000.00, 13000.00, '', '2026-02-13 23:51:38', NULL, '2026-02-13 23:51:38', '2026-02-13 23:51:38', NULL),
(23, 5, NULL, 1, 'pendiente', 20000.00, 20000.00, '', '2026-02-14 01:25:39', NULL, '2026-02-14 01:25:39', '2026-02-14 01:25:39', NULL),
(24, 9, NULL, 1, 'entregado', 17000.00, 17000.00, '', '2026-02-14 15:32:28', '2026-02-14 21:51:03', '2026-02-14 15:32:28', '2026-02-14 15:51:03', NULL),
(25, 9, NULL, 1, 'entregado', 119000.00, 119000.00, '', '2026-02-14 15:51:52', '2026-02-14 21:55:07', '2026-02-14 15:51:52', '2026-02-14 15:55:07', NULL),
(26, 5, NULL, 1, 'pendiente', 12000.00, 12000.00, '', '2026-02-14 16:44:16', NULL, '2026-02-14 16:44:16', '2026-02-14 16:44:16', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_productos_detalles`
--

CREATE TABLE `pedido_productos_detalles` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `pedido_productos_detalles`
--

INSERT INTO `pedido_productos_detalles` (`id`, `pedido_id`, `producto_id`, `cliente_id`, `cantidad`, `precio_unitario`, `subtotal`, `created_at`) VALUES
(1, 1, 1, NULL, 1, 25000.00, 25000.00, '2025-12-26 19:45:21'),
(2, 2, 3, NULL, 1, 18000.00, 18000.00, '2025-12-26 19:46:23'),
(3, 2, 7, NULL, 1, 12000.00, 12000.00, '2025-12-26 19:46:23'),
(4, 3, 4, NULL, 1, 3000.00, 3000.00, '2025-12-26 21:22:59'),
(5, 3, 4, NULL, 4, 3000.00, 12000.00, '2025-12-26 21:22:59'),
(6, 4, 3, NULL, 1, 18000.00, 18000.00, '2025-12-26 23:38:52'),
(7, 4, 18, NULL, 3, 5000.00, 15000.00, '2025-12-26 23:38:52'),
(8, 5, 5, NULL, 10, 5000.00, 50000.00, '2025-12-27 00:22:59'),
(9, 6, 11, NULL, 1, 6000.00, 6000.00, '2026-01-20 16:15:05'),
(10, 7, 29, NULL, 1, 4000.00, 4000.00, '2026-01-28 22:17:05'),
(11, 7, 8, NULL, 1, 7000.00, 7000.00, '2026-01-28 22:17:05'),
(12, 8, 5, NULL, 3, 5000.00, 15000.00, '2026-02-12 23:10:36'),
(13, 8, 7, NULL, 1, 12000.00, 12000.00, '2026-02-12 23:10:36'),
(14, 9, 6, NULL, 1, 8000.00, 8000.00, '2026-02-12 23:26:10'),
(15, 10, 5, NULL, 1, 5000.00, 5000.00, '2026-02-12 23:26:52'),
(16, 11, 7, NULL, 2, 12000.00, 24000.00, '2026-02-13 21:24:00'),
(17, 13, 18, NULL, 7, 5000.00, 35000.00, '2026-02-13 21:43:02'),
(18, 14, 18, 17, 1, 5000.00, 5000.00, '2026-02-13 22:27:27'),
(19, 14, 18, 17, 3, 5000.00, 15000.00, '2026-02-13 22:27:27'),
(20, 14, 9, 17, 1, 15000.00, 15000.00, '2026-02-13 22:27:27'),
(21, 14, 19, 17, 1, 5000.00, 5000.00, '2026-02-13 22:27:27'),
(22, 14, 19, 17, 1, 5000.00, 5000.00, '2026-02-13 22:27:27'),
(23, 15, 9, 17, 2, 15000.00, 30000.00, '2026-02-13 23:15:27'),
(24, 15, 11, 17, 1, 6000.00, 6000.00, '2026-02-13 23:15:27'),
(25, 15, 19, 17, 1, 5000.00, 5000.00, '2026-02-13 23:15:27'),
(26, 15, 13, 17, 2, 4000.00, 8000.00, '2026-02-13 23:15:27'),
(27, 16, 8, 17, 1, 7000.00, 7000.00, '2026-02-13 23:19:51'),
(28, 16, 19, 17, 1, 5000.00, 5000.00, '2026-02-13 23:19:51'),
(29, 16, 7, 17, 1, 12000.00, 12000.00, '2026-02-13 23:19:51'),
(30, 17, 7, 17, 1, 12000.00, 12000.00, '2026-02-13 23:27:15'),
(31, 17, 10, 17, 1, 8000.00, 8000.00, '2026-02-13 23:27:15'),
(32, 18, 7, 17, 1, 12000.00, 12000.00, '2026-02-13 23:30:38'),
(33, 18, 9, 17, 1, 15000.00, 15000.00, '2026-02-13 23:30:38'),
(34, 19, 13, 17, 1, 4000.00, 4000.00, '2026-02-13 23:33:22'),
(35, 20, 6, 17, 1, 8000.00, 8000.00, '2026-02-13 23:34:11'),
(36, 20, 21, 17, 1, 23000.00, 23000.00, '2026-02-13 23:34:11'),
(37, 20, 8, 17, 2, 7000.00, 14000.00, '2026-02-13 23:34:11'),
(38, 20, 7, 17, 1, 12000.00, 12000.00, '2026-02-13 23:34:11'),
(39, 20, 18, 17, 1, 5000.00, 5000.00, '2026-02-13 23:34:11'),
(40, 21, 5, 17, 1, 5000.00, 5000.00, '2026-02-13 23:50:50'),
(41, 21, 7, 17, 1, 12000.00, 12000.00, '2026-02-13 23:50:50'),
(42, 22, 5, 17, 1, 5000.00, 5000.00, '2026-02-13 23:51:38'),
(43, 22, 10, 17, 1, 8000.00, 8000.00, '2026-02-13 23:51:38'),
(44, 23, 9, 17, 1, 15000.00, 15000.00, '2026-02-14 01:25:39'),
(45, 23, 18, 15, 1, 5000.00, 5000.00, '2026-02-14 01:25:39'),
(46, 24, 7, 3, 1, 12000.00, 12000.00, '2026-02-14 15:32:28'),
(47, 24, 18, 10, 1, 5000.00, 5000.00, '2026-02-14 15:32:28'),
(48, 25, 2, 3, 2, 22000.00, 44000.00, '2026-02-14 15:51:52'),
(49, 25, 1, 10, 3, 25000.00, 75000.00, '2026-02-14 15:51:52'),
(50, 26, 7, 17, 1, 12000.00, 12000.00, '2026-02-14 16:44:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `clave` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id`, `modulo_id`, `nombre`, `descripcion`, `clave`, `created_at`) VALUES
(1, 1, 'Ver dashboard', 'Permite ver el panel principal', 'dashboard_ver', '2025-12-29 16:29:27'),
(2, 2, 'Ver habitaciones', 'Permite ver la lista de habitaciones', 'habitaciones_ver', '2025-12-29 16:29:27'),
(3, 2, 'Crear habitaciones', 'Permite crear nuevas habitaciones', 'habitaciones_crear', '2025-12-29 16:29:27'),
(4, 2, 'Editar habitaciones', 'Permite editar habitaciones existentes', 'habitaciones_editar', '2025-12-29 16:29:27'),
(5, 2, 'Eliminar habitaciones', 'Permite eliminar habitaciones', 'habitaciones_eliminar', '2025-12-29 16:29:27'),
(6, 3, 'Ver usuarios', 'Permite ver la lista de usuarios', 'usuarios_ver', '2025-12-29 16:29:27'),
(7, 3, 'Crear usuarios', 'Permite crear nuevos usuarios', 'usuarios_crear', '2025-12-29 16:29:27'),
(8, 3, 'Editar usuarios', 'Permite editar usuarios existentes', 'usuarios_editar', '2025-12-29 16:29:27'),
(9, 3, 'Eliminar usuarios', 'Permite eliminar usuarios', 'usuarios_eliminar', '2025-12-29 16:29:27'),
(10, 3, 'Gestionar roles', 'Permite asignar y gestionar roles', 'usuarios_gestionar_roles', '2025-12-29 16:29:27'),
(11, 4, 'Ver clientes', 'Permite ver la lista de clientes', 'clientes_ver', '2025-12-29 16:29:27'),
(12, 4, 'Crear clientes', 'Permite crear nuevos clientes', 'clientes_crear', '2025-12-29 16:29:27'),
(13, 4, 'Editar clientes', 'Permite editar clientes existentes', 'clientes_editar', '2025-12-29 16:29:27'),
(14, 4, 'Eliminar clientes', 'Permite eliminar clientes', 'clientes_eliminar', '2025-12-29 16:29:27'),
(15, 5, 'Ver productos', 'Permite ver la lista de productos', 'productos_ver', '2025-12-29 16:29:27'),
(16, 5, 'Crear productos', 'Permite crear nuevos productos', 'productos_crear', '2025-12-29 16:29:27'),
(17, 5, 'Editar productos', 'Permite editar productos existentes', 'productos_editar', '2025-12-29 16:29:27'),
(18, 5, 'Eliminar productos', 'Permite eliminar productos', 'productos_eliminar', '2025-12-29 16:29:27'),
(19, 6, 'Ver pedidos', 'Permite ver la lista de pedidos', 'pedidos_ver', '2025-12-29 16:29:27'),
(20, 6, 'Crear pedidos', 'Permite crear nuevos pedidos', 'pedidos_crear', '2025-12-29 16:29:27'),
(21, 6, 'Editar pedidos', 'Permite editar pedidos existentes', 'pedidos_editar', '2025-12-29 16:29:27'),
(22, 6, 'Eliminar pedidos', 'Permite eliminar pedidos', 'pedidos_eliminar', '2025-12-29 16:29:27'),
(23, 6, 'Cambiar estado pedidos', 'Permite cambiar el estado de los pedidos', 'pedidos_cambiar_estado', '2025-12-29 16:29:27'),
(24, 7, 'Ver reservas', 'Permite ver la lista de reservas', 'reservas_ver', '2025-12-29 16:29:27'),
(25, 7, 'Crear reservas', 'Permite crear nuevas reservas', 'reservas_crear', '2025-12-29 16:29:27'),
(26, 7, 'Editar reservas', 'Permite editar reservas existentes', 'reservas_editar', '2025-12-29 16:29:27'),
(27, 7, 'Eliminar reservas', 'Permite eliminar reservas', 'reservas_eliminar', '2025-12-29 16:29:27'),
(28, 7, 'Cambiar estado reservas', 'Permite cambiar el estado de las reservas', 'reservas_cambiar_estado', '2025-12-29 16:29:27'),
(29, 8, 'Ver eventos', 'Permite ver la lista de eventos', 'eventos_ver', '2025-12-29 16:29:27'),
(30, 8, 'Crear eventos', 'Permite crear nuevos eventos', 'eventos_crear', '2025-12-29 16:29:27'),
(31, 8, 'Editar eventos', 'Permite editar eventos existentes', 'eventos_editar', '2025-12-29 16:29:27'),
(32, 8, 'Eliminar eventos', 'Permite eliminar eventos', 'eventos_eliminar', '2025-12-29 16:29:27'),
(33, 9, 'Ver espacios', 'Permite ver la lista de espacios', 'espacios_ver', '2025-12-29 16:29:27'),
(34, 9, 'Crear espacios', 'Permite crear nuevos espacios', 'espacios_crear', '2025-12-29 16:29:27'),
(35, 9, 'Editar espacios', 'Permite editar espacios existentes', 'espacios_editar', '2025-12-29 16:29:27'),
(36, 9, 'Eliminar espacios', 'Permite eliminar espacios', 'espacios_eliminar', '2025-12-29 16:29:27'),
(37, 10, 'Ver reservas eventos', 'Permite ver reservas de eventos', 'reservas_eventos_ver', '2025-12-29 16:29:27'),
(38, 10, 'Crear reservas eventos', 'Permite crear reservas de eventos', 'reservas_eventos_crear', '2025-12-29 16:29:27'),
(39, 10, 'Editar reservas eventos', 'Permite editar reservas de eventos', 'reservas_eventos_editar', '2025-12-29 16:29:27'),
(40, 10, 'Eliminar reservas eventos', 'Permite eliminar reservas de eventos', 'reservas_eventos_eliminar', '2025-12-29 16:29:27'),
(41, 11, 'Ver contabilidad', 'Permite ver el módulo contable', 'contabilidad_ver', '2025-12-29 16:29:27'),
(42, 11, 'Crear transacciones', 'Permite crear transacciones contables', 'contabilidad_crear', '2025-12-29 16:29:27'),
(43, 11, 'Editar transacciones', 'Permite editar transacciones', 'contabilidad_editar', '2025-12-29 16:29:27'),
(44, 11, 'Eliminar transacciones', 'Permite eliminar transacciones', 'contabilidad_eliminar', '2025-12-29 16:29:27'),
(45, 11, 'Confirmar transacciones', 'Permite confirmar transacciones', 'contabilidad_confirmar', '2025-12-29 16:29:27'),
(46, 11, 'Ver reportes', 'Permite ver reportes financieros', 'contabilidad_reportes', '2025-12-29 16:29:27'),
(47, 12, 'Ver reportes', 'Permite ver reportes financieros', 'reportes_ver', '2025-12-29 16:29:27'),
(48, 12, 'Exportar reportes', 'Permite exportar reportes', 'reportes_exportar', '2025-12-29 16:29:27'),
(49, 13, 'Ver turnos', 'Permite ver la gestión de turnos', 'turnos_ver', '2025-12-29 16:29:27'),
(50, 13, 'Crear turnos', 'Permite crear turnos', 'turnos_crear', '2025-12-29 16:29:27'),
(51, 13, 'Editar turnos', 'Permite editar turnos', 'turnos_editar', '2025-12-29 16:29:27'),
(52, 13, 'Eliminar turnos', 'Permite eliminar turnos', 'turnos_eliminar', '2025-12-29 16:29:27'),
(53, 13, 'Asignar turnos', 'Permite asignar turnos a usuarios', 'turnos_asignar', '2025-12-29 16:29:27'),
(54, 13, 'Aprobar turnos', 'Permite aprobar turnos', 'turnos_aprobar', '2025-12-29 16:29:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id` int(11) NOT NULL DEFAULT 0,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento` enum('DNI','Pasaporte','Cedula') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'DNI',
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Colombia',
  `fecha_nacimiento` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`id`, `nombre`, `apellido`, `email`, `telefono`, `documento`, `tipo_documento`, `direccion`, `ciudad`, `pais`, `fecha_nacimiento`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Jhuliet Anghelica', 'Tibasosa', 'JhulietTibasosa30@gmail.com', '+573224125100', '1010150914', 'Cedula', 'Calle 123 #45-67', 'Bogotá', 'Colombia', '2003-09-07', '2025-11-18 15:24:33', '2025-11-18 21:16:25', NULL),
(2, 'Juan', 'Pérez', 'juan.perez@email.com', '3107654321', '0987654321', 'Cedula', 'Carrera 45 #12-34', 'Medellín', 'CO', '1985-08-20', '2025-11-18 15:24:33', '2026-03-03 13:55:09', NULL),
(3, 'Ana', 'López', 'ana.lopez@email.com', '3159876543', '153051152', 'Cedula', 'Avenida 68 #89-01', 'Cali', 'VE', '1992-12-10', '2025-11-18 15:24:33', '2026-03-03 13:55:45', NULL),
(4, 'Pedro', 'Martínez', 'pedro.martinez@email.com', '3201239876', '5544332211', 'Pasaporte', 'Diagonal 27 #34-56', 'Cartagena', 'CO', '1988-03-25', '2025-11-18 15:24:33', '2026-03-03 13:56:38', NULL),
(5, 'Camila', 'Caceres', 'Cami_caceres30@gmail.com', '3218381092', '105546588', 'Cedula', 'Calle 22#16-34', 'Cùcuta', 'Colombia', '2003-10-20', '2025-11-18 20:35:54', '2025-11-18 21:16:14', NULL),
(6, 'AFDSF', 'ADSFADS', 'admin@hotel.com', '3203564489', '444444555', 'Cedula', 'DFD', 'TAME', 'Colombia', '2025-12-26', '2025-12-26 14:32:07', '2026-03-03 02:02:58', NULL),
(7, 'OLAYA', 'SHELKIER', 'cheolaya11@gmail.com', '3203564488', '111823782', 'Cedula', 'CALLE X', 'TAMEE', 'Colombia', '2025-12-26', '2025-12-26 22:56:05', '2026-03-03 02:02:58', NULL),
(8, 'pepito ', 'PEREZ', 'pepito@gmail.com', '23424234', '23423424234', 'DNI', 'fdgdfgdf', 'TAME', 'colombia', '2006-07-26', '2025-12-27 02:53:07', '2025-12-27 02:53:07', NULL),
(9, 'PEDRITO', 'PEREZ', 'pedrito@gmail.com', '3203564480', '111111111114', 'Cedula', 'kk', 'TAME', 'Colombia', '2026-01-08', '2025-12-29 22:55:34', '2026-03-03 02:02:58', NULL),
(10, 'eeeeeeeeeeee', 'ñññññññññññ', 'pedrito@gmail.com', '3124589774', '58456456415641', 'Cedula', 'ĺkl', 'TAME', 'Colombia', '2025-12-31', '2025-12-29 22:59:33', '2026-03-03 02:02:58', NULL),
(11, 'Juanito', 'Rodriguez', 'juanito@gmail.com', '312345867', '10985672', 'Cedula', 'Calle 16', 'Tame', 'Colombia', '2025-01-01', '2026-01-14 16:29:05', '2026-01-23 22:38:22', NULL),
(12, 'juan', 'cardenas', 'jcardenas.@gmail.com', '320234234234', '342342342', 'Cedula', '123123', 'TAME', 'colombia', '1979-01-28', '2026-01-28 22:06:15', '2026-01-28 22:06:15', NULL),
(13, 'pepito', 'PEREZ', 'pei95@hotel.com', '31023454345', '2342342', 'Cedula', '234324', 'TAME', 'COLOMBIA', '2005-09-28', '2026-01-28 22:49:23', '2026-01-28 22:53:55', '2026-01-28 22:53:55'),
(14, 'TESEO', 'PASEO', 'PASEO@hotel.com', '2434234234', '1243214234', 'DNI', 'WREFSEDF', 'TAME', 'COLOMBIA', '1985-12-10', '2026-01-29 00:18:10', '2026-03-03 02:21:28', NULL),
(15, 'dell', 'APP', 'DELL@hotel.com', '123123123', '43324234', 'Cedula', 'DGSFGFDG', 'TAME', 'Colombia', '1990-02-02', '2026-02-11 22:30:54', '2026-03-03 02:02:58', NULL),
(17, 'pipx', 'pithon', 'pipx@hotel.com', '124214234', '143234234', NULL, '23423542345', 'TAME', 'colombia', NULL, '2026-02-11 22:35:44', '2026-02-11 22:35:44', NULL),
(20, 'PEDRO', 'PERRITO', 'pedroperrito@gmail.com', '31456788987', '1117628828', 'Cedula', 'direcciòn de pedro perrito', 'Tame', 'CO', '1994-11-11', '2026-03-13 16:41:27', '2026-03-13 16:41:27', NULL),
(21, 'CARLITOS', 'NIÑO', 'carlitos@gmail.com', '31436789098', '1050462222', NULL, 'adfasdfafa', 'Tame', '', NULL, '2026-03-13 16:54:06', '2026-03-13 16:54:06', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria` enum('comida','bebida','snack','higiene','otros') NOT NULL DEFAULT 'otros',
  `motivo_viaje` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen_url` varchar(500) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `categoria`, `motivo_viaje`, `precio`, `imagen_url`, `stock`, `activo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Hamburguesa Clásica', 'Hamburguesa de carne con lechuga, tomate, cebolla y salsa especial', 'comida', NULL, 25000.00, 'assets/images/products/hamburguesa.jpg', 46, 1, '2025-12-26 19:37:37', '2026-03-13 16:23:50', '2026-03-13 16:23:50'),
(2, 'Pizza Personal', 'Pizza pepperoni tamaño personal', 'comida', NULL, 22000.00, 'assets/images/products/pizza.jpg', 28, 1, '2025-12-26 19:37:37', '2026-03-13 16:23:56', '2026-03-13 16:23:56'),
(3, 'Ensalada César', 'Ensalada fresca con pollo, crutones y aderezo césar', 'comida', NULL, 18000.00, 'assets/images/products/ensalada.jpg', 23, 1, '2025-12-26 19:37:37', '2026-02-12 23:07:25', '2026-02-12 23:07:25'),
(4, 'Agua Mineral 500ml', 'Agua mineral natural', 'bebida', NULL, 3000.00, 'assets/images/products/agua.jpg', 95, 1, '2025-12-26 19:37:37', '2026-03-13 16:23:56', '2026-03-13 16:23:56'),
(5, 'Refresco Cola 600ml', 'Bebida gaseosa cola', 'bebida', NULL, 5000.00, 'assets/images/products/cola.jpg', 64, 1, '2025-12-26 19:37:37', '2026-03-13 16:24:04', '2026-03-13 16:24:04'),
(6, 'Cerveza Nacional 330ml', 'Cerveza nacional botella', 'bebida', NULL, 8000.00, 'assets/images/products/cerveza.jpg', 58, 1, '2025-12-26 19:37:37', '2026-03-13 16:24:01', '2026-03-13 16:24:01'),
(7, 'Papas Fritas', 'Porción de papas fritas con ketchup', 'snack', NULL, 12000.00, 'assets/images/products/papas.jpg', 29, 1, '2025-12-26 19:37:37', '2026-03-13 16:24:09', '2026-03-13 16:24:09'),
(8, 'Chocolate Bar', 'Barra de chocolate con leche', 'snack', NULL, 7000.00, 'assets/images/products/chocolate.jpg', 66, 1, '2025-12-26 19:37:37', '2026-03-13 16:24:08', '2026-03-13 16:24:08'),
(9, 'Jabón de Manos', 'Jabón líquido antibacterial', 'higiene', NULL, 15000.00, 'assets/images/products/jabon.jpg', 45, 1, '2025-12-26 19:37:37', '2026-03-13 16:24:10', '2026-03-13 16:24:10'),
(10, 'Toallas Papel', 'Paquete de toallas de papel', 'higiene', NULL, 8000.00, 'assets/images/products/toallas.jpg', 38, 1, '2025-12-26 19:37:37', '2026-03-13 16:24:18', '2026-03-13 16:24:18'),
(11, 'Alfanjor', 'Galletas con dulce de leche', 'snack', NULL, 6000.00, '', 2, 1, '2025-12-26 19:48:32', '2026-03-13 16:24:06', '2026-03-13 16:24:06'),
(12, 'Soap', 'jabón', 'higiene', NULL, 4000.00, '', 3, 1, '2025-12-26 22:04:22', '2025-12-26 23:08:12', '2025-12-26 23:08:12'),
(13, 'Soap', 'jabón', 'higiene', NULL, 4000.00, '', 0, 1, '2025-12-26 22:04:40', '2026-03-13 16:24:14', '2026-03-13 16:24:14'),
(18, 'jabón', 'jabón', 'higiene', NULL, 5000.00, 'assets/images/products/producto_1766787972_7189.webp', 6, 1, '2025-12-26 22:26:12', '2026-03-13 16:24:09', '2026-03-13 16:24:09'),
(19, 'jabónPlus', 'jabón', 'higiene', NULL, 5000.00, 'assets/images/products/producto_1766787972_7189.webp', 0, 1, '2025-12-26 22:26:55', '2026-03-13 16:24:12', '2026-03-13 16:24:12'),
(20, 'jabónPlus', 'SANDWICH', 'higiene', NULL, 5000.00, 'assets/images/products/producto_1766787972_7189.webp', 4, 1, '2025-12-26 22:57:23', '2025-12-26 23:07:59', '2025-12-26 23:07:59'),
(21, 'PERRITO CALIENTE', 'CON QUESO, CHORIZO Y DEMÀS', 'comida', NULL, 23000.00, 'assets/images/products/producto_1766790017_1117.webp', 9, 1, '2025-12-26 23:00:17', '2026-03-13 16:23:54', '2026-03-13 16:23:54'),
(29, 'CERVEZA AGUILA', '', 'bebida', NULL, 4000.00, '', 39, 1, '2026-01-14 16:42:54', '2026-03-13 16:23:59', '2026-03-13 16:23:59'),
(30, 'JABÓN AROMAS', 'JABÓN PERSONAL PARA HABITACIÓN', 'higiene', NULL, 2500.00, 'uploads/products/producto_1773808071_7359.jpg', 20, 1, '2026-03-13 16:27:34', '2026-03-18 04:27:51', NULL),
(31, '', '', 'otros', NULL, 0.00, '', 0, 1, '2026-03-13 16:34:45', '2026-03-14 21:13:04', '2026-03-14 21:13:04'),
(32, 'AFDFJLASDF', 'fakdfjlaksdf', 'comida', NULL, 19000.00, 'assets/images/products/producto_1773419822_4221.jpg', 20, 1, '2026-03-13 16:37:02', '2026-03-13 16:37:14', '2026-03-13 16:37:14'),
(33, '', '', 'otros', NULL, 0.00, '', 0, 1, '2026-03-13 20:36:53', '2026-03-14 21:13:06', '2026-03-14 21:13:06'),
(34, 'JABÓN AROMA', 'JABÓN PERSONAL PARA HABITACIÓN', 'higiene', NULL, 2500.00, 'uploads/products/producto_1773454728_4224.jpg', 20, 1, '2026-03-14 02:18:48', '2026-03-14 03:17:28', '2026-03-14 03:17:28'),
(35, 'JABÓN AROMA', 'JABÓN PERSONAL PARA HABITACIÓN', 'higiene', NULL, 2500.00, 'uploads/products/producto_1773455698_4407.jpg', 20, 1, '2026-03-14 02:34:58', '2026-03-14 03:17:23', '2026-03-14 03:17:23'),
(36, 'Helado', 'Helado casero', 'otros', NULL, 50000.00, 'uploads/products/producto_1773675854_7853.jpg', 20, 1, '2026-03-16 15:25:04', '2026-03-16 15:44:37', NULL),
(37, 'frgrdg', 'sdfgsdxfv', 'otros', NULL, 435435.00, 'uploads/products/producto_1773677796_7812.jpg', 43, 1, '2026-03-16 15:49:26', '2026-03-16 16:16:36', NULL),
(38, 'rgdfg', 'fghbfgh 5ty45 ', 'otros', NULL, 456456.00, 'uploads/products/producto_1773678036_4364.jpg', 345646, 1, '2026-03-16 16:20:36', '2026-03-16 16:20:36', NULL),
(39, 'linux', 'linux', 'otros', NULL, 200000.00, 'uploads/products/producto_1773806912_7314.jpg', 50, 1, '2026-03-18 04:08:32', '2026-03-18 04:08:32', NULL);

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
  `estado` enum('pendiente','confirmada','checkin','checkout','cancelada','completada') DEFAULT 'pendiente',
  `precio_noche` decimal(10,2) NOT NULL,
  `num_noches` int(11) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `metodo_pago` enum('efectivo','tarjeta','transferencia') DEFAULT 'efectivo',
  `motivo_viaje` varchar(50) DEFAULT 'turismo',
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `cliente_id`, `habitacion_id`, `usuario_id`, `fecha_entrada`, `fecha_salida`, `num_huespedes`, `estado`, `precio_noche`, `num_noches`, `precio_total`, `metodo_pago`, `motivo_viaje`, `notas`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 2, '2025-12-26', '2025-12-27', 2, 'confirmada', 150000.00, 1, 150000.00, 'tarjeta', 'turismo', NULL, '2025-11-18 15:24:33', '2025-12-26 20:16:44', '2025-11-19 15:05:28'),
(2, 2, 2, 2, '2025-01-14', '2025-01-16', 2, 'confirmada', 250000.00, 2, 500000.00, 'efectivo', 'turismo', NULL, '2025-11-18 15:24:33', '2025-12-27 16:23:42', '2025-12-27 16:23:42'),
(3, 3, 5, 1, '2025-01-12', '2025-01-14', 1, 'confirmada', 150000.00, 2, 300000.00, 'transferencia', 'turismo', NULL, '2025-11-18 15:24:33', '2025-12-27 16:23:39', '2025-12-27 16:23:39'),
(4, 4, 4, 1, '2025-01-15', '2025-01-18', 4, 'pendiente', 800000.00, 3, 2400000.00, 'tarjeta', 'turismo', NULL, '2025-11-18 15:24:33', '2025-11-19 15:16:30', '2025-11-19 15:16:30'),
(5, 1, 2, NULL, '2025-12-01', '2025-12-03', 1, 'confirmada', 250000.00, 2, 500000.00, 'tarjeta', 'turismo', NULL, '2025-11-19 14:33:37', '2025-12-27 16:23:44', '2025-12-27 16:23:44'),
(6, 5, 5, NULL, '2025-11-25', '2025-12-12', 1, 'confirmada', 150000.00, 17, 2550000.00, 'efectivo', 'turismo', NULL, '2025-11-19 14:36:40', '2025-12-27 16:23:47', '2025-12-27 16:23:47'),
(7, 2, 7, NULL, '2025-11-28', '2025-12-20', 1, 'confirmada', 250000.00, 22, 5500000.00, 'transferencia', 'turismo', NULL, '2025-11-19 14:47:24', '2025-12-27 16:23:49', '2025-12-27 16:23:49'),
(8, 4, 2, NULL, '2025-12-12', '2025-12-25', 1, 'completada', 250000.00, 13, 3250000.00, 'transferencia', 'turismo', NULL, '2025-11-19 14:54:24', '2025-12-27 16:23:52', '2025-12-27 16:23:52'),
(9, 5, 2, NULL, '2025-12-09', '2025-12-10', 1, 'confirmada', 250000.00, 1, 250000.00, 'efectivo', 'turismo', NULL, '2025-12-09 16:15:54', '2025-12-27 16:24:10', '2025-12-27 16:24:10'),
(10, 5, 8, NULL, '2025-12-26', '2025-12-28', 1, 'confirmada', 50000.00, 2, 100000.00, 'efectivo', 'turismo', NULL, '2025-12-26 19:53:00', '2025-12-27 16:24:13', '2025-12-27 16:24:13'),
(11, 5, 9, NULL, '2025-12-26', '2025-12-31', 1, 'pendiente', 100000.00, 5, 500000.00, 'efectivo', 'turismo', NULL, '2025-12-26 23:41:05', '2025-12-27 16:24:16', '2025-12-27 16:24:16'),
(12, 8, 8, NULL, '2025-12-27', '2025-12-28', 1, 'confirmada', 50000.00, 1, 50000.00, 'efectivo', 'turismo', NULL, '2025-12-27 16:32:15', '2025-12-27 16:38:22', '2025-12-27 16:38:22'),
(13, 8, 2, NULL, '2025-12-27', '2025-12-28', 1, 'completada', 250000.00, 1, 250000.00, 'efectivo', 'turismo', NULL, '2025-12-27 16:33:02', '2025-12-29 23:49:39', '2025-12-29 23:49:39'),
(14, 8, 5, NULL, '2025-12-27', '2025-12-28', 1, 'completada', 150000.00, 1, 150000.00, 'efectivo', 'turismo', NULL, '2025-12-27 16:33:44', '2025-12-29 23:50:16', '2025-12-29 23:50:16'),
(15, 9, 8, NULL, '2025-12-29', '2026-01-08', 1, 'confirmada', 50000.00, 10, 500000.00, 'transferencia', 'turismo', NULL, '2025-12-29 22:56:19', '2025-12-29 23:51:00', '2025-12-29 23:51:00'),
(16, 1, 2, NULL, '2025-12-30', '2025-12-31', 1, 'pendiente', 250000.00, 1, 250000.00, 'efectivo', 'turismo', NULL, '2025-12-29 23:30:13', '2025-12-29 23:48:14', '2025-12-29 23:48:14'),
(17, 1, 3, NULL, '2025-12-29', '2025-12-30', 1, 'pendiente', 0.00, 1, 0.00, 'efectivo', 'turismo', NULL, '2025-12-29 23:31:09', '2025-12-29 23:50:53', '2025-12-29 23:50:53'),
(18, 1, 4, NULL, '2026-01-15', '2026-01-16', 1, 'pendiente', 800000.00, 1, 800000.00, 'tarjeta', 'turismo', NULL, '2025-12-29 23:31:24', '2025-12-29 23:51:03', '2025-12-29 23:51:03'),
(19, 6, 5, NULL, '2025-12-31', '2026-01-31', 1, 'pendiente', 150000.00, 31, 4650000.00, 'efectivo', 'turismo', NULL, '2025-12-29 23:35:44', '2025-12-29 23:51:05', '2025-12-29 23:51:05'),
(20, 8, 8, NULL, '2025-12-29', '2025-12-30', 1, 'cancelada', 50000.00, 1, 50000.00, 'efectivo', 'turismo', NULL, '2025-12-29 23:52:04', '2025-12-30 16:58:31', NULL),
(21, 1, 1, NULL, '2025-12-30', '2025-12-31', 1, 'cancelada', 150000.00, 1, 150000.00, 'efectivo', 'turismo', NULL, '2025-12-30 00:06:49', '2025-12-30 23:05:36', NULL),
(22, 1, 2, NULL, '2025-12-30', '2025-12-31', 1, 'cancelada', 250000.00, 1, 250000.00, 'efectivo', 'turismo', NULL, '2025-12-30 00:13:08', '2025-12-30 17:03:19', NULL),
(23, 3, 5, NULL, '2025-12-30', '2025-12-31', 1, 'completada', 150000.00, 1, 150000.00, 'tarjeta', 'turismo', NULL, '2025-12-30 00:15:25', '2026-01-13 23:12:04', NULL),
(24, 4, 7, NULL, '2025-12-30', '2025-12-31', 1, 'cancelada', 250000.00, 1, 250000.00, 'efectivo', 'turismo', NULL, '2025-12-30 00:15:54', '2025-12-30 00:20:24', NULL),
(25, 6, 7, NULL, '2025-12-30', '2025-12-31', 1, 'completada', 250000.00, 1, 250000.00, 'tarjeta', 'turismo', NULL, '2025-12-30 00:20:24', '2026-01-13 23:12:04', NULL),
(26, 2, 6, NULL, '2025-12-30', '2025-12-31', 1, 'cancelada', 250000.00, 1, 250000.00, 'efectivo', 'turismo', NULL, '2025-12-30 00:23:29', '2025-12-30 23:05:36', NULL),
(27, 7, 9, NULL, '2025-12-30', '2025-12-31', 1, 'cancelada', 100000.00, 1, 100000.00, 'efectivo', 'turismo', NULL, '2025-12-30 00:28:54', '2025-12-30 00:42:13', NULL),
(28, 8, 9, NULL, '2025-12-30', '2025-12-31', 1, 'completada', 100000.00, 1, 100000.00, 'tarjeta', 'turismo', NULL, '2025-12-30 00:42:13', '2026-01-13 23:12:04', NULL),
(29, 1, 8, NULL, '2025-12-30', '2025-12-31', 1, 'cancelada', 50000.00, 1, 50000.00, 'efectivo', 'turismo', NULL, '2025-12-30 00:46:47', '2025-12-30 23:05:36', NULL),
(30, 1, 8, NULL, '2025-12-30', '2025-12-31', 1, 'cancelada', 50000.00, 1, 50000.00, 'efectivo', 'turismo', NULL, '2025-12-30 00:49:46', '2025-12-30 23:05:36', NULL),
(31, 2, 4, NULL, '2025-12-30', '2025-12-31', 1, 'cancelada', 800000.00, 1, 800000.00, 'efectivo', 'turismo', NULL, '2025-12-30 00:50:55', '2025-12-30 23:05:36', NULL),
(32, 3, 2, NULL, '2025-12-30', '2025-12-31', 1, 'completada', 250000.00, 1, 250000.00, 'tarjeta', 'turismo', NULL, '2025-12-30 17:03:19', '2026-01-13 23:12:04', NULL),
(33, 8, 6, NULL, '2025-12-30', '2025-12-31', 1, 'cancelada', 250000.00, 1, 250000.00, 'efectivo', 'turismo', NULL, '2025-12-30 20:33:17', '2025-12-30 23:05:36', NULL),
(34, 7, 8, NULL, '2026-01-14', '2026-01-15', 1, 'completada', 50000.00, 1, 50000.00, 'efectivo', 'turismo', NULL, '2026-01-14 16:26:47', '2026-01-16 16:46:49', NULL),
(35, 11, 8, NULL, '2026-01-16', '2026-01-17', 1, 'completada', 50000.00, 1, 50000.00, 'transferencia', 'turismo', NULL, '2026-01-14 16:29:34', '2026-01-20 16:14:25', NULL),
(36, 7, 2, NULL, '2026-01-14', '2026-01-15', 1, 'cancelada', 250000.00, 1, 250000.00, 'tarjeta', 'turismo', NULL, '2026-01-14 16:38:22', '2026-01-16 16:46:49', NULL),
(37, 9, 2, NULL, '2026-01-16', '2026-01-18', 1, 'completada', 250000.00, 2, 500000.00, 'efectivo', 'turismo', NULL, '2026-01-16 16:47:41', '2026-01-20 16:14:25', NULL),
(38, 6, 7, NULL, '2026-01-16', '2026-01-17', 1, 'completada', 250000.00, 1, 250000.00, 'transferencia', 'turismo', NULL, '2026-01-16 16:50:19', '2026-01-20 16:14:25', NULL),
(39, 7, 2, NULL, '2026-01-28', '2026-01-30', 1, 'confirmada', 250000.00, 2, 500000.00, 'efectivo', 'turismo', NULL, '2026-01-28 17:01:10', '2026-01-28 17:18:38', '2026-01-28 17:18:38'),
(40, 11, 6, NULL, '2026-01-28', '2026-01-30', 1, 'pendiente', 250000.00, 2, 500000.00, 'efectivo', 'turismo', NULL, '2026-01-28 17:07:56', '2026-01-28 17:18:34', '2026-01-28 17:18:34'),
(41, 11, 2, NULL, '2026-01-28', '2026-01-30', 1, 'confirmada', 250000.00, 2, 500000.00, 'efectivo', 'turismo', NULL, '2026-01-28 17:18:59', '2026-01-28 17:24:09', '2026-01-28 17:24:09'),
(42, 11, 2, NULL, '2026-01-28', '2026-01-30', 1, 'pendiente', 250000.00, 2, 500000.00, 'efectivo', 'turismo', NULL, '2026-01-28 17:28:46', '2026-01-28 17:33:43', '2026-01-28 17:33:43'),
(43, 10, 2, NULL, '2026-01-28', '2026-01-30', 1, 'pendiente', 250000.00, 2, 500000.00, 'efectivo', 'turismo', NULL, '2026-01-28 17:35:11', '2026-01-28 17:42:34', '2026-01-28 17:42:34'),
(44, 11, 2, NULL, '2026-01-28', '2026-01-30', 1, 'confirmada', 250000.00, 2, 500000.00, 'efectivo', 'turismo', NULL, '2026-01-28 17:43:31', '2026-01-28 18:11:20', '2026-01-28 18:11:20'),
(45, 8, 2, NULL, '2026-01-28', '2026-01-30', 2, 'confirmada', 250000.00, 2, 500000.00, 'efectivo', 'turismo', NULL, '2026-01-28 18:12:25', '2026-01-28 18:17:06', '2026-01-28 18:17:06'),
(46, 10, 2, NULL, '2026-01-28', '2026-01-30', 2, 'cancelada', 250000.00, 2, 500000.00, 'efectivo', 'turismo', NULL, '2026-01-28 18:21:24', '2026-01-28 18:24:03', NULL),
(47, 11, 2, NULL, '2026-01-28', '2026-01-30', 2, 'confirmada', 250000.00, 2, 500000.00, 'efectivo', 'turismo', NULL, '2026-01-28 18:24:03', '2026-01-28 22:32:16', '2026-01-28 22:32:16'),
(48, 4, 6, NULL, '2026-01-28', '2026-01-30', 2, 'completada', 250000.00, 2, 500000.00, 'efectivo', 'turismo', NULL, '2026-01-28 18:57:11', '2026-02-02 23:07:40', NULL),
(49, 1, 13, NULL, '2026-01-28', '2026-01-29', 2, 'cancelada', 250000.00, 1, 250000.00, 'efectivo', 'turismo', NULL, '2026-01-28 22:01:47', '2026-01-28 23:04:56', NULL),
(50, 3, 8, NULL, '2026-01-28', '2026-01-30', 1, 'confirmada', 50000.00, 2, 100000.00, 'efectivo', 'turismo', 'si\n\nACOMPANANTES:\n[]', '2026-01-28 22:23:00', '2026-01-29 22:43:04', '2026-01-29 22:43:04'),
(51, 13, 5, NULL, '2026-01-28', '2026-01-30', 1, 'pendiente', 150000.00, 2, 300000.00, 'efectivo', 'turismo', '23RWEFWEF', '2026-01-28 22:53:41', '2026-01-28 22:55:22', '2026-01-28 22:55:22'),
(52, 12, 13, NULL, '2026-01-28', '2026-01-29', 1, 'completada', 250000.00, 1, 250000.00, 'tarjeta', 'turismo', 'sdfgsdv e wr ftwrf gergre w4', '2026-01-28 23:04:56', '2026-01-29 23:05:40', NULL),
(53, 12, 5, NULL, '2026-01-28', '2026-01-30', 1, 'cancelada', 150000.00, 2, 300000.00, 'efectivo', 'turismo', 'wef w fwer trth gertgh erg trhgetygethy rtyrt yrty tr rtyh', '2026-01-29 00:00:42', '2026-01-30 20:33:43', NULL),
(54, 12, 9, NULL, '2026-01-29', '2026-01-31', 1, 'cancelada', 100000.00, 2, 50000.00, 'efectivo', 'turismo', 'owefwepof', '2026-01-29 23:20:48', '2026-01-30 20:33:51', NULL),
(55, 8, 1, NULL, '2026-01-29', '2026-01-31', 1, 'cancelada', 150000.00, 2, 100000.00, 'efectivo', 'turismo', 'kjhjhjk', '2026-01-29 23:31:59', '2026-01-30 20:33:59', NULL),
(56, 4, 13, NULL, '2026-01-30', '2026-01-31', 1, 'completada', 250000.00, 1, 250000.00, 'efectivo', 'turismo', 'Venia con un acompañante', '2026-01-30 20:20:45', '2026-02-02 23:07:40', NULL),
(57, 14, 10, NULL, '2026-01-30', '2026-01-31', 1, 'completada', 50000.00, 1, 50000.00, 'tarjeta', 'turismo', 'Venia solo', '2026-01-30 20:21:46', '2026-02-02 23:07:40', NULL),
(58, 14, 7, NULL, '2026-01-30', '2026-01-31', 3, 'completada', 250000.00, 1, 100000.00, 'efectivo', 'turismo', 'fghfgh', '2026-01-30 20:49:44', '2026-02-02 23:07:40', NULL),
(59, 12, 9, NULL, '2026-02-10', '2026-02-12', NULL, 'cancelada', 100000.00, 2, 200000.00, 'efectivo', 'turismo', '', '2026-02-10 23:47:53', '2026-02-10 23:49:44', NULL),
(60, 14, 5, NULL, '2026-02-11', '2026-02-13', 3, 'cancelada', 150000.00, 2, 300000.00, 'efectivo', 'turismo', 'anotacion de ejemplo\n\nACOMPANANTES:\n[{\"persona_id\":12,\"nombre\":\"juan\",\"apellido\":\"cardenas\",\"tipo_documento\":\"CC\",\"numero_documento\":\"342342342\",\"fecha_nacimiento\":\"2005-06-15\",\"parentesco\":\"\",\"email\":\"jcardenas.@gmail.com\",\"telefono\":\"320234234234\"},{\"persona_id\":11,\"nombre\":\"Juanito\",\"apellido\":\"Rodriguez\",\"tipo_documento\":\"CC\",\"numero_documento\":\"10985672\",\"fecha_nacimiento\":\"2010-03-20\",\"parentesco\":\"\",\"email\":\"juanito@gmail.com\",\"telefono\":\"312345867\"}]', '2026-02-11 16:05:48', '2026-03-03 02:21:15', NULL),
(61, 8, 5, NULL, '2026-02-11', '2026-02-13', 3, 'confirmada', 150000.00, 2, 150000.00, 'efectivo', 'turismo', 'ACOMPANANTES:\n[{\"persona_id\":\"15\",\"nombre\":\"dell\",\"apellido\":\"APP\",\"tipo_documento\":\"Cedula\",\"numero_documento\":\"43324234\",\"fecha_nacimiento\":\"1993-12-12\",\"parentesco\":\"\",\"email\":\"DELL@hotel.com\",\"telefono\":\"123123123\"},{\"persona_id\":4,\"nombre\":\"Pedro\",\"apellido\":\"Mart\\u00ednez\",\"tipo_documento\":\"CC\",\"numero_documento\":\"5544332211\",\"fecha_nacimiento\":null,\"parentesco\":\"\",\"email\":\"pedro.martinez@email.com\",\"telefono\":\"3201239876\"}]', '2026-02-11 22:33:50', '2026-02-12 23:26:27', '2026-02-12 23:26:27'),
(62, 17, 10, NULL, '2026-02-13', '2026-02-15', 2, 'completada', 50000.00, 2, 200000.00, 'efectivo', 'turismo', 'ACOMPANANTES:\n[{\"persona_id\":12,\"nombre\":\"juan\",\"apellido\":\"cardenas\",\"tipo_documento\":\"CC\",\"numero_documento\":\"342342342\",\"fecha_nacimiento\":null,\"parentesco\":\"\",\"email\":\"jcardenas.@gmail.com\",\"telefono\":\"320234234234\"}]', '2026-02-13 21:11:25', '2026-02-15 23:01:29', NULL),
(63, 17, 5, NULL, '2026-02-13', '2026-02-14', 2, 'completada', 150000.00, 1, 150000.00, 'efectivo', 'turismo', 'ACOMPANANTES:\n[{\"persona_id\": 18, \"nombre\": \"dell\", \"apellido\": \"APP\", \"tipo_documento\": \"CC\", \"numero_documento\": \"12345678\", \"fecha_nacimiento\": \"1990-01-01\", \"parentesco\": \"amigo\", \"email\": \"dell@app.com\", \"telefono\": \"123456789\", \"es_menor\": false}]\n\nObservaciones adicionales del cliente', '2026-02-13 21:18:49', '2026-02-14 23:01:23', NULL),
(64, 10, 9, NULL, '2026-02-14', '2026-02-15', 2, 'completada', 100000.00, 1, 100000.00, 'efectivo', 'turismo', 'ACOMPANANTES:\n[{\"persona_id\":3,\"nombre\":\"Ana\",\"apellido\":\"L\\u00f3pez\",\"tipo_documento\":\"CC\",\"numero_documento\":\"153051152\",\"fecha_nacimiento\":null,\"parentesco\":\"\",\"email\":\"ana.lopez@email.com\",\"telefono\":\"3159876543\"}]', '2026-02-14 15:30:48', '2026-02-15 23:01:29', NULL),
(65, 17, 5, NULL, '2026-02-16', '2026-02-17', NULL, 'completada', 150000.00, 1, 150000.00, 'efectivo', 'turismo', '', '2026-02-16 22:06:56', '2026-02-18 19:20:56', NULL),
(66, 15, 9, NULL, '2026-02-16', '2026-02-17', 1, 'completada', 100000.00, 1, 100000.00, 'efectivo', 'turismo', '', '2026-02-16 22:07:18', '2026-02-18 19:20:56', NULL),
(67, 15, 1, NULL, '2026-02-16', '2026-02-17', 1, 'completada', 150000.00, 1, 250000.00, 'efectivo', 'turismo', '', '2026-02-16 22:08:01', '2026-02-18 19:20:56', NULL),
(68, 14, 13, NULL, '2026-02-16', '2026-02-18', 1, 'completada', 250000.00, 2, 500000.00, 'efectivo', 'turismo', '', '2026-02-16 22:08:39', '2026-02-18 23:01:35', NULL),
(69, 12, 5, NULL, '2026-02-18', '2026-02-20', NULL, 'completada', 150000.00, 2, 300000.00, 'efectivo', 'turismo', '', '2026-02-16 22:10:02', '2026-02-24 20:30:46', NULL),
(70, 17, 10, NULL, '2026-02-17', '2026-02-18', 2, 'completada', 50000.00, 1, 50000.00, 'efectivo', 'turismo', 'wffrfgerg 4ert3retgregt\n\nACOMPANANTES:\n[{\"persona_id\":15,\"nombre\":\"dell\",\"apellido\":\"APP\",\"tipo_documento\":\"CC\",\"numero_documento\":\"43324234\",\"fecha_nacimiento\":null,\"parentesco\":\"\",\"email\":\"DELL@hotel.com\",\"telefono\":\"123123123\"}]', '2026-02-17 21:08:16', '2026-02-18 23:01:35', NULL),
(71, 8, 4, NULL, '2026-02-17', '2026-02-18', NULL, 'confirmada', 800000.00, 1, 800000.00, 'efectivo', 'turismo', '', '2026-02-17 21:15:42', '2026-02-17 21:18:08', '2026-02-17 21:18:08'),
(72, 4, 4, NULL, '2026-02-17', '2026-02-18', NULL, 'cancelada', 800000.00, 1, 800000.00, 'efectivo', 'turismo', 'ACOMPANANTES:\n[{\"persona_id\":3,\"nombre\":\"Ana\",\"apellido\":\"L\\u00f3pez\",\"tipo_documento\":\"CC\",\"numero_documento\":\"153051152\",\"fecha_nacimiento\":null,\"parentesco\":\"\",\"email\":\"ana.lopez@email.com\",\"telefono\":\"3159876543\"},{\"persona_id\":2,\"nombre\":\"Juan\",\"apellido\":\"P\\u00e9rez\",\"tipo_documento\":\"CC\",\"numero_documento\":\"0987654321\",\"fecha_nacimiento\":null,\"parentesco\":\"\",\"email\":\"juan.perez@email.com\",\"telefono\":\"3107654321\"}]', '2026-02-17 21:19:45', '2026-02-18 19:20:57', NULL),
(73, 14, 5, NULL, '2026-03-03', '2026-03-04', NULL, 'confirmada', 150000.00, 1, 150000.00, 'efectivo', 'turismo', '', '2026-03-03 02:06:09', '2026-03-03 02:09:33', '2026-03-03 02:09:33'),
(74, 4, 10, NULL, '2026-03-03', '2026-03-04', 3, 'completada', 50000.00, 1, 150000.00, 'efectivo', 'turismo', 'ACOMPANANTES:\n[{\"persona_id\":3,\"nombre\":\"Ana\",\"apellido\":\"L\\u00f3pez\",\"tipo_documento\":\"CC\",\"numero_documento\":\"153051152\",\"fecha_nacimiento\":null,\"parentesco\":\"\",\"email\":\"ana.lopez@email.com\",\"telefono\":\"3159876543\"},{\"persona_id\":2,\"nombre\":\"Juan\",\"apellido\":\"P\\u00e9rez\",\"tipo_documento\":\"CC\",\"numero_documento\":\"0987654321\",\"fecha_nacimiento\":null,\"parentesco\":\"\",\"email\":\"juan.perez@email.com\",\"telefono\":\"3107654321\"}]', '2026-03-03 02:10:07', '2026-03-08 15:12:11', NULL),
(75, 2, 5, NULL, '2026-03-04', '2026-03-05', 2, 'completada', 150000.00, 1, 150000.00, 'efectivo', 'turismo', 'MOTIVO: trabajo\nOBSERVACIONES_GENERALES:\neeeeee\n\nNOTAS_RESERVA:\naaaaa\n\nACOMPANANTES:\n[{\"persona_id\":3,\"nombre\":\"Ana\",\"apellido\":\"L\\u00f3pez\",\"tipo_documento\":\"CC\",\"numero_documento\":\"153051152\",\"fecha_nacimiento\":null,\"parentesco\":\"\",\"email\":\"ana.lopez@email.com\",\"telefono\":\"3159876543\"}]', '2026-03-03 14:01:44', '2026-03-08 15:12:11', NULL),
(76, 17, 5, NULL, '2026-03-10', '2026-03-11', NULL, 'completada', 150000.00, 1, 150000.00, 'efectivo', 'turismo', '', '2026-03-10 14:11:10', '2026-03-13 09:52:39', NULL),
(77, 20, 5, NULL, '2026-03-13', '2026-03-14', NULL, 'completada', 150000.00, 1, 150000.00, 'efectivo', 'turismo', 'MOTIVO: negocios\nOBSERVACIONES_GENERALES:\nobservaciones\n\nNOTAS_RESERVA:\nestas son notas adicionales', '2026-03-13 16:46:41', '2026-03-15 00:34:59', NULL),
(78, 21, 13, NULL, '2026-03-13', '2026-03-14', 1, 'completada', 250000.00, 1, 150000.00, 'tarjeta', 'turismo', 'MOTIVO: trabajo\n\nACOMPANANTES:\n[{\"nombre\":\"PEDRO\",\"apellido\":\"PERRITO\",\"email\":\"pedroperrito@gmail.com\",\"telefono\":\"31456788987\"}]', '2026-03-13 21:55:01', '2026-03-15 00:34:59', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas_eventos`
--

CREATE TABLE `reservas_eventos` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `fecha_reserva` timestamp NOT NULL DEFAULT current_timestamp(),
  `cantidad_personas` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `estado` enum('confirmada','pendiente','cancelada') DEFAULT 'pendiente',
  `metodo_pago` varchar(50) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reserva_huespedes`
--

CREATE TABLE `reserva_huespedes` (
  `id` int(11) NOT NULL,
  `reserva_id` int(11) NOT NULL,
  `persona_id` int(11) NOT NULL,
  `rol_en_reserva` enum('principal','acompanante') NOT NULL DEFAULT 'acompanante',
  `parentesco` varchar(50) DEFAULT NULL,
  `es_menor` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reserva_huespedes`
--

INSERT INTO `reserva_huespedes` (`id`, `reserva_id`, `persona_id`, `rol_en_reserva`, `parentesco`, `es_menor`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 78, 2, 'acompanante', NULL, 0, '2026-03-13 23:42:25', '2026-03-13 23:42:25', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `nivel_acceso` int(11) DEFAULT 1,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `nivel_acceso`, `activo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Administrador', 'Acceso completo al sistema', 100, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27', NULL),
(2, 'Gerente', 'Acceso gerencial a la mayoría de módulos', 50, 1, '2025-12-29 16:29:27', '2025-12-29 22:44:19', NULL),
(3, 'Contador', 'Acceso completo al área contable', 10, 1, '2025-12-29 16:29:27', '2025-12-29 21:47:04', NULL),
(4, 'Auxiliar Contable', 'Acceso limitado al área contable', 30, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27', NULL),
(5, 'Recepcionista', 'Gestión de recepción y reservas', 20, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27', NULL),
(6, 'Limpieza', 'Gestión de limpieza y habitaciones', 10, 1, '2025-12-29 16:29:27', '2025-12-29 16:29:27', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_permisos`
--

CREATE TABLE `roles_permisos` (
  `rol_id` int(11) NOT NULL,
  `permiso_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `roles_permisos`
--

INSERT INTO `roles_permisos` (`rol_id`, `permiso_id`, `created_at`) VALUES
(1, 1, '2025-12-29 16:29:27'),
(1, 2, '2025-12-29 16:29:27'),
(1, 3, '2025-12-29 16:29:27'),
(1, 4, '2025-12-29 16:29:27'),
(1, 5, '2025-12-29 16:29:27'),
(1, 6, '2025-12-29 16:29:27'),
(1, 7, '2025-12-29 16:29:27'),
(1, 8, '2025-12-29 16:29:27'),
(1, 9, '2025-12-29 16:29:27'),
(1, 10, '2025-12-29 16:29:27'),
(1, 11, '2025-12-29 16:29:27'),
(1, 12, '2025-12-29 16:29:27'),
(1, 13, '2025-12-29 16:29:27'),
(1, 14, '2025-12-29 16:29:27'),
(1, 15, '2025-12-29 16:29:27'),
(1, 16, '2025-12-29 16:29:27'),
(1, 17, '2025-12-29 16:29:27'),
(1, 18, '2025-12-29 16:29:27'),
(1, 19, '2025-12-29 16:29:27'),
(1, 20, '2025-12-29 16:29:27'),
(1, 21, '2025-12-29 16:29:27'),
(1, 22, '2025-12-29 16:29:27'),
(1, 23, '2025-12-29 16:29:27'),
(1, 24, '2025-12-29 16:29:27'),
(1, 25, '2025-12-29 16:29:27'),
(1, 26, '2025-12-29 16:29:27'),
(1, 27, '2025-12-29 16:29:27'),
(1, 28, '2025-12-29 16:29:27'),
(1, 29, '2025-12-29 16:29:27'),
(1, 30, '2025-12-29 16:29:27'),
(1, 31, '2025-12-29 16:29:27'),
(1, 32, '2025-12-29 16:29:27'),
(1, 33, '2025-12-29 16:29:27'),
(1, 34, '2025-12-29 16:29:27'),
(1, 35, '2025-12-29 16:29:27'),
(1, 36, '2025-12-29 16:29:27'),
(1, 37, '2025-12-29 16:29:27'),
(1, 38, '2025-12-29 16:29:27'),
(1, 39, '2025-12-29 16:29:27'),
(1, 40, '2025-12-29 16:29:27'),
(1, 41, '2025-12-29 16:29:27'),
(1, 42, '2025-12-29 16:29:27'),
(1, 43, '2025-12-29 16:29:27'),
(1, 44, '2025-12-29 16:29:27'),
(1, 45, '2025-12-29 16:29:27'),
(1, 46, '2025-12-29 16:29:27'),
(1, 47, '2025-12-29 16:29:27'),
(1, 48, '2025-12-29 16:29:27'),
(1, 49, '2025-12-29 16:29:27'),
(1, 50, '2025-12-29 16:29:27'),
(1, 51, '2025-12-29 16:29:27'),
(1, 52, '2025-12-29 16:29:27'),
(1, 53, '2025-12-29 16:29:27'),
(1, 54, '2025-12-29 16:29:27'),
(2, 30, '2025-12-29 22:44:19'),
(3, 41, '2025-12-29 21:47:04'),
(3, 42, '2025-12-29 21:47:04'),
(3, 43, '2025-12-29 21:47:04'),
(3, 44, '2025-12-29 21:47:04'),
(3, 45, '2025-12-29 21:47:04'),
(3, 46, '2025-12-29 21:47:04'),
(3, 47, '2025-12-29 21:47:04'),
(3, 48, '2025-12-29 21:47:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) DEFAULT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sistema_logs`
--

CREATE TABLE `sistema_logs` (
  `id` int(11) NOT NULL,
  `nivel` varchar(20) NOT NULL,
  `mensaje` text NOT NULL,
  `contexto` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`contexto`)),
  `usuario_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_turno`
--

CREATE TABLE `tipos_turno` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `descripcion` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#007bff',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tipos_turno`
--

INSERT INTO `tipos_turno` (`id`, `nombre`, `hora_inicio`, `hora_fin`, `descripcion`, `color`, `activo`, `created_at`) VALUES
(1, 'Mañana', '07:00:00', '15:00:00', 'Turno de mañana (7am - 3pm)', '#28a745', 1, '2025-12-29 16:29:27'),
(2, 'Tarde', '15:00:00', '23:00:00', 'Turno de tarde (3pm - 11pm)', '#ffc107', 1, '2025-12-29 16:29:27'),
(3, 'Noche', '23:00:00', '07:00:00', 'Turno de noche (11pm - 7am)', '#6f42c1', 1, '2025-12-29 16:29:27'),
(4, 'Partido', '06:00:00', '14:00:00', 'Turno partido mañana (6am - 2pm)', '#17a2b8', 1, '2025-12-29 16:29:27'),
(5, 'Partido Tarde', '14:00:00', '22:00:00', 'Turno partido tarde (2pm - 10pm)', '#fd7e14', 1, '2025-12-29 16:29:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transacciones_contables`
--

CREATE TABLE `transacciones_contables` (
  `id` int(11) NOT NULL,
  `numero_comprobante` varchar(50) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` text NOT NULL,
  `tipo_transaccion` enum('ingreso','egreso','traspaso') NOT NULL,
  `monto_total` decimal(15,2) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `referencia_tipo` enum('reserva','pedido','evento','gasto','ajuste') DEFAULT NULL,
  `referencia_id` int(11) DEFAULT NULL,
  `estado` enum('borrador','confirmada','anulada') DEFAULT 'borrador',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `transacciones_contables`
--

INSERT INTO `transacciones_contables` (`id`, `numero_comprobante`, `fecha`, `descripcion`, `tipo_transaccion`, `monto_total`, `usuario_id`, `referencia_tipo`, `referencia_id`, `estado`, `created_at`, `updated_at`) VALUES
(5, 'RES-000023', '2025-12-30', 'Ingreso por reserva - Hab. 103 (simple) - Cliente: Ana López', 'ingreso', 150000.00, 1, 'reserva', 23, 'confirmada', '2025-12-30 23:20:36', '2025-12-30 23:20:36'),
(6, 'RES-000032', '2025-12-30', 'Ingreso por reserva - Hab. 102 (doble) - Cliente: Ana López', 'ingreso', 250000.00, 1, 'reserva', 32, 'confirmada', '2025-12-30 23:20:36', '2025-12-30 23:20:36'),
(7, 'RES-000025', '2025-12-30', 'Ingreso por reserva - Hab. 202 (suite) - Cliente: AFDSF ADSFADS', 'ingreso', 250000.00, 1, 'reserva', 25, 'confirmada', '2025-12-30 23:20:36', '2025-12-30 23:20:36'),
(8, 'RES-000028', '2025-12-30', 'Ingreso por reserva - Hab. 105 (simple) - Cliente: pepito  PEREZ', 'ingreso', 100000.00, 1, 'reserva', 28, 'confirmada', '2025-12-30 23:20:36', '2025-12-30 23:20:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transaccion_detalles`
--

CREATE TABLE `transaccion_detalles` (
  `id` int(11) NOT NULL,
  `transaccion_id` int(11) NOT NULL,
  `cuenta_id` int(11) NOT NULL,
  `tipo_movimiento` enum('debe','haber') NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

CREATE TABLE `turnos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_turno_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada_real` time DEFAULT NULL,
  `hora_salida_real` time DEFAULT NULL,
  `estado` enum('programado','en_curso','completado','ausente') DEFAULT 'programado',
  `notas` text DEFAULT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `rol` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `nacionalidad` varchar(100) DEFAULT 'Colombia',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `rol`, `password`, `telefono`, `nacionalidad`, `activo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Marcos', 'Salazar', 'admin@hotel.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+573152123745', 'Colombia', 1, '2025-11-18 15:24:33', '2026-03-08 13:44:23', NULL),
(2, 'Maria', 'Recepcionista', 'recepcion@hotel.com', 'Recepcionista', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3007654321', 'Colombia', 1, '2025-11-18 15:24:33', '2026-01-29 22:09:41', '2026-01-29 22:09:41'),
(3, 'Juan', 'Gerente', 'gerente@hotel.com', 'Gerente', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3009876543', 'Colombia', 1, '2025-11-18 15:24:33', '2026-01-29 22:09:31', '2026-01-29 22:09:31'),
(4, 'Jhuliet Anghelica', 'Tibasosa Suescun', 'JhulietTibsosa30@gmail.com', 'Recepcionista', '$2y$10$745wuvbaYE2K3/erCt0zXueQQT50MdzwCE8CEFomy1jcXPQxglMGe', '+573224125100', 'Colombia', 1, '2025-11-18 16:54:59', '2026-01-29 22:03:47', '2026-01-29 22:03:47'),
(5, 'Ana', 'PEREZ', 'limpieza@hotel.com', 'limpieza', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3007654322', 'Colombia', 1, '2025-12-09 23:20:17', '2026-01-29 22:09:35', '2026-01-29 22:09:35'),
(6, 'nuevo', 'new', 'new@hotel.com', 'recepcionista', '$2y$10$acb.c30uTI8eSY2krgMKz.6wouhjOIOzFexDOWyY6BSTGUzm1w.9O', '', 'Colombia', 1, '2026-01-28 21:35:02', '2026-01-29 22:09:23', '2026-01-29 22:09:23'),
(7, 'Juan', 'Perez', 'juan@hotel.com', 'auxiliar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555123456', 'Colombia', 1, '2026-01-29 22:15:25', '2026-01-29 22:15:25', NULL),
(8, 'admin2', 'prueba', 'adminprueba@hotel.com', 'admin', '$2y$10$4CgDNCso939kSq2ht1Rmt.vKH3h6sU4ICpcetqmyHX79gs3rb14yi', '23423423', 'Colombia', 1, '2026-01-29 22:32:18', '2026-01-30 20:32:18', NULL),
(9, 'axiliar2', 'prueba', 'auxiliar@hotel.com', 'auxiliar', '$2y$10$jhRr2zNKQ6YzeVAejx6fr.KE9Rv2EqcgK7QmT0G3DhVxCSr7BBWiW', '21432342', 'Colombia', 1, '2026-01-29 22:33:19', '2026-03-02 21:41:48', NULL),
(10, 'PEDRITO', 'ADSFADS', 'pedrito@gmail.com', 'auxiliar', '$2y$10$7zGWG0NPv5zhTPnc07JG/OFkYqdpqPLMoFMOfUxKYmQLfHxvM500i', '3203564489', 'CO', 1, '2026-01-29 23:03:27', '2026-03-02 22:38:30', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_roles`
--

CREATE TABLE `usuarios_roles` (
  `usuario_id` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `asignado_por` int(11) DEFAULT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios_roles`
--

INSERT INTO `usuarios_roles` (`usuario_id`, `rol_id`, `asignado_por`, `fecha_asignacion`) VALUES
(1, 1, NULL, '2025-12-29 16:34:45'),
(2, 5, NULL, '2025-12-29 16:32:19'),
(3, 2, NULL, '2025-12-29 16:32:19'),
(4, 5, NULL, '2025-12-29 16:32:19'),
(5, 6, NULL, '2025-12-29 16:32:19');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `documento` (`documento`),
  ADD KEY `idx_documento` (`documento`),
  ADD KEY `idx_email` (`email`);

--
-- Indices de la tabla `cuentas_contables`
--
ALTER TABLE `cuentas_contables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `cuenta_padre_id` (`cuenta_padre_id`);

--
-- Indices de la tabla `espacios_eventos`
--
ALTER TABLE `espacios_eventos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`);

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
-- Indices de la tabla `hotel_config`
--
ALTER TABLE `hotel_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`),
  ADD KEY `idx_clave` (`clave`);

--
-- Indices de la tabla `metas_hotel`
--
ALTER TABLE `metas_hotel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_mes_anio` (`mes`,`anio`),
  ADD KEY `idx_periodo` (`mes`,`anio`);

--
-- Indices de la tabla `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reserva` (`reserva_id`),
  ADD KEY `idx_fecha` (`fecha_pago`);

--
-- Indices de la tabla `pedidos_productos`
--
ALTER TABLE `pedidos_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `habitacion_id` (`habitacion_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `pedido_productos_detalles`
--
ALTER TABLE `pedido_productos_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`),
  ADD KEY `modulo_id` (`modulo_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

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
-- Indices de la tabla `reservas_eventos`
--
ALTER TABLE `reservas_eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `reserva_huespedes`
--
ALTER TABLE `reserva_huespedes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reserva_id` (`reserva_id`),
  ADD KEY `idx_persona_id` (`persona_id`),
  ADD KEY `idx_rol_en_reserva` (`rol_en_reserva`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  ADD PRIMARY KEY (`rol_id`,`permiso_id`),
  ADD KEY `permiso_id` (`permiso_id`);

--
-- Indices de la tabla `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indices de la tabla `sistema_logs`
--
ALTER TABLE `sistema_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nivel` (`nivel`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indices de la tabla `tipos_turno`
--
ALTER TABLE `tipos_turno`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `transacciones_contables`
--
ALTER TABLE `transacciones_contables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_comprobante` (`numero_comprobante`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `transaccion_detalles`
--
ALTER TABLE `transaccion_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaccion_id` (`transaccion_id`),
  ADD KEY `cuenta_id` (`cuenta_id`);

--
-- Indices de la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `tipo_turno_id` (`tipo_turno_id`),
  ADD KEY `supervisor_id` (`supervisor_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  ADD PRIMARY KEY (`usuario_id`,`rol_id`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `asignado_por` (`asignado_por`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `backups`
--
ALTER TABLE `backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `cuentas_contables`
--
ALTER TABLE `cuentas_contables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `espacios_eventos`
--
ALTER TABLE `espacios_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `hotel_config`
--
ALTER TABLE `hotel_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `metas_hotel`
--
ALTER TABLE `metas_hotel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pedidos_productos`
--
ALTER TABLE `pedidos_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `pedido_productos_detalles`
--
ALTER TABLE `pedido_productos_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de la tabla `reservas_eventos`
--
ALTER TABLE `reservas_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reserva_huespedes`
--
ALTER TABLE `reserva_huespedes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sistema_logs`
--
ALTER TABLE `sistema_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipos_turno`
--
ALTER TABLE `tipos_turno`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `transacciones_contables`
--
ALTER TABLE `transacciones_contables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `transaccion_detalles`
--
ALTER TABLE `transaccion_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cuentas_contables`
--
ALTER TABLE `cuentas_contables`
  ADD CONSTRAINT `cuentas_contables_ibfk_1` FOREIGN KEY (`cuenta_padre_id`) REFERENCES `cuentas_contables` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos_productos`
--
ALTER TABLE `pedidos_productos`
  ADD CONSTRAINT `pedidos_productos_ibfk_1` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`),
  ADD CONSTRAINT `pedidos_productos_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `pedidos_productos_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `pedido_productos_detalles`
--
ALTER TABLE `pedido_productos_detalles`
  ADD CONSTRAINT `pedido_productos_detalles_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos_productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_productos_detalles_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD CONSTRAINT `permisos_ibfk_1` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `reservas_eventos`
--
ALTER TABLE `reservas_eventos`
  ADD CONSTRAINT `reservas_eventos_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_eventos_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  ADD CONSTRAINT `roles_permisos_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `roles_permisos_ibfk_2` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sistema_logs`
--
ALTER TABLE `sistema_logs`
  ADD CONSTRAINT `sistema_logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `transacciones_contables`
--
ALTER TABLE `transacciones_contables`
  ADD CONSTRAINT `transacciones_contables_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `transaccion_detalles`
--
ALTER TABLE `transaccion_detalles`
  ADD CONSTRAINT `transaccion_detalles_ibfk_1` FOREIGN KEY (`transaccion_id`) REFERENCES `transacciones_contables` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaccion_detalles_ibfk_2` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas_contables` (`id`);

--
-- Filtros para la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD CONSTRAINT `turnos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `turnos_ibfk_2` FOREIGN KEY (`tipo_turno_id`) REFERENCES `tipos_turno` (`id`),
  ADD CONSTRAINT `turnos_ibfk_3` FOREIGN KEY (`supervisor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  ADD CONSTRAINT `usuarios_roles_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuarios_roles_ibfk_2` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuarios_roles_ibfk_3` FOREIGN KEY (`asignado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
