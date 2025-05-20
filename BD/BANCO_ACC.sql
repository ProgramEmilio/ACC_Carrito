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
saldo DECIMAL(6,2) NOT NULL,
tipo_tarjeta ENUM('Debito','Credito'),
red_pago ENUM('VISA','MASTERCARD'),
titular INT NOT NULL,
id_banco INT NOT NULL,
CONSTRAINT fk_usuario_tarjeta FOREIGN KEY (titular) REFERENCES cliente(id_cliente) ON DELETE CASCADE,
CONSTRAINT fk_usuario_banco FOREIGN KEY (id_banco) REFERENCES banco(id_banco) ON DELETE CASCADE
);

INSERT INTO `banco` (`id_banco`, `nombre_banco`) VALUES
(2, 'BBVA'),
(5, 'Banorte 2');
INSERT INTO `cliente` (`id_cliente`, `id_usuario`, `nombre_cliente`, `apellido_paterno`, `apellido_materno`, `codigo_postal`, `calle`, `num_ext`, `colonia`, `ciudad`, `telefono`) VALUES
(2, 2, 'Emilio', 'Palma', 'Jimenez', '15632', 'rios de lobos', 127, 'Centro', 'CDMX', '3232321323'),
(3, 3, 'Juan', 'Pérez', 'Gómez', '15632', 'Av Reforma', 127, 'Centro', 'saddeeeeea', '5512345678');

INSERT INTO `tarjeta` (`id_tarjeta`, `numero_tarjeta`, `cvv`, `fecha_vencimiento`, `saldo`, `tipo_tarjeta`, `red_pago`, `titular`, `id_banco`) VALUES
(1, '1243345465436645', '123', '2025-05-22 00:00:00', 12.00, 'Debito', '', 2, 2),
(4, '1868695949944343', '435', '2025-06-06 00:00:00', 23432243.00, 'Debito', 'MASTERCARD', 2, 2),
(5, '1243345465431233', '234', '2025-05-22 00:00:00', 2412443.00, 'Debito', 'MASTERCARD', 2, 5),
(6, '1111111111111111', '111', '2025-05-17 00:00:00', 999113.00, 'Debito', 'VISA', 3, 2);

INSERT INTO `usuario` (`id_usuario`, `nombre_usuario`, `correo`, `contraseña`) VALUES
(2, 'Emilio', 'emiliopalma@gmail.com', '12'),
(3, 'Cliente', 'cliente@ACC.com', '12');
