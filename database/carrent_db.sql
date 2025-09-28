-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-09-2025 a las 01:34:12
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
-- Estructura de tabla para la tabla `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `resource_type` varchar(50) DEFAULT NULL,
  `resource_id` varchar(36) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `risk_score` decimal(3,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `available` tinyint(1) DEFAULT 1,
  `location_id` varchar(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `images` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `boats`
--

INSERT INTO `boats` (`id`, `name`, `type`, `capacity`, `daily_rate`, `image`, `description`, `available`, `location_id`, `created_at`, `updated_at`, `images`) VALUES
(4, 'Yate de Lujo', 'Yate', 8, 500.00, 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&q=80', 'Yate de lujo con todas las comodidades', 1, '47ce6380-1993-11f0-9b0b-907841162cc6', '2025-05-03 02:42:24', '2025-09-19 20:50:26', '[\"https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800\", \"https://images.unsplash.com/photo-1540946485063-a40da27545f8?w=800\", \"https://images.unsplash.com/photo-1569263979104-865ab7cd8d13?w=800\"]'),
(5, 'Velero Clásico', 'yate', 6, 300.00, 'https://images.unsplash.com/photo-1567899378494-47b22a2ae96a?w=800', 'Velero clásico para navegación tranquila', 1, '47ce6380-1993-11f0-9b0b-907841162cc6', '2025-05-03 02:42:24', '2025-09-20 22:38:08', '[\"https:\\/\\/images.unsplash.com\\/photo-1567899378494-47b22a2ae96a?w=800\"]'),
(48530000, 'Tiara Yachts', 'yate', 10, 200.00, 'http://localhost/dev-rent/public/images/boats/68bceeb2862c4_1757212338.jpg', '', 1, '47ce6380-1993-11f0-9b0b-907841162cc6', '2025-09-07 01:09:19', '2025-09-07 02:32:21', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `status` enum('pending','sent','failed','bounced') DEFAULT 'pending',
  `message` text DEFAULT NULL,
  `provider_name` varchar(100) DEFAULT NULL,
  `message_id` varchar(255) DEFAULT NULL,
  `response_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `email_logs`
--

INSERT INTO `email_logs` (`id`, `to_email`, `subject`, `status`, `message`, `provider_name`, `message_id`, `response_data`, `created_at`) VALUES
(1, 'prueba.verificacion@example.com', 'Verificación de Email - Dev Rent', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-22 20:23:19'),
(2, 'sow.alpha.m@gmail.com', 'Verificación de Email - Dev Rent', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-22 23:05:15'),
(3, 'sow.alpha.m@gmail.com', 'Verificación de Email - Dev Rent', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-22 23:08:28'),
(4, 'sow.alpha.m@gmail.com', 'Prueba Dev Rent - 2025-09-23 01:09:02', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-22 23:09:02'),
(5, 'sow.alpha.m@gmail.com', 'Prueba Dev Rent - 2025-09-23 01:09:42', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-22 23:09:42'),
(6, 'sow.alpha.m@gmail.com', 'Prueba Dev Rent - 2025-09-23 01:10:35', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-22 23:10:35'),
(7, 'sow.alpha.m@gmail.com', 'Verificación de Email - Dev Rent', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-22 23:12:23'),
(8, 'sow.alpha.m@gmail.com', 'Verificación de Email - Dev Rent', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-22 23:12:49'),
(9, 'sow.alpha.m@gmail.com', 'Verificación de Email - Dev Rent', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-22 23:13:23'),
(10, 'sow.alpha.m@gmail.com', 'Verificación de Email - Dev Rent', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-22 23:13:23'),
(11, 'sow.alpha.m@gmail.com', 'Test Debug', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-22 23:13:56'),
(12, 'sow.alpha.m@gmail.com', 'Verificación de Email - Dev Rent', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-22 23:13:56'),
(13, 'sow.alpha.m@gmail.com', 'Verificación de Email - Dev Rent', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-23 11:02:00'),
(14, 'sow.alpha.m@gmail.com', 'Verificación de Email - Dev Rent', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-23 11:43:39'),
(15, 'test.verification@example.com', 'Verificación de Email - Dev Rent', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-23 11:45:09'),
(16, 'mdou.sow.alpha@gmail.com', 'Verificación de Email - Dev Rent', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-23 12:09:03'),
(17, 'sow.alpha.m@gmail.com', 'Verificación de Email - Dev Rent', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-23 12:15:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `user_id` varchar(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entity_features`
--

CREATE TABLE `entity_features` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `entity_type` enum('vehicle','boat','transfer') NOT NULL,
  `entity_id` varchar(36) NOT NULL,
  `feature_id` varchar(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `entity_features`
--

INSERT INTO `entity_features` (`id`, `entity_type`, `entity_id`, `feature_id`, `created_at`) VALUES
('08090640-8b93-11f0-b6df-00090ffe0001', 'boat', '48530000', 'fc307c17-86b3-11f0-81c7-907841162cc6', '2025-09-07 02:33:25'),
('0809b102-8b93-11f0-b6df-00090ffe0001', 'boat', '48530000', 'c50c8e22-86b0-11f0-81c7-907841162cc6', '2025-09-07 02:33:25'),
('080a1bf8-8b93-11f0-b6df-00090ffe0001', 'boat', '48530000', 'c50c2765-86b0-11f0-81c7-907841162cc6', '2025-09-07 02:33:25'),
('080a9089-8b93-11f0-b6df-00090ffe0001', 'boat', '48530000', 'c50b2647-86b0-11f0-81c7-907841162cc6', '2025-09-07 02:33:25'),
('080afafb-8b93-11f0-b6df-00090ffe0001', 'boat', '48530000', 'fc2d255d-86b3-11f0-81c7-907841162cc6', '2025-09-07 02:33:25'),
('080b6cca-8b93-11f0-b6df-00090ffe0001', 'boat', '48530000', 'fc30d463-86b3-11f0-81c7-907841162cc6', '2025-09-07 02:33:25'),
('080c03fc-8b93-11f0-b6df-00090ffe0001', 'boat', '48530000', 'c50d3269-86b0-11f0-81c7-907841162cc6', '2025-09-07 02:33:25'),
('09c4719f-8c17-11f0-83ec-00090ffe0001', 'transfer', '1', 'c50f1c87-86b0-11f0-81c7-907841162cc6', '2025-09-07 18:18:22'),
('09c560fe-8c17-11f0-83ec-00090ffe0001', 'transfer', '1', 'c50fe145-86b0-11f0-81c7-907841162cc6', '2025-09-07 18:18:22'),
('09c64a3c-8c17-11f0-83ec-00090ffe0001', 'transfer', '1', 'c510612b-86b0-11f0-81c7-907841162cc6', '2025-09-07 18:18:22'),
('09c72ae6-8c17-11f0-83ec-00090ffe0001', 'transfer', '1', 'c5112607-86b0-11f0-81c7-907841162cc6', '2025-09-07 18:18:22'),
('09c812eb-8c17-11f0-83ec-00090ffe0001', 'transfer', '1', 'c511b667-86b0-11f0-81c7-907841162cc6', '2025-09-07 18:18:22'),
('09c8f8b4-8c17-11f0-83ec-00090ffe0001', 'transfer', '1', 'fc32a77c-86b3-11f0-81c7-907841162cc6', '2025-09-07 18:18:22'),
('09c9dc12-8c17-11f0-83ec-00090ffe0001', 'transfer', '1', 'fc32f5b5-86b3-11f0-81c7-907841162cc6', '2025-09-07 18:18:22'),
('09cac04b-8c17-11f0-83ec-00090ffe0001', 'transfer', '1', 'fc334fbf-86b3-11f0-81c7-907841162cc6', '2025-09-07 18:18:22'),
('09cba5fe-8c17-11f0-83ec-00090ffe0001', 'transfer', '1', 'fc33b2e3-86b3-11f0-81c7-907841162cc6', '2025-09-07 18:18:22'),
('1488ee35-8c1e-11f0-83ec-00090ffe0001', 'transfer', '5', 'c5102301-86b0-11f0-81c7-907841162cc6', '2025-09-07 19:08:46'),
('1489d84c-8c1e-11f0-83ec-00090ffe0001', 'transfer', '5', 'fc33b2e3-86b3-11f0-81c7-907841162cc6', '2025-09-07 19:08:46'),
('148a4604-8c1e-11f0-83ec-00090ffe0001', 'transfer', '5', 'c50f1c87-86b0-11f0-81c7-907841162cc6', '2025-09-07 19:08:46'),
('148ab5ce-8c1e-11f0-83ec-00090ffe0001', 'transfer', '5', '3346e158-8c10-11f0-83ec-00090ffe0001', '2025-09-07 19:08:46'),
('148b1fea-8c1e-11f0-83ec-00090ffe0001', 'transfer', '5', 'c510a589-86b0-11f0-81c7-907841162cc6', '2025-09-07 19:08:46'),
('148b8b27-8c1e-11f0-83ec-00090ffe0001', 'transfer', '5', 'fc32a77c-86b3-11f0-81c7-907841162cc6', '2025-09-07 19:08:46'),
('4b7482f3-963d-11f0-a5e0-00090ffe0001', 'vehicle', '47e1c185-1993-11f0-9b0b-907841162cc6', '3b653f7d-869a-11f0-81c7-907841162cc6', '2025-09-20 16:17:24'),
('4b74eba4-963d-11f0-a5e0-00090ffe0001', 'vehicle', '47e1c185-1993-11f0-9b0b-907841162cc6', '3b65c4f6-869a-11f0-81c7-907841162cc6', '2025-09-20 16:17:24'),
('4b753f74-963d-11f0-a5e0-00090ffe0001', 'vehicle', '47e1c185-1993-11f0-9b0b-907841162cc6', '3b66072c-869a-11f0-81c7-907841162cc6', '2025-09-20 16:17:24'),
('4b758c61-963d-11f0-a5e0-00090ffe0001', 'vehicle', '47e1c185-1993-11f0-9b0b-907841162cc6', '3b66916e-869a-11f0-81c7-907841162cc6', '2025-09-20 16:17:24'),
('7b05834b-9672-11f0-9874-00090ffe0001', 'boat', '5', 'fc307c17-86b3-11f0-81c7-907841162cc6', '2025-09-20 22:38:08'),
('7b05f5cb-9672-11f0-9874-00090ffe0001', 'boat', '5', 'c50c8e22-86b0-11f0-81c7-907841162cc6', '2025-09-20 22:38:08'),
('7b064471-9672-11f0-9874-00090ffe0001', 'boat', '5', 'c50c2765-86b0-11f0-81c7-907841162cc6', '2025-09-20 22:38:08'),
('7b0690c8-9672-11f0-9874-00090ffe0001', 'boat', '5', 'fc319a0c-86b3-11f0-81c7-907841162cc6', '2025-09-20 22:38:08'),
('7b06dbcf-9672-11f0-9874-00090ffe0001', 'boat', '5', 'fc30d463-86b3-11f0-81c7-907841162cc6', '2025-09-20 22:38:08'),
('7b0725f7-9672-11f0-9874-00090ffe0001', 'boat', '5', 'c50adb35-86b0-11f0-81c7-907841162cc6', '2025-09-20 22:38:08'),
('7b07716a-9672-11f0-9874-00090ffe0001', 'boat', '5', 'fc312ce7-86b3-11f0-81c7-907841162cc6', '2025-09-20 22:38:08'),
('7b07b908-9672-11f0-9874-00090ffe0001', 'boat', '5', 'fc3256ef-86b3-11f0-81c7-907841162cc6', '2025-09-20 22:38:08'),
('7b07ff88-9672-11f0-9874-00090ffe0001', 'boat', '5', 'c50d3269-86b0-11f0-81c7-907841162cc6', '2025-09-20 22:38:08'),
('843011f8-86ae-11f0-81c7-907841162cc6', 'vehicle', '47e1c5a1-1993-11f0-9b0b-907841162cc6', '3b6395af-869a-11f0-81c7-907841162cc6', '2025-08-31 21:07:35'),
('84306f0d-86ae-11f0-81c7-907841162cc6', 'vehicle', '47e1c5a1-1993-11f0-9b0b-907841162cc6', '3b63f505-869a-11f0-81c7-907841162cc6', '2025-08-31 21:07:35'),
('8430cfd4-86ae-11f0-81c7-907841162cc6', 'vehicle', '47e1c5a1-1993-11f0-9b0b-907841162cc6', '3b6441d7-869a-11f0-81c7-907841162cc6', '2025-08-31 21:07:35'),
('84312f65-86ae-11f0-81c7-907841162cc6', 'vehicle', '47e1c5a1-1993-11f0-9b0b-907841162cc6', '3b648a79-869a-11f0-81c7-907841162cc6', '2025-08-31 21:07:35'),
('84319576-86ae-11f0-81c7-907841162cc6', 'vehicle', '47e1c5a1-1993-11f0-9b0b-907841162cc6', '3b64e3d1-869a-11f0-81c7-907841162cc6', '2025-08-31 21:07:35'),
('a5b51425-8772-11f0-890c-00090ffe0001', 'vehicle', '47e11621-1993-11f0-9b0b-907841162cc6', '3b68a9ae-869a-11f0-81c7-907841162cc6', '2025-09-01 20:31:32'),
('a5b64a5a-8772-11f0-890c-00090ffe0001', 'vehicle', '47e11621-1993-11f0-9b0b-907841162cc6', '3b66072c-869a-11f0-81c7-907841162cc6', '2025-09-01 20:31:32'),
('a5b76680-8772-11f0-890c-00090ffe0001', 'vehicle', '47e11621-1993-11f0-9b0b-907841162cc6', '3b653f7d-869a-11f0-81c7-907841162cc6', '2025-09-01 20:31:32'),
('a5b9a866-8772-11f0-890c-00090ffe0001', 'vehicle', '47e11621-1993-11f0-9b0b-907841162cc6', '3b66916e-869a-11f0-81c7-907841162cc6', '2025-09-01 20:31:32'),
('e6093c1c-86b4-11f0-81c7-907841162cc6', 'boat', '4', 'c50cddb5-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e60981d1-86b4-11f0-81c7-907841162cc6', 'boat', '4', 'fc3209c1-86b3-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e609e4a7-86b4-11f0-81c7-907841162cc6', 'boat', '4', 'fc3027e1-86b3-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e60a303a-86b4-11f0-81c7-907841162cc6', 'boat', '4', 'c50d3269-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e60a71d6-86b4-11f0-81c7-907841162cc6', 'boat', '4', 'fc2e7535-86b3-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e60ab819-86b4-11f0-81c7-907841162cc6', 'boat', '4', 'fc2e29a0-86b3-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e60af923-86b4-11f0-81c7-907841162cc6', 'boat', '4', 'c50b727d-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e60b5828-86b4-11f0-81c7-907841162cc6', 'boat', '4', 'c50adb35-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e60b9c80-86b4-11f0-81c7-907841162cc6', 'boat', '4', 'fc3256ef-86b3-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e60bdeef-86b4-11f0-81c7-907841162cc6', 'boat', '4', 'c50bc6a3-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e60f69ba-86b4-11f0-81c7-907841162cc6', 'boat', '6', 'fc2f2cfe-86b3-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e60fb926-86b4-11f0-81c7-907841162cc6', 'boat', '6', 'c50adb35-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e6100597-86b4-11f0-81c7-907841162cc6', 'boat', '6', 'c50a8695-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e610471a-86b4-11f0-81c7-907841162cc6', 'boat', '6', 'c50d3269-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e61097ae-86b4-11f0-81c7-907841162cc6', 'boat', '6', 'c50b2647-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e610e66f-86b4-11f0-81c7-907841162cc6', 'boat', '6', 'c50d79eb-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e611327c-86b4-11f0-81c7-907841162cc6', 'boat', '6', 'fc312ce7-86b3-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e61170ba-86b4-11f0-81c7-907841162cc6', 'boat', '6', 'fc3027e1-86b3-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e611b84c-86b4-11f0-81c7-907841162cc6', 'boat', '6', 'fc2d8e83-86b3-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e611fcf3-86b4-11f0-81c7-907841162cc6', 'boat', '6', 'c50c2765-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e615e7f1-86b4-11f0-81c7-907841162cc6', 'transfer', '2', 'c5112607-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e6162db4-86b4-11f0-81c7-907841162cc6', 'transfer', '2', 'c510a589-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e6166e6f-86b4-11f0-81c7-907841162cc6', 'transfer', '2', 'c50f1c87-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e616ae76-86b4-11f0-81c7-907841162cc6', 'transfer', '2', 'fc334fbf-86b3-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e616e978-86b4-11f0-81c7-907841162cc6', 'transfer', '2', 'fc32f5b5-86b3-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e617299f-86b4-11f0-81c7-907841162cc6', 'transfer', '2', 'c50ed91a-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e6176d72-86b4-11f0-81c7-907841162cc6', 'transfer', '2', 'c511b667-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e617ada2-86b4-11f0-81c7-907841162cc6', 'transfer', '2', 'c5102301-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e617ec79-86b4-11f0-81c7-907841162cc6', 'transfer', '2', 'c50fe145-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e6188ab3-86b4-11f0-81c7-907841162cc6', 'transfer', '3', 'c5116ff9-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e618c807-86b4-11f0-81c7-907841162cc6', 'transfer', '3', 'c5112607-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e61904c5-86b4-11f0-81c7-907841162cc6', 'transfer', '3', 'c511b667-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e6193e6b-86b4-11f0-81c7-907841162cc6', 'transfer', '3', 'c50f1c87-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e619797a-86b4-11f0-81c7-907841162cc6', 'transfer', '3', 'c510a589-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e619b699-86b4-11f0-81c7-907841162cc6', 'transfer', '3', 'fc334fbf-86b3-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e619eccd-86b4-11f0-81c7-907841162cc6', 'transfer', '3', 'c50ed91a-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('e61a1f78-86b4-11f0-81c7-907841162cc6', 'transfer', '3', 'c50fa139-86b0-11f0-81c7-907841162cc6', '2025-08-31 21:53:16'),
('eec4fe6f-8c18-11f0-83ec-00090ffe0001', 'transfer', '4', 'c50f1c87-86b0-11f0-81c7-907841162cc6', '2025-09-07 18:31:55'),
('eec5ea9a-8c18-11f0-83ec-00090ffe0001', 'transfer', '4', 'c50fe145-86b0-11f0-81c7-907841162cc6', '2025-09-07 18:31:55'),
('eec6b939-8c18-11f0-83ec-00090ffe0001', 'transfer', '4', 'c510612b-86b0-11f0-81c7-907841162cc6', '2025-09-07 18:31:55'),
('eec782f5-8c18-11f0-83ec-00090ffe0001', 'transfer', '4', 'c5112607-86b0-11f0-81c7-907841162cc6', '2025-09-07 18:31:55'),
('eec842b6-8c18-11f0-83ec-00090ffe0001', 'transfer', '4', 'c511b667-86b0-11f0-81c7-907841162cc6', '2025-09-07 18:31:55'),
('eec9055b-8c18-11f0-83ec-00090ffe0001', 'transfer', '4', 'fc32a77c-86b3-11f0-81c7-907841162cc6', '2025-09-07 18:31:55'),
('eec9b557-8c18-11f0-83ec-00090ffe0001', 'transfer', '4', 'fc32f5b5-86b3-11f0-81c7-907841162cc6', '2025-09-07 18:31:55'),
('eeca6db7-8c18-11f0-83ec-00090ffe0001', 'transfer', '4', 'fc334fbf-86b3-11f0-81c7-907841162cc6', '2025-09-07 18:31:55'),
('eecb2ca7-8c18-11f0-83ec-00090ffe0001', 'transfer', '4', 'fc33b2e3-86b3-11f0-81c7-907841162cc6', '2025-09-07 18:31:55');

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
('3346def7-8c10-11f0-83ec-00090ffe0001', 'Bebidas Incluidas', 'transfer', 'fas fa-coffee', 1, '2025-09-07 17:29:25'),
('3346e116-8c10-11f0-83ec-00090ffe0001', 'Asientos C??modos', 'transfer', 'fas fa-chair', 1, '2025-09-07 17:29:25'),
('3346e158-8c10-11f0-83ec-00090ffe0001', 'Equipaje Incluido', 'transfer', 'fas fa-suitcase', 1, '2025-09-07 17:29:25'),
('3b6395af-869a-11f0-81c7-907841162cc6', 'Leather Seats', 'vehicle', 'fas fa-chair', 1, '2025-08-31 18:42:22'),
('3b63f505-869a-11f0-81c7-907841162cc6', 'Panoramic Roof', 'vehicle', 'fas fa-sun', 1, '2025-08-31 18:42:22'),
('3b6441d7-869a-11f0-81c7-907841162cc6', 'Premium Sound', 'vehicle', 'fas fa-volume-up', 1, '2025-08-31 18:42:22'),
('3b648a79-869a-11f0-81c7-907841162cc6', 'Lane Assist', 'vehicle', 'fas fa-road', 1, '2025-08-31 18:42:22'),
('3b64e3d1-869a-11f0-81c7-907841162cc6', 'Adaptive Cruise Control', 'vehicle', 'fas fa-car', 1, '2025-08-31 18:42:22'),
('3b653f7d-869a-11f0-81c7-907841162cc6', 'GPS Navigation', 'vehicle', 'fas fa-map-marker-alt', 1, '2025-08-31 18:42:22'),
('3b6583a3-869a-11f0-81c7-907841162cc6', '360 Camera', 'vehicle', 'fas fa-camera', 1, '2025-08-31 18:42:22'),
('3b65c4f6-869a-11f0-81c7-907841162cc6', 'Parking Sensors', 'vehicle', 'fas fa-parking', 1, '2025-08-31 18:42:22'),
('3b66072c-869a-11f0-81c7-907841162cc6', 'Bluetooth', 'vehicle', 'fas fa-music', 1, '2025-08-31 18:42:22'),
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
('3b68f381-869a-11f0-81c7-907841162cc6', 'Run Flat Tires', 'vehicle', 'fas fa-tire', 1, '2025-08-31 18:42:22'),
('47d69672-9b7b-11f0-b97f-00090ffe0001', 'Híbrido', 'vehicle', 'fas fa-check', 1, '2025-09-27 08:23:43'),
('c50a8695-86b0-11f0-81c7-907841162cc6', 'Cocina completa', 'boat', 'fas fa-utensils', 1, '2025-08-31 21:23:42'),
('c50adb35-86b0-11f0-81c7-907841162cc6', 'Dormitorios', 'boat', 'fas fa-bed', 1, '2025-08-31 21:23:42'),
('c50b2647-86b0-11f0-81c7-907841162cc6', 'Baño privado', 'boat', 'fas fa-bath', 1, '2025-08-31 21:23:42'),
('c50b727d-86b0-11f0-81c7-907841162cc6', 'Terraza', 'boat', 'fas fa-umbrella-beach', 1, '2025-08-31 21:23:42'),
('c50bc6a3-86b0-11f0-81c7-907841162cc6', 'Cocina básica', 'boat', 'fas fa-fire', 1, '2025-08-31 21:23:42'),
('c50c2765-86b0-11f0-81c7-907841162cc6', 'Baño', 'boat', 'fas fa-toilet', 1, '2025-08-31 21:23:42'),
('c50c8e22-86b0-11f0-81c7-907841162cc6', 'Asientos cómodos', 'boat', 'fas fa-chair', 1, '2025-08-31 21:23:42'),
('c50cddb5-86b0-11f0-81c7-907841162cc6', 'Equipo de seguridad', 'boat', 'fas fa-life-ring', 1, '2025-08-31 21:23:42'),
('c50d3269-86b0-11f0-81c7-907841162cc6', 'WiFi', 'boat', 'fas fa-wifi', 1, '2025-08-31 21:23:42'),
('c50d79eb-86b0-11f0-81c7-907841162cc6', 'Sistema de sonido', 'boat', 'fas fa-volume-up', 1, '2025-08-31 21:23:42'),
('c50dd363-86b0-11f0-81c7-907841162cc6', 'Equipo de pesca', 'boat', 'fas fa-fish', 1, '2025-08-31 21:23:42'),
('c50e1d71-86b0-11f0-81c7-907841162cc6', 'Equipo de buceo', 'boat', 'fas fa-swimming-pool', 1, '2025-08-31 21:23:42'),
('c50ed91a-86b0-11f0-81c7-907841162cc6', 'Bebidas', 'transfer', 'fas fa-glass-martini', 1, '2025-08-31 21:23:42'),
('c50f1c87-86b0-11f0-81c7-907841162cc6', 'Conductor profesional', 'transfer', 'fas fa-id-badge', 1, '2025-08-31 21:23:42'),
('c50fa139-86b0-11f0-81c7-907841162cc6', 'Aire acondicionado', 'transfer', 'fas fa-fan', 1, '2025-08-31 21:23:42'),
('c50fe145-86b0-11f0-81c7-907841162cc6', 'TV', 'transfer', 'fas fa-tv', 1, '2025-08-31 21:23:42'),
('c5102301-86b0-11f0-81c7-907841162cc6', 'Carga de equipaje', 'transfer', 'fas fa-suitcase', 1, '2025-08-31 21:23:42'),
('c510612b-86b0-11f0-81c7-907841162cc6', 'Servicio 24/7', 'transfer', 'fas fa-clock', 1, '2025-08-31 21:23:42'),
('c510a589-86b0-11f0-81c7-907841162cc6', 'Reserva online', 'transfer', 'fas fa-calendar-check', 1, '2025-08-31 21:23:42'),
('c5112607-86b0-11f0-81c7-907841162cc6', 'Pago con tarjeta', 'transfer', 'fas fa-credit-card', 1, '2025-08-31 21:23:42'),
('c5116ff9-86b0-11f0-81c7-907841162cc6', 'Cancelación gratuita', 'transfer', 'fas fa-times-circle', 1, '2025-08-31 21:23:42'),
('c511b667-86b0-11f0-81c7-907841162cc6', 'Seguro de viaje', 'transfer', 'fas fa-shield-alt', 1, '2025-08-31 21:23:42'),
('cc7add90-9b7d-11f0-b97f-00090ffe0001', 'GPS', 'vehicle', 'fas fa-location-arrow', 1, '2025-09-27 08:41:44'),
('cc7b4f9b-9b7d-11f0-b97f-00090ffe0001', 'Automático', 'vehicle', 'fas fa-cogs', 1, '2025-09-27 08:41:45'),
('cc7c9b3d-9b7d-11f0-b97f-00090ffe0001', 'Tripulación incluida', 'boat', 'fas fa-user-friends', 1, '2025-09-27 08:41:45'),
('cc7d7d5d-9b7d-11f0-b97f-00090ffe0001', 'Cabina', 'boat', 'fas fa-bed', 1, '2025-09-27 08:41:45'),
('cc7f2ecc-9b7d-11f0-b97f-00090ffe0001', 'Agua y snacks', 'transfer', 'fas fa-glass-water', 1, '2025-09-27 08:41:45'),
('cc7fa3c9-9b7d-11f0-b97f-00090ffe0001', 'Wi-Fi', 'transfer', 'fas fa-wifi', 1, '2025-09-27 08:41:45'),
('fc2d255d-86b3-11f0-81c7-907841162cc6', 'Casco de fibra de vidrio', 'boat', 'fas fa-ship', 1, '2025-08-31 21:46:43'),
('fc2d8e83-86b3-11f0-81c7-907841162cc6', 'Motor fuera de borda', 'boat', 'fas fa-cog', 1, '2025-08-31 21:46:43'),
('fc2dd732-86b3-11f0-81c7-907841162cc6', 'Sistema de navegación GPS', 'boat', 'fas fa-map-marker-alt', 1, '2025-08-31 21:46:43'),
('fc2e29a0-86b3-11f0-81c7-907841162cc6', 'Radio marina VHF', 'boat', 'fas fa-broadcast-tower', 1, '2025-08-31 21:46:43'),
('fc2e7535-86b3-11f0-81c7-907841162cc6', 'Equipo de pesca incluido', 'boat', 'fas fa-fish', 1, '2025-08-31 21:46:43'),
('fc2ed3bd-86b3-11f0-81c7-907841162cc6', 'Escalera de baño', 'boat', 'fas fa-swimming-pool', 1, '2025-08-31 21:46:43'),
('fc2f2cfe-86b3-11f0-81c7-907841162cc6', 'Cubierta de sol', 'boat', 'fas fa-umbrella-beach', 1, '2025-08-31 21:46:43'),
('fc2f9e34-86b3-11f0-81c7-907841162cc6', 'Nevera portátil', 'boat', 'fas fa-snowflake', 1, '2025-08-31 21:46:43'),
('fc3027e1-86b3-11f0-81c7-907841162cc6', 'Equipo de snorkel', 'boat', 'fas fa-water', 1, '2025-08-31 21:46:43'),
('fc307c17-86b3-11f0-81c7-907841162cc6', 'Ancla de seguridad', 'boat', 'fas fa-anchor', 1, '2025-08-31 21:46:43'),
('fc30d463-86b3-11f0-81c7-907841162cc6', 'Chalecos salvavidas', 'boat', 'fas fa-life-ring', 1, '2025-08-31 21:46:43'),
('fc312ce7-86b3-11f0-81c7-907841162cc6', 'Extintor de incendios', 'boat', 'fas fa-fire-extinguisher', 1, '2025-08-31 21:46:43'),
('fc319a0c-86b3-11f0-81c7-907841162cc6', 'Botiquín de primeros auxilios', 'boat', 'fas fa-first-aid', 1, '2025-08-31 21:46:43'),
('fc3209c1-86b3-11f0-81c7-907841162cc6', 'Luces de navegación LED', 'boat', 'fas fa-lightbulb', 1, '2025-08-31 21:46:43'),
('fc3256ef-86b3-11f0-81c7-907841162cc6', 'Sistema de sonido Bluetooth', 'boat', 'fas fa-music', 1, '2025-08-31 21:46:43'),
('fc32a77c-86b3-11f0-81c7-907841162cc6', 'Vehículo de lujo', 'transfer', 'fas fa-car', 1, '2025-08-31 21:46:43'),
('fc32f5b5-86b3-11f0-81c7-907841162cc6', 'Conductor uniformado', 'transfer', 'fas fa-user-tie', 1, '2025-08-31 21:46:43'),
('fc334fbf-86b3-11f0-81c7-907841162cc6', 'WiFi gratuito', 'transfer', 'fas fa-wifi', 1, '2025-08-31 21:46:43'),
('fc33b2e3-86b3-11f0-81c7-907841162cc6', 'Cargador USB', 'transfer', 'fas fa-charging-station', 1, '2025-08-31 21:46:43');

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
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `token` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_id`, `token`, `email`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 'user_68d444420bf5b7.81305862', '1df8b51b2bbc949422904106f3bba5ffb1a340f248ffe80d5c1b7eac1f98c1ca', 'mdou.sow.alpha@gmail.com', '2025-09-25 18:35:58', '2025-09-25 18:35:58', '2025-09-25 18:28:16'),
(2, 'user_68d4547ccdfd28.62286863', '2cdf3df77258a7e2a842459bfef9b3a78f30f8575ab4c6d41306c40589f5be77', 'sow.alpha.m@gmail.com', '2025-09-25 19:11:51', '2025-09-25 19:11:51', '2025-09-25 18:30:57'),
(3, 'user_68d444420bf5b7.81305862', '4d99c026e7083ddad821e246f7f2906bf7d1dbbc85df1b68e89ae10619085bff', 'mdou.sow.alpha@gmail.com', '2025-09-25 19:19:02', '2025-09-25 19:19:02', '2025-09-25 18:35:58'),
(4, 'user_68d4547ccdfd28.62286863', '6a5b090c7a99df85091425c9502fa1b441be526698bf40ab3d1b5bfef885c422', 'sow.alpha.m@gmail.com', '2025-09-25 19:36:27', '2025-09-25 19:36:27', '2025-09-25 19:11:51'),
(5, 'user_68d444420bf5b7.81305862', '83b338e8beb51c87a7ac60d078fd0250529f6e333594769b5f7c8dd7ee896855', 'mdou.sow.alpha@gmail.com', '2025-09-25 19:27:16', '2025-09-25 19:27:16', '2025-09-25 19:19:02'),
(6, 'user_68d444420bf5b7.81305862', 'c92308ec0dbe1277194b71ae683019b0f4ca3ecbe0ff668dc5e5ddeb5be234c3', 'mdou.sow.alpha@gmail.com', '2025-09-26 19:27:16', NULL, '2025-09-25 19:27:16'),
(7, 'user_68d4547ccdfd28.62286863', 'b866e45001a1b73d89c87f91950fb6be16d73282d8c1f8fb08dfe295187b0f4d', 'sow.alpha.m@gmail.com', '2025-09-25 19:40:19', '2025-09-25 19:40:19', '2025-09-25 19:36:27'),
(8, 'user_68d4547ccdfd28.62286863', 'ab04ee051b2df0469031d29865eeb64b7ee57e03508c935f0d90cee293e3301f', 'sow.alpha.m@gmail.com', '2025-09-25 19:42:50', '2025-09-25 19:42:50', '2025-09-25 19:40:19'),
(9, 'user_68d4547ccdfd28.62286863', '22750828a12b6063656b1fb646f3808ff6b8c0d5b4013bce31ed88e71c08f87e', 'sow.alpha.m@gmail.com', '2025-09-25 20:17:25', '2025-09-25 20:17:25', '2025-09-25 19:42:50'),
(10, 'user_68d4547ccdfd28.62286863', 'fa8cc240e8d6f7ea78a540f0575772ac84a87db79fd258f2d3485b65093e45a5', 'sow.alpha.m@gmail.com', '2025-09-25 20:23:01', '2025-09-25 20:23:01', '2025-09-25 20:18:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provider_configs`
--

CREATE TABLE `provider_configs` (
  `id` int(11) NOT NULL,
  `provider_name` varchar(100) NOT NULL,
  `provider_type` enum('email','sms') NOT NULL,
  `config_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`config_data`)),
  `is_active` tinyint(1) DEFAULT 1,
  `priority` int(11) DEFAULT 1,
  `reliability_score` decimal(3,2) DEFAULT 1.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `provider_configs`
--

INSERT INTO `provider_configs` (`id`, `provider_name`, `provider_type`, `config_data`, `is_active`, `priority`, `reliability_score`, `created_at`, `updated_at`) VALUES
(1, 'Gmail SMTP', 'email', '{\"host\":\"smtp.gmail.com\",\"port\":587,\"encryption\":\"tls\",\"username\":\"\",\"password\":\"\"}', 1, 1, 1.00, '2025-09-22 12:37:39', '2025-09-22 12:37:39'),
(2, 'Outlook SMTP', 'email', '{\"host\":\"smtp-mail.outlook.com\",\"port\":587,\"encryption\":\"tls\",\"username\":\"\",\"password\":\"\"}', 1, 2, 0.95, '2025-09-22 12:37:39', '2025-09-22 12:37:39'),
(3, 'Twilio SMS', 'sms', '{\"account_sid\":\"\",\"auth_token\":\"\",\"phone_number\":\"\"}', 1, 1, 1.00, '2025-09-22 12:37:39', '2025-09-22 12:37:39'),
(4, 'Nexmo SMS', 'sms', '{\"api_key\":\"\",\"api_secret\":\"\",\"from\":\"\"}', 1, 2, 0.90, '2025-09-22 12:37:39', '2025-09-22 12:37:39'),
(5, 'Gmail SMTP', 'email', '{\"host\":\"smtp.gmail.com\",\"port\":587,\"encryption\":\"tls\",\"username\":\"tu-email@gmail.com\",\"password\":\"tu-app-password\",\"from_name\":\"Dev Rent\"}', 1, 1, 1.00, '2025-09-22 18:22:51', '2025-09-22 18:22:51'),
(6, 'Outlook SMTP', 'email', '{\"host\":\"smtp-mail.outlook.com\",\"port\":587,\"encryption\":\"tls\",\"username\":\"tu-email@outlook.com\",\"password\":\"tu-password\",\"from_name\":\"Dev Rent\"}', 1, 2, 0.95, '2025-09-22 18:22:51', '2025-09-22 18:22:51'),
(7, 'SendGrid SMTP', 'email', '{\"host\":\"smtp.sendgrid.net\",\"port\":587,\"encryption\":\"tls\",\"username\":\"apikey\",\"password\":\"tu-sendgrid-api-key\",\"from_name\":\"Dev Rent\"}', 1, 3, 0.90, '2025-09-22 18:22:51', '2025-09-22 18:22:51'),
(8, 'Twilio SMS', 'sms', '{\"account_sid\":\"tu-account-sid\",\"auth_token\":\"tu-auth-token\",\"phone_number\":\"+1234567890\",\"webhook_url\":\"http://localhost/dev-rent/webhooks/twilio\"}', 1, 1, 1.00, '2025-09-22 18:22:51', '2025-09-22 18:22:51'),
(9, 'Nexmo SMS', 'sms', '{\"api_key\":\"tu-nexmo-api-key\",\"api_secret\":\"tu-nexmo-api-secret\",\"from\":\"+1234567890\"}', 1, 2, 0.90, '2025-09-22 18:22:51', '2025-09-22 18:22:51'),
(10, 'AWS SNS', 'sms', '{\"region\":\"us-east-1\",\"access_key\":\"tu-aws-access-key\",\"secret_key\":\"tu-aws-secret-key\",\"topic_arn\":\"arn:aws:sns:us-east-1:123456789012:dev-rent-sms\"}', 1, 3, 0.85, '2025-09-22 18:22:51', '2025-09-22 18:22:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservations`
--

CREATE TABLE `reservations` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `user_id` varchar(36) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `entity_type` enum('vehicle','boat','transfer') NOT NULL DEFAULT 'vehicle',
  `entity_id` varchar(36) NOT NULL,
  `pickup_location_id` varchar(36) DEFAULT NULL,
  `return_location_id` varchar(36) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `pickup_date` date NOT NULL,
  `return_date` date NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `status` enum('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
-- Estructura de tabla para la tabla `security_logs`
--

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` int(11) NOT NULL,
  `to_phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','sent','failed','delivered') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `provider_name` varchar(100) DEFAULT NULL,
  `message_id` varchar(255) DEFAULT NULL,
  `response_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sms_logs`
--

INSERT INTO `sms_logs` (`id`, `to_phone`, `message`, `status`, `error_message`, `provider_name`, `message_id`, `response_data`, `created_at`) VALUES
(1, '+34612345678', 'tu código de verificación para Dev Rent es: 733866. Válido por 5 minutos. No compartas este código.', '', 'No hay proveedores disponibles', NULL, NULL, NULL, '2025-09-22 20:23:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_config`
--

CREATE TABLE `system_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_type` varchar(50) DEFAULT 'string',
  `is_encrypted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `system_config`
--

INSERT INTO `system_config` (`id`, `config_key`, `config_value`, `description`, `created_at`, `updated_at`, `config_type`, `is_encrypted`) VALUES
(1, 'email_verification_enabled', '1', 'Habilitar verificación de email', '2025-09-18 19:04:33', '2025-09-18 19:04:33', 'string', 0),
(2, 'phone_verification_enabled', '1', 'Habilitar verificación de teléfono', '2025-09-18 19:04:33', '2025-09-18 19:04:33', 'string', 0),
(3, 'recaptcha_enabled', '1', 'Habilitar reCAPTCHA', '2025-09-18 19:04:33', '2025-09-18 19:04:33', 'string', 0),
(4, 'verification_token_expiry', '3600', 'Tiempo de expiración del token de verificación en segundos', '2025-09-18 19:04:33', '2025-09-18 19:04:33', 'string', 0),
(5, 'phone_code_expiry', '300', 'Tiempo de expiración del código OTP en segundos', '2025-09-18 19:04:33', '2025-09-18 19:04:33', 'string', 0),
(6, 'max_verification_attempts', '3', 'Máximo número de intentos de verificación', '2025-09-18 19:04:33', '2025-09-21 10:22:31', 'string', 0),
(7, 'kickbox_api_key', '', 'API Key de Kickbox para validación de emails', '2025-09-18 19:04:33', '2025-09-18 19:04:33', 'string', 0),
(8, 'twilio_account_sid', 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'Account SID de Twilio', '2025-09-18 19:04:33', '2025-09-23 19:38:09', 'string', 0),
(9, 'twilio_auth_token', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'Auth Token de Twilio', '2025-09-18 19:04:33', '2025-09-23 19:38:09', 'string', 1),
(10, 'twilio_phone_number', '+1234567890', 'Número de teléfono de Twilio', '2025-09-18 19:04:33', '2025-09-23 19:38:09', 'string', 0),
(11, 'recaptcha_site_key', '', 'Site Key de reCAPTCHA', '2025-09-18 19:04:33', '2025-09-18 19:04:33', 'string', 0),
(12, 'recaptcha_secret_key', '', 'Secret Key de reCAPTCHA', '2025-09-18 19:04:33', '2025-09-18 19:04:33', 'string', 0),
(13, 'smtp_host', 'smtp.gmail.com', NULL, '2025-09-21 10:22:31', '2025-09-25 19:27:11', 'string', 0),
(14, 'smtp_port', '587', NULL, '2025-09-21 10:22:31', '2025-09-25 19:27:11', 'string', 0),
(15, 'smtp_username', 'sow.alpha.m@gmail.com', NULL, '2025-09-21 10:22:31', '2025-09-25 19:27:11', 'string', 0),
(16, 'smtp_password', 'dgmuwljtcmvasibn', NULL, '2025-09-21 10:22:31', '2025-09-25 19:27:11', 'string', 1),
(17, 'smtp_encryption', 'tls', NULL, '2025-09-21 10:22:31', '2025-09-25 19:27:11', 'string', 0),
(18, 'email_from_name', 'Dev Rent', NULL, '2025-09-21 10:22:31', '2025-09-25 19:27:11', 'string', 0),
(19, 'email_from_email', 'sow.alpha.m@gmail.com', NULL, '2025-09-21 10:22:31', '2025-09-25 19:27:11', 'string', 0),
(20, 'email_reply_to', 'sow.alpha.m@gmail.com', NULL, '2025-09-21 10:22:31', '2025-09-25 19:27:11', 'string', 0),
(21, 'use_smtp', '1', NULL, '2025-09-21 10:22:31', '2025-09-25 19:27:11', 'string', 0),
(22, 'email_backup_method', 'mail_native', NULL, '2025-09-21 10:22:31', '2025-09-21 10:23:03', 'string', 0),
(26, 'twilio_enabled', '1', NULL, '2025-09-21 10:22:31', '2025-09-21 10:23:36', 'string', 0),
(27, 'sms_backup_method', 'email_notification', NULL, '2025-09-21 10:22:31', '2025-09-21 10:23:36', 'string', 0),
(28, 'verification_required', '1', NULL, '2025-09-21 10:22:31', '2025-09-21 10:22:31', 'string', 0),
(29, 'email_verification_required', '1', NULL, '2025-09-21 10:22:31', '2025-09-21 10:22:31', 'string', 0),
(30, 'phone_verification_required', '0', NULL, '2025-09-21 10:22:31', '2025-09-21 10:22:31', 'string', 0),
(31, 'existing_users_exempt', '1', NULL, '2025-09-21 10:22:31', '2025-09-21 10:22:31', 'string', 0),
(32, 'verification_timeout_hours', '24', NULL, '2025-09-21 10:22:31', '2025-09-21 10:22:31', 'string', 0),
(57, 'monitoring_enabled', '1', NULL, '2025-09-22 12:37:39', '2025-09-22 12:37:39', 'boolean', 0),
(58, 'encryption_algorithm', 'AES-256-GCM', NULL, '2025-09-22 12:37:39', '2025-09-22 12:37:39', 'string', 0),
(59, 'password_min_length', '12', NULL, '2025-09-22 12:37:39', '2025-09-22 12:37:39', 'integer', 0),
(60, 'rate_limit_enabled', '1', NULL, '2025-09-22 12:37:39', '2025-09-22 12:37:39', 'boolean', 0),
(61, 'fraud_detection_enabled', '1', NULL, '2025-09-22 12:37:39', '2025-09-22 12:37:39', 'boolean', 0),
(62, 'audit_retention_days', '2555', NULL, '2025-09-22 12:37:39', '2025-09-22 12:37:39', 'integer', 0),
(63, 'security_retention_days', '365', NULL, '2025-09-22 12:37:39', '2025-09-22 12:37:39', 'integer', 0),
(69, 'smtp_from_name', 'Dev', NULL, '2025-09-22 18:22:51', '2025-09-22 23:04:40', 'string', 0),
(70, 'smtp_outlook_host', 'smtp-mail.outlook.com', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(71, 'smtp_outlook_port', '587', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(72, 'smtp_outlook_username', 'tu-email@outlook.com', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(73, 'smtp_outlook_password', 'tu-password-outlook', NULL, '2025-09-22 18:22:51', '2025-09-22 18:33:42', 'string', 1),
(74, 'smtp_outlook_encryption', 'tls', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(75, 'smtp_sendgrid_host', 'smtp.sendgrid.net', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(76, 'smtp_sendgrid_port', '587', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(77, 'smtp_sendgrid_username', 'apikey', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(78, 'smtp_sendgrid_password', 'tu-sendgrid-api-key', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 1),
(79, 'smtp_sendgrid_encryption', 'tls', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(83, 'twilio_webhook_url', 'http://localhost/dev-rent/webhooks/twilio', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(84, 'nexmo_api_key', 'tu-nexmo-api-key', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(85, 'nexmo_api_secret', 'tu-nexmo-api-secret', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 1),
(86, 'nexmo_from_number', '+1234567890', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(87, 'aws_sns_region', 'us-east-1', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(88, 'aws_sns_access_key', 'tu-aws-access-key', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(89, 'aws_sns_secret_key', 'tu-aws-secret-key', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 1),
(90, 'aws_sns_topic_arn', 'arn:aws:sns:us-east-1:123456789012:dev-rent-sms', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(92, 'monitoring_interval', '300', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'integer', 0),
(93, 'alert_email', 'admin@dev-rent.com', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(94, 'alert_phone', '+1234567890', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(95, 'health_check_url', 'http://localhost/dev-rent/health', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(96, 'uptime_monitoring', '1', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'boolean', 0),
(97, 'performance_monitoring', '1', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'boolean', 0),
(98, 'error_tracking', '1', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'boolean', 0),
(101, 'password_require_uppercase', '1', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'boolean', 0),
(102, 'password_require_lowercase', '1', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'boolean', 0),
(103, 'password_require_numbers', '1', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'boolean', 0),
(104, 'password_require_symbols', '1', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'boolean', 0),
(106, 'rate_limit_requests_per_minute', '60', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'integer', 0),
(108, 'session_timeout', '3600', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'integer', 0),
(109, 'max_login_attempts', '5', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'integer', 0),
(110, 'lockout_duration', '900', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'integer', 0),
(113, 'backup_enabled', '1', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'boolean', 0),
(114, 'backup_frequency', 'daily', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'string', 0),
(115, 'backup_retention_days', '30', NULL, '2025-09-22 18:22:51', '2025-09-22 18:22:51', 'integer', 0),
(116, 'encryption_key', '/cnBqot5QMl7xlA3Y+AiaXo589COL1/6rN0PAlcFen0=', NULL, '2025-09-22 18:23:57', '2025-09-22 18:23:57', 'string', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_health_logs`
--

CREATE TABLE `system_health_logs` (
  `id` int(11) NOT NULL,
  `overall_status` enum('healthy','warning','critical') NOT NULL,
  `services_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`services_data`)),
  `alerts_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`alerts_data`)),
  `metrics_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metrics_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `system_health_logs`
--

INSERT INTO `system_health_logs` (`id`, `overall_status`, `services_data`, `alerts_data`, `metrics_data`, `created_at`) VALUES
(1, 'healthy', '{\"Base de datos\":true,\"Servicios de email\":true,\"Servicios de SMS\":true,\"Sistema de seguridad\":true,\"Logs de auditor\\u00eda\":true}', '[]', '{\"cpu_usage\":\"15%\",\"memory_usage\":\"45%\",\"disk_usage\":\"30%\",\"response_time\":\"120ms\"}', '2025-09-22 18:33:46'),
(2, 'healthy', '{\"Base de datos\":true,\"Servicios de email\":true,\"Servicios de SMS\":true,\"Sistema de seguridad\":true,\"Logs de auditor\\u00eda\":true}', '[]', '{\"cpu_usage\":\"15%\",\"memory_usage\":\"45%\",\"disk_usage\":\"30%\",\"response_time\":\"120ms\"}', '2025-09-22 18:51:06');

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
  `available` tinyint(1) DEFAULT 1,
  `location_id` varchar(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `images` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `transfers`
--

INSERT INTO `transfers` (`id`, `name`, `type`, `capacity`, `price`, `image`, `description`, `available`, `location_id`, `created_at`, `updated_at`, `images`) VALUES
(1, 'Limusina Ejecutiva', 'limusina', 4, 150.00, 'http://localhost/dev-rent/public/images/transfers/68bdcc49dfd52_1757269065.jpg', 'Servicio de transfer ejecutivo', 1, '47ce61e1-1993-11f0-9b0b-907841162cc6', '2025-05-03 02:42:27', '2025-09-07 18:18:22', NULL),
(2, 'Minivan Familiar', 'Minivan', 8, 100.00, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&q=80', 'Transfer familiar espacioso', 1, '47ce4d50-1993-11f0-9b0b-907841162cc6', '2025-05-03 02:42:27', '2025-09-19 20:50:26', '[\"https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?w=800\", \"https://images.unsplash.com/photo-1563720223185-11003d516935?w=800\", \"https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800\"]'),
(3, 'SUV Premium', 'SUV', 6, 120.00, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80', 'Transfer en SUV de lujo', 1, '47ce4d50-1993-11f0-9b0b-907841162cc6', '2025-05-03 02:42:27', '2025-09-19 20:50:26', '[\"https://images.unsplash.com/photo-1549924231-f129b911e442?w=800\", \"https://images.unsplash.com/photo-1580273916550-e323be2ae537?w=800\"]'),
(5, 'volkswagen', 'confortable', 4, 70.00, 'http://localhost/dev-rent/public/images/transfers/68bdd82f2bef0_1757272111.jpg', '', 1, '47ce4d50-1993-11f0-9b0b-907841162cc6', '2025-09-07 19:08:46', '2025-09-07 19:08:46', NULL);

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
-- Estructura de tabla para la tabla `types`
--

CREATE TABLE `types` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `name` varchar(100) NOT NULL,
  `category` enum('vehicle','boat','transfer') NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `types`
--

INSERT INTO `types` (`id`, `name`, `category`, `active`, `created_at`, `updated_at`) VALUES
('35b8ae0e-8c1c-11f0-83ec-00090ffe0001', 'Económico', 'vehicle', 1, '2025-09-07 18:55:23', '2025-09-07 18:56:14'),
('35b8bfdb-8c1c-11f0-83ec-00090ffe0001', 'Familiar', 'vehicle', 1, '2025-09-07 18:55:23', '2025-09-07 18:55:23'),
('35b8c11f-8c1c-11f0-83ec-00090ffe0001', 'Lujo', 'vehicle', 1, '2025-09-07 18:55:23', '2025-09-07 18:55:23'),
('35b8c17a-8c1c-11f0-83ec-00090ffe0001', 'Deportivo', 'vehicle', 1, '2025-09-07 18:55:23', '2025-09-07 18:55:23'),
('35b901fa-8c1c-11f0-83ec-00090ffe0001', 'Yate', 'boat', 1, '2025-09-07 18:55:23', '2025-09-07 18:55:23'),
('35b92f6f-8c1c-11f0-83ec-00090ffe0001', 'Velero', 'boat', 1, '2025-09-07 18:55:23', '2025-09-07 18:55:23'),
('35b92fff-8c1c-11f0-83ec-00090ffe0001', 'Lancha', 'boat', 1, '2025-09-07 18:55:23', '2025-09-07 18:55:23'),
('35b9304d-8c1c-11f0-83ec-00090ffe0001', 'Catamarán', 'boat', 1, '2025-09-07 18:55:23', '2025-09-07 18:56:14'),
('35b970f0-8c1c-11f0-83ec-00090ffe0001', 'Limusina', 'transfer', 1, '2025-09-07 18:55:23', '2025-09-07 18:55:23'),
('35b9968a-8c1c-11f0-83ec-00090ffe0001', 'Minivan', 'transfer', 1, '2025-09-07 18:55:23', '2025-09-07 18:55:23'),
('35b997c4-8c1c-11f0-83ec-00090ffe0001', 'SUV', 'transfer', 1, '2025-09-07 18:55:23', '2025-09-07 18:55:23'),
('35b998bf-8c1c-11f0-83ec-00090ffe0001', 'Autobús', 'transfer', 1, '2025-09-07 18:55:23', '2025-09-07 18:56:14'),
('35b99992-8c1c-11f0-83ec-00090ffe0001', 'Taxi', 'transfer', 1, '2025-09-07 18:55:23', '2025-09-07 18:55:23'),
('35b999f6-8c1c-11f0-83ec-00090ffe0001', 'Van', 'transfer', 1, '2025-09-07 18:55:23', '2025-09-07 18:55:23'),
('8c25af2f-9b7d-11f0-b97f-00090ffe0001', 'sedán', 'vehicle', 1, '2025-09-27 08:39:57', '2025-09-27 08:39:57'),
('8c293397-9b7d-11f0-b97f-00090ffe0001', 'suv', 'vehicle', 1, '2025-09-27 08:39:57', '2025-09-27 08:39:57'),
('8c29c557-9b7d-11f0-b97f-00090ffe0001', 'hatchback', 'vehicle', 1, '2025-09-27 08:39:57', '2025-09-27 08:39:57'),
('8c2a455a-9b7d-11f0-b97f-00090ffe0001', 'pickup', 'vehicle', 1, '2025-09-27 08:39:57', '2025-09-27 08:39:57'),
('8c2b037b-9b7d-11f0-b97f-00090ffe0001', 'furgoneta', 'vehicle', 1, '2025-09-27 08:39:57', '2025-09-27 08:39:57'),
('8c2c2a51-9b7d-11f0-b97f-00090ffe0001', 'eléctrico', 'vehicle', 1, '2025-09-27 08:39:57', '2025-09-27 08:39:57'),
('8c2f6957-9b7d-11f0-b97f-00090ffe0001', 'bus', 'transfer', 1, '2025-09-27 08:39:57', '2025-09-27 08:39:57'),
('bcee0869-8c1c-11f0-83ec-00090ffe0001', 'Confortable', 'transfer', 1, '2025-09-07 18:59:10', '2025-09-07 18:59:10');

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
  `email_verified` tinyint(1) DEFAULT 0,
  `account_status` enum('active','pending_verification','disabled','expired') DEFAULT 'pending_verification',
  `verification_expires_at` timestamp NULL DEFAULT NULL,
  `disabled_at` timestamp NULL DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `verification_token_expires_at` timestamp NULL DEFAULT NULL,
  `phone_verification_code` varchar(6) DEFAULT NULL,
  `phone_verification_expires_at` timestamp NULL DEFAULT NULL,
  `registration_ip` varchar(45) DEFAULT NULL,
  `registration_user_agent` text DEFAULT NULL,
  `recaptcha_score` decimal(3,2) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`, `role`, `created_at`, `phone`, `email_verified`, `account_status`, `verification_expires_at`, `disabled_at`, `address`, `avatar`, `active`, `email_verified_at`, `phone_verified_at`, `verification_token`, `verification_token_expires_at`, `phone_verification_code`, `phone_verification_expires_at`, `registration_ip`, `registration_user_agent`, `recaptcha_score`, `updated_at`) VALUES
('559ded0b-6493-11f0-be8b-907841162cc6', 'juan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez Gómez', 'user', '2025-07-19 11:27:20', NULL, 0, 'active', NULL, NULL, NULL, NULL, 1, '2025-09-18 19:11:15', '2025-09-18 19:11:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-25 20:38:59'),
('559deeea-6493-11f0-be8b-907841162cc6', 'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María García', 'user', '2025-07-19 11:27:20', '', 0, 'active', NULL, NULL, '', '', 1, '2025-09-18 19:11:15', '2025-09-18 19:11:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-24 18:50:29'),
('559df13a-6493-11f0-be8b-907841162cc6', 'carlos@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos López', 'user', '2025-07-19 11:27:20', NULL, 0, 'active', NULL, NULL, NULL, NULL, 1, '2025-09-18 19:11:15', '2025-09-18 19:11:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-18 19:11:15'),
('559df2ab-6493-11f0-be8b-907841162cc6', 'ana@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana Martínez', 'user', '2025-07-19 11:27:20', NULL, 0, 'active', NULL, NULL, NULL, NULL, 1, '2025-09-18 19:11:15', '2025-09-18 19:11:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-18 19:11:15'),
('559df427-6493-11f0-be8b-907841162cc6', 'luis@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Luis Rodríguez', 'user', '2025-07-19 11:27:20', NULL, 0, 'active', NULL, NULL, NULL, NULL, 1, '2025-09-18 19:11:15', '2025-09-18 19:11:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-18 19:11:15'),
('559df680-6493-11f0-be8b-907841162cc6', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Johnson', 'user', '2025-07-19 11:27:20', NULL, 0, 'active', NULL, NULL, NULL, NULL, 1, '2025-09-18 19:11:15', '2025-09-18 19:11:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-18 19:11:15'),
('user_68d444420bf5b7.81305862', 'mdou.sow.alpha@gmail.com', '$2y$10$O38H6b6sBFpHkj.McVBg8.UnGv3rPW0wh6XNhsg/J5UXTplrRObmm', 'Mamadou Alpha Sow', 'admin', '2025-09-24 19:19:30', '+34634914451', 0, 'active', '2025-09-25 19:19:30', NULL, '', '', 1, '2025-09-24 19:22:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-25 20:36:29'),
('user_68d4547ccdfd28.62286863', 'sow.alpha.m@gmail.com', '$2y$10$u9qEHsRWj6YsTdEdZunFqO9nep9yXcCgoT9q5BpCMcViBPklNlAjC', 'Alpha', 'admin', '2025-09-24 20:28:44', '+34634909090', 0, 'active', '2025-09-25 20:28:44', NULL, '', '', 1, '2025-09-24 20:34:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-25 20:36:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_blocks`
--

CREATE TABLE `user_blocks` (
  `id` int(11) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `available` tinyint(1) DEFAULT 1,
  `location_id` varchar(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `images` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehicles`
--

INSERT INTO `vehicles` (`id`, `brand`, `model`, `year`, `category`, `daily_rate`, `capacity`, `image`, `description`, `available`, `location_id`, `created_at`, `images`) VALUES
('34e4c43b-9633-11f0-a5e0-00090ffe0001', 'Test Brand', 'Test Model', 2024, '', 50.00, 5, 'http://localhost/dev-rent/public/images/vehicles/68b39ac63743c_1756601030.jpg', 'Vehículo de prueba', 1, '47ce4d50-1993-11f0-9b0b-907841162cc6', '2025-09-20 15:05:12', '[\"http:\\/\\/localhost\\/dev-rent\\/public\\/images\\/vehicles\\/68b39ac63743c_1756601030.jpg\"]'),
('47e11621-1993-11f0-9b0b-907841162cc6', 'BMW', 'Serie 4', 2025, 'lujo', 110.00, 5, 'https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800', 'Sedán de lujo con acabados premium y tecnología de última generación', 1, 'ba843c90-8696-11f0-81c7-907841162cc6', '2025-04-15 00:48:00', '[\"https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?w=800\", \"https://images.unsplash.com/photo-1611273426858-450d8e3c9fce?w=800\", \"https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=800\", \"https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800\"]'),
('47e1c185-1993-11f0-9b0b-907841162cc6', 'Volkswagen', 'Golf', 2023, 'económico', 45.99, 5, 'http://localhost/dev-rent/public/images/vehicles/68ced35274120_1758384978.jpg', 'Compacto eficiente ideal para ciudad', 1, 'ba843c90-8696-11f0-81c7-907841162cc6', '2025-04-15 00:48:00', '[\"http:\\/\\/localhost\\/dev-rent\\/public\\/images\\/vehicles\\/68ced35274120_1758384978.jpg\",\"http:\\/\\/localhost\\/dev-rent\\/public\\/images\\/vehicles\\/68ced3527677c_1758384978.jpg\",\"http:\\/\\/localhost\\/dev-rent\\/public\\/images\\/vehicles\\/68ced3527a484_1758384978.jpg\"]'),
('47e1c5a1-1993-11f0-9b0b-907841162cc6', 'Mercedes-Benz', 'Clase E', 2024, 'lujo', 129.99, 5, 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=800', 'Experiencia premium con máximo confort', 1, '47ce4d50-1993-11f0-9b0b-907841162cc6', '2025-04-15 00:48:00', NULL),
('47e1c7cc-1993-11f0-9b0b-907841162cc6', 'Toyota', 'RAV4', 2023, 'familiar', 75.99, 5, 'https://images.unsplash.com/photo-1581540222194-0def2dda95b8?w=800', 'SUV versátil para toda la familia', 1, '47ce6380-1993-11f0-9b0b-907841162cc6', '2025-04-15 00:48:00', '[\"https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=800\", \"https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?w=800\"]'),
('47e1c99a-1993-11f0-9b0b-907841162cc6', 'Porsche', '911', 2024, 'deportivo', 249.99, 5, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800', 'Icónico deportivo con prestaciones excepcionales', 1, '47ce61e1-1993-11f0-9b0b-907841162cc6', '2025-04-15 00:48:00', NULL),
('583c7857-9634-11f0-a5e0-00090ffe0001', 'Test Simple Brand', 'Test Simple Model', 2024, '', 50.00, 5, 'http://localhost/dev-rent/public/images/vehicles/68cec434bce7e_1758381108.jpg', 'Vehículo de prueba simple', 1, '47ce4d50-1993-11f0-9b0b-907841162cc6', '2025-09-20 15:13:20', '[\"http:\\/\\/localhost\\/dev-rent\\/public\\/images\\/vehicles\\/68cec434bce7e_1758381108.jpg\"]');

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `verification_logs`
--

CREATE TABLE `verification_logs` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `type` enum('email','phone','recaptcha') NOT NULL,
  `status` enum('sent','verified','failed','expired') NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `verification_logs`
--

INSERT INTO `verification_logs` (`id`, `user_id`, `type`, `status`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
('68d4444480cf0', 'user_68d444420bf5b7.81305862', 'email', 'sent', '{\"user_id\":\"user_68d444420bf5b7.81305862\",\"user_name\":\"Mamadou Alpha Sow\",\"token\":\"535244984c9e3394deda68f5ccbbc07d84c8aa3ee7a1bf2b441456fbcceb03db\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 19:19:32'),
('68d44446dc912', 'user_68d444420bf5b7.81305862', 'email', 'sent', '{\"user_id\":\"user_68d444420bf5b7.81305862\",\"user_name\":\"Mamadou Alpha Sow\",\"token\":\"23a6955cc8fa4e7d10d3cd9c7bbcf2855b2647b13ac714aee7a6498bb030e9ca\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 19:19:34'),
('68d444f1dd8ec', 'user_68d444420bf5b7.81305862', 'email', 'verified', '{\"user_id\":\"user_68d444420bf5b7.81305862\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 19:22:25'),
('68d4548013479', 'user_68d4547ccdfd28.62286863', 'email', 'sent', '{\"user_id\":\"user_68d4547ccdfd28.62286863\",\"user_name\":\"Alpha\",\"token\":\"54753992f55f6fe4381fdd6be0191c8fb3f52fa6ed57e0cf0a6b578529bbbcba\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 20:28:48'),
('68d454831bd66', 'user_68d4547ccdfd28.62286863', 'email', 'sent', '{\"user_id\":\"user_68d4547ccdfd28.62286863\",\"user_name\":\"Alpha\",\"token\":\"f7b9fb7731c5925c3d99c5fb8abfcc2dcbf9aec23eb57de39642dc2fd4ee5e74\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 20:28:51'),
('68d455bb0c917', 'user_68d4547ccdfd28.62286863', 'email', 'verified', '{\"user_id\":\"user_68d4547ccdfd28.62286863\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 20:34:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `verification_tokens`
--

CREATE TABLE `verification_tokens` (
  `id` int(11) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `token` varchar(255) NOT NULL,
  `type` enum('email','phone','password_reset') NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `token_type` varchar(50) DEFAULT 'email_verification'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `verification_tokens`
--

INSERT INTO `verification_tokens` (`id`, `user_id`, `token`, `type`, `expires_at`, `used_at`, `created_at`, `token_type`) VALUES
(1, '68d1ac4d2b7cd', '8cc3344425f43aad16a1ea548073bd5ae5c6b0a9a75d6082e2289fae7ba9a61f', 'email', '2025-09-22 21:06:37', NULL, '2025-09-22 20:06:37', 'email_verification');

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
-- Indices de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_risk_score` (`risk_score`);

--
-- Indices de la tabla `boats`
--
ALTER TABLE `boats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indices de la tabla `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_to_email` (`to_email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_provider` (`provider_name`);

--
-- Indices de la tabla `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_email_verification_token` (`token`),
  ADD KEY `idx_email_verification_user_id` (`user_id`),
  ADD KEY `idx_email_verification_email` (`email`);

--
-- Indices de la tabla `entity_features`
--
ALTER TABLE `entity_features`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_entity_feature` (`entity_type`,`entity_id`,`feature_id`);

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
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_password_reset_tokens_expires` (`expires_at`);

--
-- Indices de la tabla `provider_configs`
--
ALTER TABLE `provider_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_provider_name` (`provider_name`),
  ADD KEY `idx_provider_type` (`provider_type`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indices de la tabla `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reserv_user` (`user_id`),
  ADD KEY `idx_reservations_dates` (`pickup_date`,`return_date`),
  ADD KEY `idx_reservations_status` (`status`),
  ADD KEY `idx_reservations_entity` (`entity_type`,`entity_id`);

--
-- Indices de la tabla `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_severity` (`severity`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indices de la tabla `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_to_phone` (`to_phone`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_provider` (`provider_name`);

--
-- Indices de la tabla `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`),
  ADD KEY `idx_config_key` (`config_key`);

--
-- Indices de la tabla `system_health_logs`
--
ALTER TABLE `system_health_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_overall_status` (`overall_status`),
  ADD KEY `idx_created_at` (`created_at`);

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
-- Indices de la tabla `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_type_category` (`name`,`category`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_email_verified` (`email_verified_at`),
  ADD KEY `idx_users_phone_verified` (`phone_verified_at`),
  ADD KEY `idx_users_account_status` (`account_status`);

--
-- Indices de la tabla `user_blocks`
--
ALTER TABLE `user_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indices de la tabla `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vehicles_category` (`category`),
  ADD KEY `idx_vehicles_location` (`location_id`),
  ADD KEY `idx_vehicles_available` (`available`);

--
-- Indices de la tabla `verification_logs`
--
ALTER TABLE `verification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_verification_logs_type_status` (`type`,`status`);

--
-- Indices de la tabla `verification_tokens`
--
ALTER TABLE `verification_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `boats`
--
ALTER TABLE `boats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48530001;

--
-- AUTO_INCREMENT de la tabla `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `provider_configs`
--
ALTER TABLE `provider_configs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `system_config`
--
ALTER TABLE `system_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT de la tabla `system_health_logs`
--
ALTER TABLE `system_health_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `transfers`
--
ALTER TABLE `transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `translations`
--
ALTER TABLE `translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT de la tabla `user_blocks`
--
ALTER TABLE `user_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `verification_tokens`
--
ALTER TABLE `verification_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- Filtros para la tabla `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reserv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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

--
-- Filtros para la tabla `verification_logs`
--
ALTER TABLE `verification_logs`
  ADD CONSTRAINT `verification_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
