-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-08-2025 a las 22:59:47
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
-- Base de datos: `carrent_db`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `check_vehicle_availability` (IN `vehicle_id` VARCHAR(36), IN `pickup_date` DATE, IN `return_date` DATE)   BEGIN
    SELECT COUNT(*) = 0 AS is_available FROM reservations r
    WHERE r.vehicle_id = vehicle_id
    AND r.status != 'cancelada'
    AND (
        (pickup_date BETWEEN r.pickup_date AND r.return_date) OR
        (return_date BETWEEN r.pickup_date AND r.return_date) OR
        (pickup_date <= r.pickup_date AND return_date >= r.return_date)
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `create_reservation` (IN `p_user_id` VARCHAR(36), IN `p_vehicle_id` VARCHAR(36), IN `p_pickup_date` DATE, IN `p_return_date` DATE)   BEGIN
    DECLARE days_count INT;
    DECLARE vehicle_rate DECIMAL(10,2);
    DECLARE total DECIMAL(10,2);
    
    -- Verificar que las fechas son válidas
    IF p_return_date <= p_pickup_date THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La fecha de devolución debe ser posterior a la fecha de recogida';
    END IF;
    
    -- Calcular días de reserva
    SET days_count = DATEDIFF(p_return_date, p_pickup_date);
    
    -- Obtener tarifa diaria del vehículo
    SELECT daily_rate INTO vehicle_rate FROM vehicles WHERE id = p_vehicle_id;
    
    -- Calcular costo total
    SET total = days_count * vehicle_rate;
    
    -- Verificar que el costo total es válido
    IF total <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El costo total debe ser mayor que 0';
    END IF;
    
    -- Crear la reserva
    INSERT INTO reservations (id, user_id, vehicle_id, pickup_date, return_date, total_cost)
    VALUES (UUID(), p_user_id, p_vehicle_id, p_pickup_date, p_return_date, total);
    
    -- Devolver el ID de la reserva creada
    SELECT LAST_INSERT_ID() AS reservation_id, total AS total_cost;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `find_available_vehicles` (IN `p_category` VARCHAR(20), IN `p_location_id` VARCHAR(36), IN `p_pickup_date` DATE, IN `p_return_date` DATE)   BEGIN
    -- Verificar que las fechas son válidas
    IF p_return_date <= p_pickup_date THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La fecha de devolución debe ser posterior a la fecha de recogida';
    END IF;

    SELECT v.* FROM vehicles v
    WHERE v.available = TRUE
    AND (p_category IS NULL OR v.category = p_category)
    AND (p_location_id IS NULL OR v.location_id = p_location_id)
    AND NOT EXISTS (
        SELECT 1 FROM reservations r
        WHERE r.vehicle_id = v.id
        AND r.status != 'cancelada'
        AND (
            (p_pickup_date BETWEEN r.pickup_date AND r.return_date) OR
            (p_return_date BETWEEN r.pickup_date AND r.return_date) OR
            (p_pickup_date <= r.pickup_date AND p_return_date >= r.return_date)
        )
    );
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boats`
--

CREATE TABLE `boats` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `available` tinyint(1) DEFAULT 1,
  `location_id` varchar(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `boats`
--

INSERT INTO `boats` (`id`, `name`, `type`, `capacity`, `daily_rate`, `image`, `description`, `features`, `available`, `location_id`, `created_at`, `updated_at`) VALUES
(4, 'Yate de Lujo', 'Yate', 8, 500.00, 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&q=80', 'Yate de lujo con todas las comodidades', '[\"Cocina completa\", \"Dormitorios\", \"Baño privado\", \"Terraza\"]', 1, '47ce6380-1993-11f0-9b0b-907841162cc6', '2025-05-03 02:42:24', '2025-05-03 02:42:24'),
(5, 'Velero Clásico', 'Velero', 6, 300.00, 'https://images.unsplash.com/photo-1518546305927-5a555bb7020d?auto=format&fit=crop&q=80', 'Velero clásico para navegación tranquila', '[\"Cocina básica\", \"Dormitorios\", \"Baño\"]', 1, '47ce6380-1993-11f0-9b0b-907841162cc6', '2025-05-03 02:42:24', '2025-05-03 02:42:24'),
(6, 'Lancha Rápida', 'Lancha', 4, 200.00, 'https://images.unsplash.com/photo-1501706362039-c06b85d55e70?auto=format&fit=crop&q=81', 'Lancha rápida para aventuras', '[\"Asientos cómodos\", \"Equipo de seguridad\"]', 1, '47ce6380-1993-11f0-9b0b-907841162cc6', '2025-05-03 02:42:24', '2025-05-06 01:40:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `features`
--

CREATE TABLE `features` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `name` varchar(100) NOT NULL,
  `category` enum('vehicle','boat','transfer','general') DEFAULT 'general',
  `icon` varchar(50) DEFAULT 'fas fa-check',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `features`
--

INSERT INTO `features` (`id`, `name`, `category`, `icon`, `active`, `created_at`) VALUES
('3b6395af-869a-11f0-81c7-907841162cc6', 'Leather Seats', 'vehicle', 'fas fa-chair', 1, '2025-08-31 18:42:22'),
('3b63f505-869a-11f0-81c7-907841162cc6', 'Panoramic Roof', 'vehicle', 'fas fa-sun', 1, '2025-08-31 18:42:22'),
('3b6441d7-869a-11f0-81c7-907841162cc6', 'Premium Sound', 'vehicle', 'fas fa-volume-up', 1, '2025-08-31 18:42:22'),
('3b648a79-869a-11f0-81c7-907841162cc6', 'Lane Assist', 'vehicle', 'fas fa-road', 1, '2025-08-31 18:42:22'),
('3b64e3d1-869a-11f0-81c7-907841162cc6', 'Adaptive Cruise Control', 'vehicle', 'fas fa-car', 1, '2025-08-31 18:42:22'),
('3b653f7d-869a-11f0-81c7-907841162cc6', 'GPS Navigation', 'vehicle', 'fas fa-map-marker-alt', 1, '2025-08-31 18:42:22'),
('3b6583a3-869a-11f0-81c7-907841162cc6', '360 Camera', 'vehicle', 'fas fa-camera', 1, '2025-08-31 18:42:22'),
('3b65c4f6-869a-11f0-81c7-907841162cc6', 'Parking Sensors', 'vehicle', 'fas fa-parking', 1, '2025-08-31 18:42:22'),
('3b66072c-869a-11f0-81c7-907841162cc6', 'Bluetooth', 'vehicle', 'fas fa-bluetooth-b', 1, '2025-08-31 18:42:22'),
('3b6649e3-869a-11f0-81c7-907841162cc6', 'Apple CarPlay', 'vehicle', 'fab fa-apple', 1, '2025-08-31 18:42:22'),
('3b66916e-869a-11f0-81c7-907841162cc6', 'Android Auto', 'vehicle', 'fab fa-android', 1, '2025-08-31 18:42:22'),
('3b66d1d7-869a-11f0-81c7-907841162cc6', 'Wireless Charging', 'vehicle', 'fas fa-charging-station', 1, '2025-08-31 18:42:22'),
('3b67189e-869a-11f0-81c7-907841162cc6', 'Heated Seats', 'vehicle', 'fas fa-fire', 1, '2025-08-31 18:42:22'),
('3b675e30-869a-11f0-81c7-907841162cc6', 'Ventilated Seats', 'vehicle', 'fas fa-wind', 1, '2025-08-31 18:42:22'),
('3b67a348-869a-11f0-81c7-907841162cc6', 'Memory Seats', 'vehicle', 'fas fa-memory', 1, '2025-08-31 18:42:22'),
('3b67e434-869a-11f0-81c7-907841162cc6', 'Keyless Entry', 'vehicle', 'fas fa-key', 1, '2025-08-31 18:42:22'),
('3b6826de-869a-11f0-81c7-907841162cc6', 'Push Start', 'vehicle', 'fas fa-power-off', 1, '2025-08-31 18:42:22'),
('3b6865c0-869a-11f0-81c7-907841162cc6', 'Tinted Windows', 'vehicle', 'fas fa-window-maximize', 1, '2025-08-31 18:42:22'),
('3b68a9ae-869a-11f0-81c7-907841162cc6', 'Alloy Wheels', 'vehicle', 'fas fa-circle', 1, '2025-08-31 18:42:22'),
('3b68f381-869a-11f0-81c7-907841162cc6', 'Run Flat Tires', 'vehicle', 'fas fa-tire', 1, '2025-08-31 18:42:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `locations`
--

CREATE TABLE `locations` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `latitude` decimal(10,6) DEFAULT NULL,
  `longitude` decimal(10,6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `locations`
--

INSERT INTO `locations` (`id`, `name`, `address`, `latitude`, `longitude`, `created_at`) VALUES
('47ce4d50-1993-11f0-9b0b-907841162cc6', 'Madrid Centro', 'Calle Gran Vía 1, Madrid', 40.416800, -3.703800, '2025-04-15 00:48:00'),
('47ce61e1-1993-11f0-9b0b-907841162cc6', 'Barcelona Aeropuerto', 'Terminal 1, El Prat de Llobregat', 41.297400, 2.083300, '2025-04-15 00:48:00'),
('47ce6380-1993-11f0-9b0b-907841162cc6', 'Valencia Ciudad', 'Avenida del Puerto 100, Valencia', 39.469900, -0.376300, '2025-04-15 00:48:00'),
('ba843c90-8696-11f0-81c7-907841162cc6', 'Mallorca', 'Calle Raul 12', NULL, NULL, '2025-08-31 18:17:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservations`
--

CREATE TABLE `reservations` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `user_id` varchar(36) NOT NULL,
  `vehicle_id` varchar(36) NOT NULL,
  `pickup_date` date NOT NULL,
  `return_date` date NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `status` enum('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `reservations`
--
DELIMITER $$
CREATE TRIGGER `before_reservation_insert` BEFORE INSERT ON `reservations` FOR EACH ROW BEGIN
    IF NEW.return_date <= NEW.pickup_date THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La fecha de devolución debe ser posterior a la fecha de recogida';
    END IF;
    
    IF NEW.total_cost <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El costo total debe ser mayor que 0';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_reservation_update` BEFORE UPDATE ON `reservations` FOR EACH ROW BEGIN
    IF NEW.return_date <= NEW.pickup_date THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La fecha de devolución debe ser posterior a la fecha de recogida';
    END IF;
    
    IF NEW.total_cost <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El costo total debe ser mayor que 0';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transfers`
--

CREATE TABLE `transfers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `available` tinyint(1) DEFAULT 1,
  `location_id` varchar(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `transfers`
--

INSERT INTO `transfers` (`id`, `name`, `type`, `capacity`, `price`, `image`, `description`, `features`, `available`, `location_id`, `created_at`, `updated_at`) VALUES
(1, 'Limusina Ejecutiva', 'Limusina', 4, 150.00, 'https://images.unsplash.com/photo-1562618817-92b2a2c78399?auto=format&fit=crop&q=80', 'Servicio de transfer ejecutivo', '[\"WiFi\", \"Bebidas\", \"Conductor profesional\"]', 1, '47ce61e1-1993-11f0-9b0b-907841162cc6', '2025-05-03 02:42:27', '2025-05-03 02:42:27'),
(2, 'Minivan Familiar', 'Minivan', 8, 100.00, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&q=80', 'Transfer familiar espacioso', '[\"Asientos cómodos\", \"Aire acondicionado\"]', 1, '47ce4d50-1993-11f0-9b0b-907841162cc6', '2025-05-03 02:42:27', '2025-05-03 02:42:27'),
(3, 'SUV Premium', 'SUV', 6, 120.00, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80', 'Transfer en SUV de lujo', '[\"WiFi\", \"Bebidas\", \"Conductor profesional\"]', 1, '47ce4d50-1993-11f0-9b0b-907841162cc6', '2025-05-03 02:42:27', '2025-05-03 02:42:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `translations`
--

CREATE TABLE `translations` (
  `id` int(11) NOT NULL,
  `entity_type` enum('boat','transfer','vehicle') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `language` varchar(2) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `translations`
--

INSERT INTO `translations` (`id`, `entity_type`, `entity_id`, `language`, `name`, `description`, `features`, `created_at`, `updated_at`) VALUES
(136, 'transfer', 1, 'fr', 'Limousine Exécutive', 'Service de transfert exécutif', '[\"WiFi\", \"Boissons\", \"Chauffeur professionnel\"]', '2025-05-06 03:44:24', '2025-05-06 03:44:24'),
(137, 'transfer', 1, 'en', 'Executive Limousine', 'Executive transfer service', '[\"WiFi\", \"Drinks\", \"Professional Driver\"]', '2025-05-06 03:44:24', '2025-05-06 03:44:24'),
(138, 'transfer', 1, 'de', 'Executive Limousine', 'Executive Transfer-Service', '[\"WLAN\", \"Getränke\", \"Professioneller Fahrer\"]', '2025-05-06 03:44:24', '2025-05-06 03:44:24'),
(139, 'transfer', 2, 'fr', 'Minivan Familial', 'Transfert familial spacieux', '[\"Sièges confortables\", \"Climatisation\"]', '2025-05-06 03:44:24', '2025-05-06 03:44:24'),
(140, 'transfer', 2, 'en', 'Family Minivan', 'Spacious family transfer', '[\"Comfortable Seats\", \"Air Conditioning\"]', '2025-05-06 03:44:24', '2025-05-06 03:44:24'),
(141, 'transfer', 2, 'de', 'Familien-Minivan', 'Geräumiger Familientransfer', '[\"Bequeme Sitze\", \"Klimaanlage\"]', '2025-05-06 03:44:24', '2025-05-06 03:44:24'),
(142, 'transfer', 3, 'fr', 'SUV Premium', 'Transfert en SUV de luxe', '[\"WiFi\", \"Boissons\", \"Chauffeur professionnel\"]', '2025-05-06 03:44:24', '2025-05-06 03:44:24'),
(143, 'transfer', 3, 'en', 'Premium SUV', 'Luxury SUV transfer', '[\"WiFi\", \"Drinks\", \"Professional Driver\"]', '2025-05-06 03:44:24', '2025-05-06 03:44:24'),
(144, 'transfer', 3, 'de', 'Premium SUV', 'Luxus-SUV-Transfer', '[\"WLAN\", \"Getränke\", \"Professioneller Fahrer\"]', '2025-05-06 03:44:24', '2025-05-06 03:44:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`, `role`, `created_at`, `phone`, `address`, `avatar`, `active`) VALUES
('0bdd38da-6fea-11f0-97de-907841162cc6', 'sow.alpha.m@gmail.com', '$2y$10$ZXW6kCYUJVNXWZZU26oZ8.sV/9RH0BHinuxnTqbTv8s4F.EP1w.Lm', 'sow', 'user', '2025-08-02 21:45:46', NULL, NULL, NULL, 1),
('559dce0d-6493-11f0-be8b-907841162cc6', 'admin@devrent.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Principal', 'admin', '2025-07-19 11:27:20', NULL, NULL, NULL, 1),
('559ded0b-6493-11f0-be8b-907841162cc6', 'juan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez', 'user', '2025-07-19 11:27:20', NULL, NULL, NULL, 1),
('559deeea-6493-11f0-be8b-907841162cc6', 'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María García', 'user', '2025-07-19 11:27:20', NULL, NULL, NULL, 1),
('559df13a-6493-11f0-be8b-907841162cc6', 'carlos@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos López', 'user', '2025-07-19 11:27:20', NULL, NULL, NULL, 1),
('559df2ab-6493-11f0-be8b-907841162cc6', 'ana@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana Martínez', 'user', '2025-07-19 11:27:20', NULL, NULL, NULL, 1),
('559df427-6493-11f0-be8b-907841162cc6', 'luis@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Luis Rodríguez', 'user', '2025-07-19 11:27:20', NULL, NULL, NULL, 1),
('559df5c4-6493-11f0-be8b-907841162cc6', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Smith', 'user', '2025-07-19 11:27:20', NULL, NULL, NULL, 1),
('559df680-6493-11f0-be8b-907841162cc6', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Johnson', 'user', '2025-07-19 11:27:20', NULL, NULL, NULL, 1),
('559df71f-6493-11f0-be8b-907841162cc6', 'pierre@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pierre Dubois', 'user', '2025-07-19 11:27:20', NULL, NULL, NULL, 1),
('559df7b2-6493-11f0-be8b-907841162cc6', 'hans@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hans Mueller', 'user', '2025-07-19 11:27:20', NULL, NULL, NULL, 1),
('9b7d13d8-3cf3-11f0-9072-907841162cc6', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin', '2025-05-30 01:15:42', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehicles`
--

CREATE TABLE `vehicles` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `brand` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `year` int(11) NOT NULL,
  `category` enum('económico','familiar','lujo','deportivo') NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `capacity` int(11) DEFAULT 5,
  `image` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '[]' CHECK (json_valid(`features`)),
  `available` tinyint(1) DEFAULT 1,
  `location_id` varchar(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehicles`
--

INSERT INTO `vehicles` (`id`, `brand`, `model`, `year`, `category`, `daily_rate`, `capacity`, `image`, `description`, `features`, `available`, `location_id`, `created_at`) VALUES
('267301f5-8697-11f0-81c7-907841162cc6', 'Mazda', 'KM1', 2019, 'lujo', 120.00, 5, 'https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800', '', '\"\"', 1, 'ba843c90-8696-11f0-81c7-907841162cc6', '2025-08-31 18:20:19'),
('47e11621-1993-11f0-9b0b-907841162cc6', 'BMW', 'Serie 3', 2024, 'lujo', 89.99, 5, 'https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800', 'Sedán de lujo con acabados premium y tecnología de última generación', '[\"GPS Navigation\", \"Leather Seats\", \"Bluetooth\", \"360 Camera\", \"Parking Sensors\"]', 1, '47ce4d50-1993-11f0-9b0b-907841162cc6', '2025-04-15 00:48:00'),
('47e1c185-1993-11f0-9b0b-907841162cc6', 'Volkswagen', 'Golf', 2023, 'económico', 45.99, 5, 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?w=800', 'Compacto eficiente ideal para ciudad', '[\"Bluetooth\", \"Air Conditioning\", \"USB Port\", \"Fuel Efficient\"]', 1, '47ce61e1-1993-11f0-9b0b-907841162cc6', '2025-04-15 00:48:00'),
('47e1c5a1-1993-11f0-9b0b-907841162cc6', 'Mercedes-Benz', 'Clase E', 2024, 'lujo', 129.99, 5, 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=800', 'Experiencia premium con máximo confort', '[\"Leather Seats\", \"Panoramic Roof\", \"Premium Sound\", \"Lane Assist\", \"Adaptive Cruise Control\"]', 1, '47ce4d50-1993-11f0-9b0b-907841162cc6', '2025-04-15 00:48:00'),
('47e1c7cc-1993-11f0-9b0b-907841162cc6', 'Toyota', 'RAV4', 2023, 'familiar', 75.99, 5, 'https://images.unsplash.com/photo-1581540222194-0def2dda95b8?w=800', 'SUV versátil para toda la familia', '[\"7 Seats\", \"Large Trunk\", \"Child Locks\", \"Roof Rails\", \"Safety Package\"]', 1, '47ce6380-1993-11f0-9b0b-907841162cc6', '2025-04-15 00:48:00'),
('47e1c99a-1993-11f0-9b0b-907841162cc6', 'Porsche', '911', 2024, 'deportivo', 249.99, 5, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800', 'Icónico deportivo con prestaciones excepcionales', '[\"Sport Mode\", \"Carbon Fiber\", \"Launch Control\", \"Sport Exhaust\", \"Track Package\"]', 1, '47ce61e1-1993-11f0-9b0b-907841162cc6', '2025-04-15 00:48:00');

--
-- Disparadores `vehicles`
--
DELIMITER $$
CREATE TRIGGER `before_vehicle_insert` BEFORE INSERT ON `vehicles` FOR EACH ROW BEGIN
    IF NEW.year < 2000 OR NEW.year > YEAR(CURRENT_TIMESTAMP) + 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El año del vehículo debe estar entre 2000 y el año actual + 1';
    END IF;
    
    IF NEW.daily_rate <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La tarifa diaria debe ser mayor que 0';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_vehicle_update` BEFORE UPDATE ON `vehicles` FOR EACH ROW BEGIN
    IF NEW.year < 2000 OR NEW.year > YEAR(CURRENT_TIMESTAMP) + 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El año del vehículo debe estar entre 2000 y el año actual + 1';
    END IF;
    
    IF NEW.daily_rate <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La tarifa diaria debe ser mayor que 0';
    END IF;
END
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `boats`
--
ALTER TABLE `boats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indices de la tabla `features`
--
ALTER TABLE `features`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reserv_user` (`user_id`),
  ADD KEY `fk_reserv_vehicle` (`vehicle_id`),
  ADD KEY `idx_reservations_dates` (`pickup_date`,`return_date`),
  ADD KEY `idx_reservations_status` (`status`);

--
-- Indices de la tabla `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indices de la tabla `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_translation` (`entity_type`,`entity_id`,`language`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`);

--
-- Indices de la tabla `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vehicles_category` (`category`),
  ADD KEY `idx_vehicles_location` (`location_id`),
  ADD KEY `idx_vehicles_available` (`available`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `boats`
--
ALTER TABLE `boats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `transfers`
--
ALTER TABLE `transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `translations`
--
ALTER TABLE `translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `boats`
--
ALTER TABLE `boats`
  ADD CONSTRAINT `boats_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`);

--
-- Filtros para la tabla `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reserv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reserv_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `transfers`
--
ALTER TABLE `transfers`
  ADD CONSTRAINT `transfers_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`);

--
-- Filtros para la tabla `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `fk_vehicles_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
