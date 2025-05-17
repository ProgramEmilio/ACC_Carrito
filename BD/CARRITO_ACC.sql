-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-05-2025 a las 00:13:02
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
-- Base de datos: `carrito_acc`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulos`
--

CREATE TABLE `articulos` (
  `id_articulo` varchar(5) NOT NULL,
  `descripcion` text NOT NULL,
  `id_detalle_articulo` int(11) NOT NULL,
  `nombre_articulo` varchar(100) NOT NULL,
  `imagen` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `articulos`
--

INSERT INTO `articulos` (`id_articulo`, `descripcion`, `id_detalle_articulo`, `nombre_articulo`, `imagen`) VALUES
('A001', 'Playera negra con logo del TEC de Culiacán', 1, 'Playera con logo', 'playera2.png'),
('A002', 'Agenda escolar con calendario 2025 y stickers', 2, 'Agenda 2025', 'agenda1.png'),
('A003', 'Termo color azul edición limitada de 1L', 3, 'Termo YETI', 'termo3.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulo_completo`
--

CREATE TABLE `articulo_completo` (
  `id_articulo` varchar(5) NOT NULL,
  `id_atributo` int(11) NOT NULL,
  `valor` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atributos`
--

CREATE TABLE `atributos` (
  `id_atributo` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id_carrito`, `id_cliente`, `fecha`, `total`) VALUES
(1, 1, '2025-05-15 12:00:00', 1200.00),
(2, 2, '2025-05-16 16:30:00', 900.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nom_persona` varchar(50) NOT NULL,
  `apellido_paterno` varchar(20) NOT NULL,
  `apellido_materno` varchar(20) NOT NULL,
  `telefono` varchar(10) NOT NULL,
  `monedero` decimal(6,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `id_usuario`, `nom_persona`, `apellido_paterno`, `apellido_materno`, `telefono`, `monedero`) VALUES
(1, 3, 'cris', 'soliz', 'herrera', '6676047652', 0.00),
(2, 4, 'pedro', 'aguilar', 'godoy', '6677557002', 234.00),
(3, 5, 'emilio', 'Leyva', 'aveiro', '6676047652', 234.00),
(4, 6, 'be', 'dos santos', 'godoy', '6676047652', 234.00),
(5, 7, 'mia', 'soliz', 'herrera', '6677557002', 234.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_articulos`
--

CREATE TABLE `detalle_articulos` (
  `id_detalle_articulo` int(11) NOT NULL,
  `existencia` int(11) NOT NULL,
  `costo` decimal(6,2) NOT NULL,
  `precio` decimal(6,2) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `estatus` enum('Disponible','No Disponible','Descontinuado') DEFAULT 'Disponible',
  `iva` decimal(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_articulos`
--

INSERT INTO `detalle_articulos` (`id_detalle_articulo`, `existencia`, `costo`, `precio`, `id_proveedor`, `estatus`, `iva`) VALUES
(1, 50, 100.00, 150.00, 1, 'Disponible', 16.00),
(2, 30, 80.00, 120.00, 1, 'Disponible', 16.00),
(3, 20, 200.00, 300.00, 1, 'Disponible', 16.00),
(4, 10, 100.00, 250.00, 1, 'Disponible', 16.00),
(5, 5, 80.00, 150.00, 1, 'Disponible', 16.00),
(6, 7, 150.00, 300.00, 1, 'Disponible', 16.00),
(7, 10, 100.00, 250.00, 1, 'Disponible', 16.00),
(8, 5, 80.00, 150.00, 1, 'Disponible', 16.00),
(9, 7, 150.00, 300.00, 1, 'Disponible', 16.00),
(10, 10, 100.00, 250.00, 1, 'Disponible', 16.00),
(11, 5, 80.00, 150.00, 1, 'Disponible', 16.00),
(12, 7, 150.00, 300.00, 1, 'Disponible', 16.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_carrito`
--

CREATE TABLE `detalle_carrito` (
  `id_detalle_carrito` int(11) NOT NULL,
  `id_carrito` int(11) NOT NULL,
  `id_articulo` varchar(5) NOT NULL,
  `cantidad` decimal(6,2) NOT NULL,
  `precio` decimal(6,2) NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `personalizacion` enum('Icono','Texto','Imagen') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `id_direccion` int(11) NOT NULL,
  `codigo_postal` varchar(5) DEFAULT NULL,
  `calle` varchar(20) DEFAULT NULL,
  `num_ext` tinyint(4) DEFAULT NULL,
  `colonia` varchar(50) DEFAULT NULL,
  `ciudad` varchar(20) DEFAULT NULL,
  `id_cliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direccion`
--

INSERT INTO `direccion` (`id_direccion`, `codigo_postal`, `calle`, `num_ext`, `colonia`, `ciudad`, `id_cliente`) VALUES
(2, '80190', 'ddf', 34, 'd', '15', 3),
(3, '80300', 'avenida', 12, 'BUENOS AIRES', 'culiacan', 1),
(4, '80300', 'avenida', 12, 'bn', 'culiacan', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envio`
--

CREATE TABLE `envio` (
  `id_envio` int(11) NOT NULL,
  `tipo_envio` enum('Domicilio','Punto de Entrega') NOT NULL,
  `costo` decimal(6,2) NOT NULL,
  `fecha_estimada` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `envio`
--

INSERT INTO `envio` (`id_envio`, `tipo_envio`, `costo`, `fecha_estimada`) VALUES
(1, 'Domicilio', 50.00, '2025-05-20 15:00:00'),
(2, 'Punto de Entrega', 30.00, '2025-05-21 10:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formas_pago`
--

CREATE TABLE `formas_pago` (
  `id_forma_pago` int(11) NOT NULL,
  `forma` enum('Tarjeta','Sucursal','Monedero','Otro') NOT NULL,
  `folio` varchar(5) DEFAULT NULL,
  `estado` enum('Activo','Usado') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_articulo`
--

CREATE TABLE `imagenes_articulo` (
  `id_imagen` int(11) NOT NULL,
  `id_articulo` varchar(10) DEFAULT NULL,
  `nombre_imagen` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `imagenes_articulo`
--

INSERT INTO `imagenes_articulo` (`id_imagen`, `id_articulo`, `nombre_imagen`) VALUES
(1, 'A001', 'playera1.png'),
(2, 'A001', 'playera2.png'),
(3, 'A001', 'playera3.png'),
(4, 'A002', 'agenda1.png'),
(5, 'A002', 'agenda2.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

CREATE TABLE `pago` (
  `id_pago` int(11) NOT NULL,
  `id_forma_pago` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `fecha_pago` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paqueteria`
--

CREATE TABLE `paqueteria` (
  `id_paqueteria` int(11) NOT NULL,
  `nombre_paqueteria` varchar(50) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `fecha` datetime NOT NULL,
  `costo` decimal(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `paqueteria`
--

INSERT INTO `paqueteria` (`id_paqueteria`, `nombre_paqueteria`, `descripcion`, `fecha`, `costo`) VALUES
(1, 'DHL', 'Entrega rápida nacional', '2025-05-17 14:32:22', 150.00),
(2, 'FedEx', 'Cobertura internacional', '2025-05-17 14:32:22', 200.00),
(3, 'Estafeta', 'Envío económico', '2025-05-17 14:32:22', 100.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id_pedido` int(11) NOT NULL,
  `id_envio` int(11) NOT NULL,
  `id_paqueteria` int(11) NOT NULL,
  `id_carrito` int(11) NOT NULL,
  `precio_total_pedido` decimal(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`id_pedido`, `id_envio`, `id_paqueteria`, `id_carrito`, `precio_total_pedido`) VALUES
(1, 1, 1, 1, 1250.00),
(2, 2, 2, 2, 930.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `roles` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `roles`) VALUES
(1, 'Administrador'),
(2, 'Cliente'),
(3, 'Proveedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarjeta`
--

CREATE TABLE `tarjeta` (
  `id_tarjeta` int(11) NOT NULL,
  `numero_tarjeta` varchar(16) DEFAULT NULL,
  `cvv` varchar(3) NOT NULL,
  `fecha_vencimiento` datetime NOT NULL,
  `tipo_tarjeta` enum('Debito','Credito') DEFAULT NULL,
  `red_pago` enum('VISA','MASTERCARD') DEFAULT NULL,
  `titular` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tarjeta`
--

INSERT INTO `tarjeta` (`id_tarjeta`, `numero_tarjeta`, `cvv`, `fecha_vencimiento`, `tipo_tarjeta`, `red_pago`, `titular`) VALUES
(5, '1234567891234567', '123', '2025-09-01 00:00:00', 'Credito', 'MASTERCARD', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contraseña` varchar(25) NOT NULL,
  `id_rol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre_usuario`, `correo`, `contraseña`, `id_rol`) VALUES
(1, 'Proveedor Demo', 'proveedor@demo.com', '123456', 2),
(3, 'cris', 'cris@gmail.com', '12', 2),
(4, 'pedro', 'pedro@gmail.com', '12', 2),
(5, 'emilio', 'emilio@gmail.com', '12', 2),
(6, 'Belem', 'belem@gmail.com', '12', 2),
(7, 'mia', 'mia@gmail.com', '12', 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD PRIMARY KEY (`id_articulo`),
  ADD KEY `fk_deta_art` (`id_detalle_articulo`);

--
-- Indices de la tabla `articulo_completo`
--
ALTER TABLE `articulo_completo`
  ADD PRIMARY KEY (`id_articulo`,`id_atributo`),
  ADD KEY `fk_atri_com` (`id_atributo`);

--
-- Indices de la tabla `atributos`
--
ALTER TABLE `atributos`
  ADD PRIMARY KEY (`id_atributo`);

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD KEY `fk_cliente_carrito` (`id_cliente`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`),
  ADD KEY `fk_usuario` (`id_usuario`);

--
-- Indices de la tabla `detalle_articulos`
--
ALTER TABLE `detalle_articulos`
  ADD PRIMARY KEY (`id_detalle_articulo`),
  ADD KEY `fk_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `detalle_carrito`
--
ALTER TABLE `detalle_carrito`
  ADD PRIMARY KEY (`id_detalle_carrito`),
  ADD KEY `fk_detalle_carrito` (`id_carrito`),
  ADD KEY `fk_articulo_carrito` (`id_articulo`);

--
-- Indices de la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`id_direccion`),
  ADD KEY `fk_cliente_direccion` (`id_cliente`);

--
-- Indices de la tabla `envio`
--
ALTER TABLE `envio`
  ADD PRIMARY KEY (`id_envio`);

--
-- Indices de la tabla `formas_pago`
--
ALTER TABLE `formas_pago`
  ADD PRIMARY KEY (`id_forma_pago`);

--
-- Indices de la tabla `imagenes_articulo`
--
ALTER TABLE `imagenes_articulo`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `id_articulo` (`id_articulo`);

--
-- Indices de la tabla `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `fk_forma_pago` (`id_forma_pago`),
  ADD KEY `fk_pedido_pago` (`id_pedido`);

--
-- Indices de la tabla `paqueteria`
--
ALTER TABLE `paqueteria`
  ADD PRIMARY KEY (`id_paqueteria`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `fk_pedido_envio` (`id_envio`),
  ADD KEY `fk_pedido_paqueteria` (`id_paqueteria`),
  ADD KEY `fk_pedido_carrito` (`id_carrito`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `tarjeta`
--
ALTER TABLE `tarjeta`
  ADD PRIMARY KEY (`id_tarjeta`),
  ADD KEY `fk_usuario_tarjeta` (`titular`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `fk_usuario_roles` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `atributos`
--
ALTER TABLE `atributos`
  MODIFY `id_atributo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalle_articulos`
--
ALTER TABLE `detalle_articulos`
  MODIFY `id_detalle_articulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `detalle_carrito`
--
ALTER TABLE `detalle_carrito`
  MODIFY `id_detalle_carrito` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `id_direccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `envio`
--
ALTER TABLE `envio`
  MODIFY `id_envio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `formas_pago`
--
ALTER TABLE `formas_pago`
  MODIFY `id_forma_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `imagenes_articulo`
--
ALTER TABLE `imagenes_articulo`
  MODIFY `id_imagen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pago`
--
ALTER TABLE `pago`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `paqueteria`
--
ALTER TABLE `paqueteria`
  MODIFY `id_paqueteria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tarjeta`
--
ALTER TABLE `tarjeta`
  MODIFY `id_tarjeta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD CONSTRAINT `fk_deta_art` FOREIGN KEY (`id_detalle_articulo`) REFERENCES `detalle_articulos` (`id_detalle_articulo`);

--
-- Filtros para la tabla `articulo_completo`
--
ALTER TABLE `articulo_completo`
  ADD CONSTRAINT `fk_art_com` FOREIGN KEY (`id_articulo`) REFERENCES `articulos` (`id_articulo`),
  ADD CONSTRAINT `fk_atri_com` FOREIGN KEY (`id_atributo`) REFERENCES `atributos` (`id_atributo`);

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `fk_cliente_carrito` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`);

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `fk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `detalle_articulos`
--
ALTER TABLE `detalle_articulos`
  ADD CONSTRAINT `fk_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `detalle_carrito`
--
ALTER TABLE `detalle_carrito`
  ADD CONSTRAINT `fk_articulo_carrito` FOREIGN KEY (`id_articulo`) REFERENCES `articulos` (`id_articulo`),
  ADD CONSTRAINT `fk_detalle_carrito` FOREIGN KEY (`id_carrito`) REFERENCES `carrito` (`id_carrito`);

--
-- Filtros para la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD CONSTRAINT `fk_cliente_direccion` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`);

--
-- Filtros para la tabla `imagenes_articulo`
--
ALTER TABLE `imagenes_articulo`
  ADD CONSTRAINT `imagenes_articulo_ibfk_1` FOREIGN KEY (`id_articulo`) REFERENCES `articulos` (`id_articulo`);

--
-- Filtros para la tabla `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `fk_forma_pago` FOREIGN KEY (`id_forma_pago`) REFERENCES `formas_pago` (`id_forma_pago`),
  ADD CONSTRAINT `fk_pedido_pago` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`);

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `fk_pedido_carrito` FOREIGN KEY (`id_carrito`) REFERENCES `carrito` (`id_carrito`),
  ADD CONSTRAINT `fk_pedido_envio` FOREIGN KEY (`id_envio`) REFERENCES `envio` (`id_envio`),
  ADD CONSTRAINT `fk_pedido_paqueteria` FOREIGN KEY (`id_paqueteria`) REFERENCES `paqueteria` (`id_paqueteria`);

--
-- Filtros para la tabla `tarjeta`
--
ALTER TABLE `tarjeta`
  ADD CONSTRAINT `fk_usuario_tarjeta` FOREIGN KEY (`titular`) REFERENCES `cliente` (`id_cliente`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
