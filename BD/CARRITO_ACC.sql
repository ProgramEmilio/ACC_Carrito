SET SQL_MODE = NO_AUTO_VALUE_ON_ZERO;
START TRANSACTION;
SET time_zone = +00:00;

CREATE TABLE articulos (
  id_articulo varchar(5) NOT NULL,
  descripcion text NOT NULL,
  id_detalle_articulo int(11) NOT NULL,
  nombre_articulo varchar(100) NOT NULL,
  imagen varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO articulos (id_articulo, descripcion, id_detalle_articulo, nombre_articulo, imagen) VALUES
('A001', 'Playera negra con logo del TEC de Culiacán', 1, 'Playera con logo', 'playera2.png'),
('A002', 'Agenda escolar con calendario 2025 y stickers', 2, 'Agenda 2025', 'agenda1.png'),
('A003', 'Termo color azul edición limitada de 1L', 3, 'Termo YETI', 'termo3.png');

CREATE TABLE articulo_completo (
  id_articulo varchar(5) NOT NULL,
  id_atributo int(11) NOT NULL,
  valor varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE atributos (
  id_atributo int(11) NOT NULL,
  nombre varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE carrito (
  id_carrito int(11) NOT NULL,
  id_cliente int(11) NOT NULL,
  fecha datetime NOT NULL,
  total decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO carrito (id_carrito, id_cliente, fecha, total) VALUES
(1, 1, 2025-05-15 12:00:00, 1200.00),
(2, 2, 2025-05-16 16:30:00, 900.00),
(3, 1, 2025-05-17 19:17:19, 500.00);

CREATE TABLE cliente (
  id_cliente int(11) NOT NULL,
  id_usuario int(11) NOT NULL,
  nom_persona varchar(50) NOT NULL,
  apellido_paterno varchar(20) NOT NULL,
  apellido_materno varchar(20) NOT NULL,
  telefono varchar(10) NOT NULL,
  monedero decimal(6,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO cliente (id_cliente, id_usuario, nom_persona, apellido_paterno, apellido_materno, telefono, monedero) VALUES
(1, 3, 'cris', 'soliz', 'herrera', '6676047652', 0.00),
(2, 4, 'pedro', 'aguilar', 'godoy', '6677557002', 234.00),
(3, 5, 'emilio', 'Leyva', 'aveiro', '6676047652', 234.00),
(4, 6, 'be', 'dos santos', 'godoy', '6676047652', 234.00),
(5, 7, 'mia', 'soliz', 'herrera', '6677557002', 234.00);

CREATE TABLE detalle_articulos (
  id_detalle_articulo int(11) NOT NULL,
  existencia int(11) NOT NULL,
  costo decimal(6,2) NOT NULL,
  precio decimal(6,2) NOT NULL,
  id_proveedor int(11) NOT NULL,
  estatus enum('Disponible','No Disponible','Descontinuado') DEFAULT 'Disponible',
  iva decimal(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO detalle_articulos (id_detalle_articulo, existencia, costo, precio, id_proveedor, estatus, iva) VALUES
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

CREATE TABLE detalle_carrito (
  id_detalle_carrito int(11) NOT NULL,
  id_carrito int(11) NOT NULL,
  id_articulo varchar(5) NOT NULL,
  cantidad decimal(6,2) NOT NULL,
  precio decimal(6,2) NOT NULL,
  importe decimal(10,2) NOT NULL,
  personalizacion enum('Icono','Texto','Imagen') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO detalle_carrito (id_detalle_carrito, id_carrito, id_articulo, cantidad, precio, importe, personalizacion) VALUES
(9, 2, 'A001', 1.00, 200.00, 200.00, 'Texto'),
(10, 2, 'A002', 1.00, 300.00, 300.00, 'Icono');

CREATE TABLE direccion (
  id_direccion int(11) NOT NULL,
  codigo_postal varchar(5) DEFAULT NULL,
  calle varchar(20) DEFAULT NULL,
  num_ext tinyint(4) DEFAULT NULL,
  colonia varchar(50) DEFAULT NULL,
  ciudad varchar(20) DEFAULT NULL,
  id_cliente int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO direccion (id_direccion, codigo_postal, calle, num_ext, colonia, ciudad, id_cliente) VALUES
(2, '80190', 'ddf', 34, 'd', '15', 3),
(3, '80300', 'avenida', 12, 'BUENOS AIRES', 'culiacan', 1),
(5, '124', 'cristobal colon', 23, 'culiacancito', 'culiacan', 5);

CREATE TABLE envio (
  id_envio int(11) NOT NULL,
  tipo_envio enum('Domicilio','Punto de Entrega') NOT NULL,
  costo decimal(6,2) NOT NULL,
  fecha_estimada datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO envio (id_envio, tipo_envio, costo, fecha_estimada) VALUES
(1, 'Domicilio', 50.00, 2025-05-20 15:00:00),
(2, 'Punto de Entrega', 30.00, 2025-05-21 10:00:00),
(3, 'Domicilio', 80.00, 2025-05-22 19:19:05);

CREATE TABLE formas_pago (
  id_forma_pago int(11) NOT NULL,
  forma enum('Tarjeta','Sucursal','Monedero','Otro') NOT NULL,
  folio varchar(5) DEFAULT NULL,
  estado enum('Activo','Usado') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO formas_pago (id_forma_pago, forma, folio, estado) VALUES
(1, 'Sucursal', 'A004', 'Activo');

CREATE TABLE imagenes_articulo (
  id_imagen int(11) NOT NULL,
  id_articulo varchar(10) DEFAULT NULL,
  nombre_imagen varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO imagenes_articulo (id_imagen, id_articulo, nombre_imagen) VALUES
(1, 'A001', 'playera1.png'),
(2, 'A001', 'playera2.png'),
(3, 'A001', 'playera3.png'),
(4, 'A002', 'agenda1.png'),
(5, 'A002', 'agenda2.png');

CREATE TABLE pago (
  id_pago int(11) NOT NULL,
  id_forma_pago int(11) NOT NULL,
  id_pedido int(11) NOT NULL,
  fecha_pago datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO pago (id_pago, id_forma_pago, id_pedido, fecha_pago) VALUES
(6, 1, 2, 2025-05-17 19:25:04);

CREATE TABLE paqueteria (
  id_paqueteria int(11) NOT NULL,
  nombre_paqueteria varchar(50) NOT NULL,
  descripcion varchar(50) NOT NULL,
  fecha datetime NOT NULL,
  costo decimal(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO paqueteria (id_paqueteria, nombre_paqueteria, descripcion, fecha, costo) VALUES
(1, 'DHL', 'Entrega rápida nacional', 2025-05-17 14:32:22, 150.00),
(2, 'FedEx', 'Cobertura internacional', 2025-05-17 14:32:22, 200.00),
(3, 'Estafeta', 'Envío económico', 2025-05-17 14:32:22, 100.00),
(4, 'Redpack', 'Sucursal Culiacán Oriente', 2025-05-17 19:19:14, 180.00);

CREATE TABLE pedido (
  id_pedido int(11) NOT NULL,
  id_envio int(11) NOT NULL,
  id_paqueteria int(11) NOT NULL,
  id_carrito int(11) NOT NULL,
  precio_total_pedido decimal(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO pedido (id_pedido, id_envio, id_paqueteria, id_carrito, precio_total_pedido) VALUES
(1, 1, 1, 1, 1250.00),
(2, 2, 2, 2, 930.00),
(3, 2, 4, 2, 580.00),
(4, 2, 4, 2, 580.00);

CREATE TABLE roles (
  id_rol int(11) NOT NULL,
  roles varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO roles (id_rol, roles) VALUES
(1, 'Administrador'),
(2, 'Cliente'),
(3, 'Proveedor');

CREATE TABLE tarjeta (
  id_tarjeta int(11) NOT NULL,
  numero_tarjeta varchar(16) DEFAULT NULL,
  cvv varchar(3) NOT NULL,
  fecha_vencimiento datetime NOT NULL,
  tipo_tarjeta enum('Debito','Credito') DEFAULT NULL,
  red_pago enum('VISA','MASTERCARD','AMEX') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
