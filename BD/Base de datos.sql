CREATE DATABASE CARRITO_ACC;
USE CARRITO_ACC;

CREATE TABLE roles (
id_rol INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
roles VARCHAR(20) NOT NULL
);

CREATE TABLE impuestos (
    id_impuesto INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    porcentaje DECIMAL(5,2) NOT NULL -- Ej. 16.00 para IVA
);

CREATE TABLE metodos_envio (
    id_envio INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    nombre_envio VARCHAR(100) NOT NULL,
    costo DECIMAL(10,2) NOT NULL,
    tiempo_estimado VARCHAR(100) -- Ej. "3-5 días hábiles"
);

CREATE TABLE formas_pago (
    id_pago INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    metodo_pago VARCHAR(50) NOT NULL -- Ej. Tarjeta, PayPal, OXXO, Transferencia
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
codigo_postal VARCHAR(5),
calle VARCHAR(20),
num_ext TINYINT,
colonia VARCHAR(50),
ciudad VARCHAR(20),
telefono VARCHAR(10) NOT NULL,
CONSTRAINT fk_usuario FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario)
);

CREATE TABLE tarjeta (
    id_tarjeta INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    numero_tarjeta VARCHAR(16),
    pin VARCHAR(4),
    id_cliente INT NOT NULL,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
);

-- Tabla de categorías
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT
);
 
-- Tabla de artículos
CREATE TABLE articulos (
    id_articulo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    fecha_registro DATE,
    stock INT DEFAULT 0,
    sku VARCHAR(50) UNIQUE,
    id_categoria INT,
    imagen VARCHAR(255),
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
);
 
-- Tabla de atributos
CREATE TABLE atributos (
    id_atributo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);
 
-- Relación artículo-atributo con valor específico
CREATE TABLE articulo_atributo (
	id_articulo_atributo INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_articulo INT,
    id_atributo INT,
    valor VARCHAR(100),
    FOREIGN KEY (id_articulo) REFERENCES articulos(id_articulo),
    FOREIGN KEY (id_atributo) REFERENCES atributos(id_atributo)
);
 
CREATE TABLE carrito (
    id_carrito INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
);

CREATE TABLE carrito_detalle (
	id_carrito_detalle INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_carrito INT,
    id_articulo_atributo INT,
    cantidad INT NOT NULL DEFAULT 1,
    precio_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_carrito) REFERENCES carrito(id_carrito),
    FOREIGN KEY (id_articulo_atributo) REFERENCES articulo_atributo(id_articulo_atributo)
);

CREATE TABLE pedidos (
    id_pedido INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    id_envio INT,
    id_pago INT,
    id_impuesto INT,
    fecha_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2),
    estatus ENUM('Pendiente', 'Pagado', 'Enviado', 'Entregado', 'Cancelado') DEFAULT 'Pendiente',
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
    FOREIGN KEY (id_envio) REFERENCES metodos_envio(id_envio),
    FOREIGN KEY (id_pago) REFERENCES formas_pago(id_pago),
    FOREIGN KEY (id_impuesto) REFERENCES impuestos(id_impuesto)
);

CREATE TABLE pedido_detalle (
	id_pedido_detalle INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_pedido INT,
    id_articulo_atributo INT,
    cantidad INT,
    precio_unitario DECIMAL(10,2),
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido),
    FOREIGN KEY (id_articulo_atributo) REFERENCES articulo_atributo(id_articulo_atributo)
);
