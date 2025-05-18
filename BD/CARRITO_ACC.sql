-- CREACIÓN DE TABLAS

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `roles` varchar(20) NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contraseña` varchar(25) NOT NULL,
  `id_rol` int(11) NOT NULL,
  PRIMARY KEY (`id_usuario`),
  KEY `fk_usuario_roles` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `nom_persona` varchar(50) NOT NULL,
  `apellido_paterno` varchar(20) NOT NULL,
  `apellido_materno` varchar(20) NOT NULL,
  `telefono` varchar(10) NOT NULL,
  `monedero` decimal(6,2) DEFAULT 0.00,
  PRIMARY KEY (`id_cliente`),
  KEY `fk_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `detalle_articulos` (
  `id_detalle_articulo` int(11) NOT NULL AUTO_INCREMENT,
  `existencia` int(11) NOT NULL,
  `costo` decimal(6,2) NOT NULL,
  `precio` decimal(6,2) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `estatus` enum('Disponible','No Disponible','Descontinuado') DEFAULT 'Disponible',
  `iva` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id_detalle_articulo`),
  KEY `fk_proveedor` (`id_proveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `articulos` (
  `id_articulo` varchar(5) NOT NULL,
  `descripcion` text NOT NULL,
  `id_detalle_articulo` int(11) NOT NULL,
  `nombre_articulo` varchar(100) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  PRIMARY KEY (`id_articulo`),
  KEY `fk_deta_art` (`id_detalle_articulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `atributos` (
  `id_atributo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id_atributo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `articulo_completo` (
  `id_articulo` varchar(5) NOT NULL,
  `id_atributo` int(11) NOT NULL,
  `valor` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_articulo`, `id_atributo`),
  KEY `fk_atri_com` (`id_atributo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `total` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id_carrito`),
  KEY `fk_cliente_carrito` (`id_cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `detalle_carrito` (
  `id_detalle_carrito` int(11) NOT NULL AUTO_INCREMENT,
  `id_carrito` int(11) NOT NULL,
  `id_articulo` varchar(5) NOT NULL,
  `cantidad` decimal(6,2) NOT NULL,
  `precio` decimal(6,2) NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `personalizacion` enum('Icono','Texto','Imagen') DEFAULT NULL,
  PRIMARY KEY (`id_detalle_carrito`),
  KEY `fk_detalle_carrito` (`id_carrito`),
  KEY `fk_articulo_carrito` (`id_articulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `direccion` (
  `id_direccion` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_postal` varchar(5) DEFAULT NULL,
  `calle` varchar(20) DEFAULT NULL,
  `num_ext` tinyint(4) DEFAULT NULL,
  `colonia` varchar(50) DEFAULT NULL,
  `ciudad` varchar(20) DEFAULT NULL,
  `id_cliente` int(11) NOT NULL,
  PRIMARY KEY (`id_direccion`),
  KEY `fk_cliente_direccion` (`id_cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `envio` (
  `id_envio` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_envio` enum('Domicilio','Punto de Entrega') NOT NULL,
  `costo` decimal(6,2) NOT NULL,
  `fecha_estimada` datetime NOT NULL,
  PRIMARY KEY (`id_envio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `formas_pago` (
  `id_forma_pago` int(11) NOT NULL AUTO_INCREMENT,
  `forma` enum('Tarjeta','Sucursal','Monedero','Otro') NOT NULL,
  `folio` varchar(5) DEFAULT NULL,
  `estado` enum('Activo','Usado') DEFAULT NULL,
  PRIMARY KEY (`id_forma_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `imagenes_articulo` (
  `id_imagen` int(11) NOT NULL AUTO_INCREMENT,
  `id_articulo` varchar(10) DEFAULT NULL,
  `nombre_imagen` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_imagen`),
  KEY `id_articulo` (`id_articulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `paqueteria` (
  `id_paqueteria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_paqueteria` varchar(50) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `fecha` datetime NOT NULL,
  `costo` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id_paqueteria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pedido` (
  `id_pedido` int(11) NOT NULL AUTO_INCREMENT,
  `id_envio` int(11) NOT NULL,
  `id_paqueteria` int(11) NOT NULL,
  `id_carrito` int(11) NOT NULL,
  `precio_total_pedido` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id_pedido`),
  KEY `fk_pedido_envio` (`id_envio`),
  KEY `fk_pedido_paqueteria` (`id_paqueteria`),
  KEY `fk_pedido_carrito` (`id_carrito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pago` (
  `id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `id_forma_pago` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `fecha_pago` datetime NOT NULL,
  PRIMARY KEY (`id_pago`),
  KEY `fk_forma_pago` (`id_forma_pago`),
  KEY `fk_pedido_pago` (`id_pedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tarjeta` (
  `id_tarjeta` int(11) NOT NULL AUTO_INCREMENT,
  `numero_tarjeta` varchar(16) DEFAULT NULL,
  `cvv` varchar(3) NOT NULL,
  `fecha_vencimiento` datetime NOT NULL,
  `tipo_tarjeta` enum('Debito','Credito') DEFAULT NULL,
  `red_pago` enum('VISA','MASTERCARD') DEFAULT NULL,
  `titular` int(11) NOT NULL,
  PRIMARY KEY (`id_tarjeta`),
  KEY `fk_usuario_tarjeta` (`titular`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- INSERCIONES (puedes copiar las tuyas tal como las tienes, son correctas)

-- Por ejemplo:
INSERT INTO `roles` (`id_rol`, `roles`) VALUES
(1, 'Administrador'), (2, 'Cliente'), (3, 'Proveedor');

-- Resto de INSERTs los puedes colocar aquí como están.

