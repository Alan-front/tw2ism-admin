-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-04-2026 a las 12:24:28
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
-- Base de datos: `tw2ism_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audio_config`
--

CREATE TABLE `audio_config` (
  `id` int(11) NOT NULL,
  `soundcloud_url` varchar(500) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `slides`
--

CREATE TABLE `slides` (
  `id` int(11) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `layout_tipo` varchar(100) DEFAULT NULL,
  `background` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `height_vh` decimal(5,2) DEFAULT 100.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `slide_elementos`
--

CREATE TABLE `slide_elementos` (
  `id` int(11) NOT NULL,
  `slide_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `type` enum('image','video','gif') NOT NULL,
  `posicion` varchar(50) DEFAULT 'center',
  `z_index` int(11) DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sound_enabled` tinyint(1) DEFAULT 0,
  `pos_x` decimal(5,2) DEFAULT 50.00,
  `pos_y` decimal(5,2) DEFAULT 50.00,
  `width` decimal(5,2) DEFAULT 50.00,
  `rotation` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `audio_config`
--
ALTER TABLE `audio_config`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `slides`
--
ALTER TABLE `slides`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `slide_elementos`
--
ALTER TABLE `slide_elementos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `slide_id` (`slide_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `audio_config`
--
ALTER TABLE `audio_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `slides`
--
ALTER TABLE `slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `slide_elementos`
--
ALTER TABLE `slide_elementos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `slide_elementos`
--
ALTER TABLE `slide_elementos`
  ADD CONSTRAINT `slide_elementos_ibfk_1` FOREIGN KEY (`slide_id`) REFERENCES `slides` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
