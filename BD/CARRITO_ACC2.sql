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
correo TEXT NOT NULL,
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
num_ext TINYINT,
colonia VARCHAR(50),
ciudad VARCHAR(20),
id_cliente INT NOT NULL,
CONSTRAINT fk_cliente_direccion FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
);

CREATE TABLE detalle_articulos(
id_detalle_articulo INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
existencia INT NOT NULL,
costo DECIMAL(6,2) NOT NULL,
precio DECIMAL(6,2) NOT NULL,
id_proveedor INT NOT NULL,
estatus ENUM('Disponible','No Disponible','Descontinuado') DEFAULT 'Disponible',
iva DECIMAL(6,2) NOT NULL,
-- ieps DECIMAL(2) NOT NULL,
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
id_articulo VARCHAR(5) NOT NULL,
id_atributo INT NOT NULL,
valor VARCHAR(100),
PRIMARY KEY (id_articulo,id_atributo),
CONSTRAINT fk_art_com FOREIGN KEY (id_articulo) REFERENCES articulos(id_articulo),
CONSTRAINT fk_atri_com FOREIGN KEY (id_atributo) REFERENCES atributos(id_atributo)
);

CREATE TABLE detalle_carrito(
id_carrito INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_cliente INT NOT NULL,
id_articulo VARCHAR(5) NOT NULL,
fecha DATETIME NOT NULL,
cantidad DECIMAL(6,2) NOT NULL,
precio DECIMAL(6,2) NOT NULL,
importe DECIMAL(6,2) NOT NULL,
CONSTRAINT fk_cliente_carrito FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
CONSTRAINT fk_articulo_carrito FOREIGN KEY (id_articulo) REFERENCES articulos(id_articulo)
);

CREATE TABLE listado_carrito(
id_listado_carrito INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_carrito INT NOT NULL,
total DECIMAL(6,2) NOT NULL,
CONSTRAINT fk_listado_carrito FOREIGN KEY (id_carrito) REFERENCES detalle_carrito(id_carrito)
);

CREATE TABLE paqueteria(
id_paqueteria INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
nombre_paqueteria VARCHAR(50) NOT NULL,
descripcion VARCHAR(50) NOT NULL,
fecha DATETIME NOT NULL,
costo DECIMAL(6,2) NOT NULL
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
folio VARCHAR(5),
estado ENUM('Activo','Usado') 
);

CREATE TABLE pedido(
id_pedido INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_envio INT NOT NULL,
id_paqueteria INT NOT NULL,
id_listado_carrito INT NOT NULL,
precio_total_pedido DECIMAL(6,2) NOT NULL,
CONSTRAINT fk_pedido_envio FOREIGN KEY (id_envio) REFERENCES envio(id_envio),
CONSTRAINT fk_pedido_paqueteria FOREIGN KEY (id_paqueteria) REFERENCES paqueteria(id_paqueteria),
CONSTRAINT fk_pedido_carrito FOREIGN KEY (id_listado_carrito) REFERENCES listado_carrito(id_listado_carrito)
);

CREATE TABLE pago(
id_pago INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_forma_pago INT NOT NULL,
id_pedido INT NOT NULL,
fecha_pago DATETIME NOT NULL,
CONSTRAINT fk_forma_pago FOREIGN KEY (id_forma_pago) REFERENCES formas_pago(id_forma_pago),
CONSTRAINT fk_pedido_pago FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido)
);
