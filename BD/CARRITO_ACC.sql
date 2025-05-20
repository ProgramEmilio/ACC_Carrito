
CREATE DATABASE CARRITO_ACC;
USE CARRITO_ACC;

-- cliente,proveedor,administrador
CREATE TABLE roles (
id_rol INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
roles VARCHAR(20) NOT NULL
);

CREATE TABLE usuario (
id_usuario INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
nombre_usuario VARCHAR(50) NOT NULL,
correo VARCHAR(100) NOT NULL,
contraseña VARCHAR(25) NOT NULL,
id_rol INT NOT NULL, 
CONSTRAINT fk_usuario_roles FOREIGN KEY (id_rol) REFERENCES Roles(id_rol)
);

CREATE TABLE cliente (
id_cliente INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_usuario INT NOT NULL,
nom_persona VARCHAR(50) NOT NULL,
apellido_paterno VARCHAR(20) NOT NULL,
apellido_materno VARCHAR(20) NOT NULL,
telefono VARCHAR(10) NOT NULL,
monedero DECIMAL(6,2) DEFAULT 0,
CONSTRAINT fk_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE direccion(
id_direccion INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
codigo_postal VARCHAR(5),
calle VARCHAR(20),
num_ext INT,
colonia VARCHAR(50),
ciudad VARCHAR(20),
estado VARCHAR(50),
id_cliente INT NOT NULL,
CONSTRAINT fk_cliente_direccion FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
);

CREATE TABLE tarjeta (
id_tarjeta INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
numero_tarjeta VARCHAR(16),
cvv VARCHAR(3) NOT NULL,
fecha_vencimiento DATETIME NOT NULL, 
tipo_tarjeta ENUM('Debito','Credito'),
red_pago ENUM('VISA','MASTERCARD'),
titular INT NOT NULL,
CONSTRAINT fk_usuario_tarjeta FOREIGN KEY (titular) REFERENCES cliente(id_cliente)
);

CREATE TABLE detalle_articulos(
id_detalle_articulo INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
existencia INT NOT NULL,
costo DECIMAL(6,2) NOT NULL,
precio DECIMAL(6,2) NOT NULL,
id_proveedor INT NOT NULL,
estatus ENUM('Disponible','No Disponible','Descontinuado') DEFAULT 'Disponible',
CONSTRAINT fk_proveedor FOREIGN KEY (id_proveedor) REFERENCES usuario(id_usuario)
);

-- Tabla de atributos
CREATE TABLE atributos (
-- fotos,videos,color,unidades(talla,pieza,cm para termo,agenda),dimensiones,peso
id_atributo INT AUTO_INCREMENT PRIMARY KEY,
nombre VARCHAR(100) NOT NULL
);

 CREATE TABLE articulos(
id_articulo VARCHAR(5) NOT NULL PRIMARY KEY UNIQUE,
descripcion TEXT NOT NULL,
id_detalle_articulo INT NOT NULL,
CONSTRAINT fk_deta_art FOREIGN KEY (id_detalle_articulo) REFERENCES detalle_articulos(id_detalle_articulo)
);

CREATE TABLE articulo_completo (
id_articulo_completo INT NOT NULL PRIMARY KEY AUTO_INCREMENT,    
id_articulo VARCHAR(5) NOT NULL,
id_atributo INT NOT NULL,
valor VARCHAR(100),
CONSTRAINT fk_art_com FOREIGN KEY (id_articulo) REFERENCES articulos(id_articulo),
CONSTRAINT fk_atri_com FOREIGN KEY (id_atributo) REFERENCES atributos(id_atributo)
);

CREATE TABLE carrito(
id_carrito INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_cliente INT NOT NULL,
fecha DATETIME NOT NULL,
total DECIMAL(10,2) DEFAULT 0,
CONSTRAINT fk_cliente_carrito FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
);

CREATE TABLE detalle_carrito(
id_detalle_carrito INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_carrito INT NOT NULL,
id_articulo VARCHAR(5) NOT NULL,
cantidad DECIMAL(6,2) NOT NULL,
precio DECIMAL(6,2) NOT NULL,
importe DECIMAL(10,2) NOT NULL,
personalizacion ENUM('Icono','Texto','Imagen'),
CONSTRAINT fk_detalle_carrito FOREIGN KEY (id_carrito) REFERENCES carrito(id_carrito),
CONSTRAINT fk_articulo_carrito FOREIGN KEY (id_articulo) REFERENCES articulos(id_articulo)
);

CREATE TABLE paqueteria(
id_paqueteria INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
nombre_paqueteria VARCHAR(50) NOT NULL,
descripcion VARCHAR(50) NOT NULL,
fecha DATETIME NOT NULL
);

CREATE TABLE envio(
id_envio INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
tipo_envio ENUM('Domicilio','Punto de Entrega') NOT NULL,
costo DECIMAL(6,2) NOT NULL,
fecha_estimada DATETIME NOT NULL
);

-- Formas de pago (tarjeta, efectivo/pago sucursal/OXXO,monedero,otra)
CREATE TABLE formas_pago(
id_forma_pago INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
forma ENUM('Tarjeta','Sucursal','Monedero','Otro') NOT NULL,
folio VARCHAR(35),
estado ENUM('Activo','Usado') 
);

CREATE TABLE pedido(
id_pedido INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_envio INT NOT NULL,
id_paqueteria INT,
id_carrito INT NOT NULL,
precio_total_pedido DECIMAL(6,2) NOT NULL,
CONSTRAINT fk_pedido_envio FOREIGN KEY (id_envio) REFERENCES envio(id_envio),
CONSTRAINT fk_pedido_paqueteria FOREIGN KEY (id_paqueteria) REFERENCES paqueteria(id_paqueteria),
CONSTRAINT fk_pedido_carrito FOREIGN KEY (id_carrito) REFERENCES carrito(id_carrito)
);

ALTER TABLE pedido
ADD COLUMN iva DECIMAL(6,2) NOT NULL,
ADD COLUMN ieps DECIMAL(2),
ADD COLUMN id_direccion INT;

CREATE TABLE pago(
id_pago INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_forma_pago INT NOT NULL,
id_pedido INT NOT NULL,
monto DECIMAL(6,2) NOT NULL,
fecha_pago DATETIME NOT NULL,
CONSTRAINT fk_forma_pago FOREIGN KEY (id_forma_pago) REFERENCES formas_pago(id_forma_pago),
CONSTRAINT fk_pedido_pago FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido)
);

CREATE TABLE compra(
id_compra INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_cliente INT NOT NULL,
id_pago INT NOT NULL,
id_paqueteria INT NOT NULL,
CONSTRAINT fk_compra_cli FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
CONSTRAINT fk_compra_pago FOREIGN KEY (id_pago) REFERENCES pago(id_pago),
CONSTRAINT fk_compra_paq FOREIGN KEY (id_paqueteria) REFERENCES paqueteria(id_paqueteria)
);

CREATE TABLE seguimiento_pedido(
id_seguimiento_pedido INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_pedido INT NOT NULL,
id_cliente INT NOT NULL,
Estado ENUM('Enviado','En camino','Entregado','Otro') NOT NULL,
CONSTRAINT fk_pedido_envio FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido),
CONSTRAINT fk_pedido_carrito FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
);


-- Roles
INSERT INTO roles (roles) VALUES
('Administrador'),               
('Cliente'),             
('Proveedor');

-- Usuarios
INSERT INTO usuario (nombre_usuario, correo, contraseña, id_rol) VALUES
('admin', 'admin@ACC.com', '12', 1),
('Juans', 'cliente@ACC.com', '12', 2),
('proveedor', 'proveedor@ACC.com', '12', 3);

-- Cliente
INSERT INTO cliente (id_usuario, nom_persona, apellido_paterno, apellido_materno, telefono, monedero) VALUES
(2, 'Juan', 'Pérez', 'Gómez', '6123456789', 50.00),
(1, 'Emilio', 'Palma', 'Jimenez', '6123456789', 0.00);

-- Dirección del cliente
INSERT INTO direccion(codigo_postal, calle, num_ext, colonia, ciudad, estado, id_cliente) VALUES
('80100', 'Av. Revolución', 123, 'Centro', 'Culiacán', 'Sinaloa', 1);

-- Tarjeta del cliente
INSERT INTO tarjeta (numero_tarjeta, cvv, fecha_vencimiento, tipo_tarjeta, red_pago, titular) VALUES
('1234567890123456', '123', '2026-12-31', 'Credito', 'VISA', 1),
('1111111111111111', '111', '2025-05-01 00:00:00', 'Debito', 'VISA', 1);;

-- Detalle de artículos
INSERT INTO detalle_articulos (existencia, costo, precio, id_proveedor, estatus) VALUES
(50, 120.00, 250.00, 3, 'Disponible'),
(30, 150.00, 300.00, 3, 'Disponible'),
(40, 100.00, 200.00, 3, 'Disponible');

-- Artículos
INSERT INTO articulos (id_articulo, descripcion, id_detalle_articulo) VALUES
('P001', 'Playera personalizada con diseño a elección.', 1),
('P002', 'Termo de acero inoxidable con grabado personalizado.', 2),
('P003', 'Agenda con portada personalizada.', 3);

-- Atributos
INSERT INTO atributos (nombre) VALUES 
('Color'), 
('Tamaño'), 
('Imagen'),
('Video'),
('Dimensiones'),
('Peso');

-- Relación artículo-atributo
INSERT INTO articulo_completo (id_articulo, id_atributo, valor) VALUES 
('P001', 1, 'Negro'),
('P001', 2, 'M'),
('P001', 3, 'playera2.png'),
('P001', 3, 'termo3.png'),
('P002', 1, 'Azul'),
('P002', 2, '750ml'),
('P002', 3, 'termo3.png'),
('P003', 1, 'Verde'),
('P003', 2, 'A5'),
('P003', 3, 'agenda1.png');

-- Carrito del cliente
INSERT INTO carrito (id_cliente, fecha, total) VALUES
(1, NOW(), 800.00);

-- Detalles del carrito
INSERT INTO detalle_carrito (id_carrito, id_articulo, cantidad, precio, importe, personalizacion) VALUES
(1, 'P001', 2, 250.00, 500.00, 'Texto'),
(1, 'P002', 1, 300.00, 300.00, 'Imagen');

-- Paquetería
INSERT INTO paqueteria (nombre_paqueteria, descripcion, fecha) VALUES
('Estafeta', 'Sucursal Culiacán Centro', NOW()),
('FedEx', 'Sucursal Culiacán Sur',  NOW()),
('DHL', 'Sucursal Culiacán Norte', NOW());

-- Envíos
INSERT INTO envio (tipo_envio, costo, fecha_estimada) VALUES
('Domicilio', 80.00, DATE_ADD(NOW(), INTERVAL 3 DAY)),
('Punto de Entrega', 40.00, DATE_ADD(NOW(), INTERVAL 2 DAY));

-- Formas de pago
INSERT INTO formas_pago (forma, folio, estado) VALUES
('Tarjeta', 'A001', 'Activo'),
('Monedero', 'A002', 'Usado'),
('Sucursal', 'A003', 'Activo');

-- Pedido
INSERT INTO pedido (id_envio, id_paqueteria, id_carrito, precio_total_pedido) VALUES
(1, 1, 1, 880.00);

-- Pago
INSERT INTO pago (id_forma_pago, id_pedido, fecha_pago) VALUES
(1, 1, NOW());
