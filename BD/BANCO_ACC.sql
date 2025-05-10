CREATE DATABASE BANCO_ACC;
USE BANCO_ACC;

CREATE TABLE banco(
id_banco INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
nombre_banco VARCHAR(50) NOT NULL
);

CREATE TABLE usuario (
id_usuario INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
nombre_usuario VARCHAR(50) NOT NULL,
correo TEXT NOT NULL,
contraseña VARCHAR(25) NOT NULL
);

CREATE TABLE cliente (
id_cliente INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_usuario INT NOT NULL,
nombre_cliente VARCHAR(50) NOT NULL,
apellido_paterno VARCHAR(20) NOT NULL,
apellido_materno VARCHAR(20) NOT NULL,
codigo_postal VARCHAR(5) NOT NULL,
calle VARCHAR(20) NOT NULL,
num_ext TINYINT NOT NULL,
colonia VARCHAR(50) NOT NULL,
ciudad VARCHAR(20) NOT NULL,
telefono VARCHAR(10) NOT NULL,
CONSTRAINT fk_usuario FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario)
);

CREATE TABLE tarjeta (
id_tarjeta INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
numero_tarjeta VARCHAR(16),
cvv VARCHAR(3) NOT NULL,
fecha_vencimiento DATETIME NOT NULL, 
saldo DECIMAL(2) NOT NULL,
tipo_tarjeta ENUM('Debito','Credito'),
red_pago ENUM('VISA','MASTERCARD'),
titular INT NOT NULL,
id_banco INT NOT NULL,
CONSTRAINT fk_usuario_tarjeta FOREIGN KEY (titular) REFERENCES cliente(id_cliente),
CONSTRAINT fk_usuario_banco FOREIGN KEY (id_banco) REFERENCES banco(id_banco)
);

