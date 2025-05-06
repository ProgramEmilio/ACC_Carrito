CREATE DATABASE ERP_ACC;
USE ERP_ACC;

CREATE TABLE roles (
id_rol INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
roles VARCHAR(20) NOT NULL
);

CREATE TABLE departamento(
id_departamento INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
nombre_departamento VARCHAR(50) NOT NULL
);

CREATE TABLE usuario (
id_usuario INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
nombre_usuario VARCHAR(50) NOT NULL,
correo TEXT NOT NULL,
contraseña VARCHAR(25) NOT NULL,
id_rol INT NOT NULL, 
id_departamento INT NOT NULL,
fecha_ingreso datetime,
CONSTRAINT fk_usuario_roles FOREIGN KEY (id_rol) REFERENCES Roles(id_rol),
CONSTRAINT fk_usuario_depar FOREIGN KEY (id_departamento) REFERENCES departamento(id_departamento)
);

CREATE TABLE persona (
id_persona INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_usuario INT NOT NULL,
nom_persona VARCHAR(50) NOT NULL,
apellido_paterno VARCHAR(20) NOT NULL,
apellido_materno VARCHAR(20) NOT NULL,
curp VARCHAR(18) NOT NULL,
rfc  VARCHAR(13) NOT NULL,
codigo_postal VARCHAR(5),
calle VARCHAR(20),
num_ext TINYINT,
colonia VARCHAR(50),
ciudad VARCHAR(20),
telefono VARCHAR(10) NOT NULL,
sueldo NUMERIC(10,2),
modo_Pago ENUM('Efectivo','Tarjeta','Cheque'),
CONSTRAINT fk_usuario FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario)
);

CREATE TABLE percepciones(
id_percepcion INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
sueldo_base NUMERIC(10,2) NOT NULL,
puntualidad NUMERIC(10,2) NOT NULL,
asistencia NUMERIC(10,2) NOT NULL,
bono NUMERIC(10,2),
vales_despensa NUMERIC(10,2),
compensaciones NUMERIC(10,2),
prima_antiguedad NUMERIC(10,2)
);

CREATE TABLE deducciones(
id_deducciones INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
isr NUMERIC(10,2) NOT NULL,
imss NUMERIC(10,2) NOT NULL,
caja_ahorro NUMERIC(10,2) NOT NULL,
prestamos NUMERIC(10,2) NOT NULL,
infonavit NUMERIC(10,2) NOT NULL,
fonacot NUMERIC(10,2) NOT NULL,
cuota_sindical NUMERIC(10,2) NOT NULL
);

CREATE TABLE incapacidad(
id_incapacidad INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_persona INT NOT NULL,
fecha_inicio DATE NOT NULL,
fecha_final DATE NOT NULL,
total_dias INT NOT NULL,
motivo VARCHAR(100) NOT NULL,
estatus ENUM('Activo','Inactivo'),
CONSTRAINT fk_persona FOREIGN KEY (id_persona) REFERENCES persona(id_persona)
);

CREATE TABLE nomina(
id_nomina INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_persona INT NOT NULL,
fecha_nomina DATE NOT NULL,
periodo_inicio DATE NOT NULL,
periodo_final DATE NOT NULL,
dias_trabajados TINYINT NOT NULL DEFAULT 0,
dias_justificados TINYINT NOT NULL DEFAULT 0,
dias_total NUMERIC(10,2) NOT NULL,
id_deducciones INT NOT NULL,
id_percepcion INT NOT NULL,
CONSTRAINT fk_persona_nomina FOREIGN KEY (id_persona) REFERENCES persona(id_persona),
CONSTRAINT fk_deducciones FOREIGN KEY (id_deducciones) REFERENCES deducciones(id_deducciones),
CONSTRAINT fk_percepcion FOREIGN KEY (id_percepcion) REFERENCES percepciones(id_percepcion)
);


INSERT INTO departamento (nombre_departamento) VALUES
('Administración'),
('Recursos Humanos'),
('Tecnologías de la Información'),
('Finanzas'),
('Marketing'),
('Ventas');

INSERT INTO roles (roles) VALUES
('Administrador'),                -- Administración
('Gerente de RRHH'),             -- Recursos Humanos
('Especialista TI'),             -- Tecnologías de la Información
('Contador'),                    -- Finanzas
('Analista de Marketing'),       -- Marketing
('Ejecutivo de Ventas');         -- Ventas

INSERT INTO usuario (nombre_usuario, correo, contraseña, id_rol, id_departamento,fecha_ingreso) VALUES
('admin', 'admin@ACC.com', '12', 1, 1,'20230320 17:03:01'),
('rrhh_gerente', 'rrhh@ACC.com', '12', 2, 2,'20230320 17:03:01'),
('ti_user', 'ti@ACC.com', '12', 3, 3,'20230320 17:03:01'),
('finanzas_cont', 'cont@ACC.com', '12', 4, 4,'20230320 17:03:01'),
('mkt_analyst', 'mkt@ACC.com', '12', 5, 5,'20230320 17:03:01'),
('ventas_exec', 'ventas@ACC.com', '12', 6, 6,'20230320 17:03:01');

INSERT INTO persona (
  id_usuario, nom_persona, apellido_paterno, apellido_materno, curp, rfc, 
  codigo_postal, calle, num_ext, colonia, ciudad, telefono, sueldo, modo_Pago
) VALUES
(1, 'Carlos', 'Ramírez', 'López', 'CARR900101HDFLPL01', 'CARR9001011A1', '01000', 'Av Reforma', 101, 'Centro', 'CDMX', '5512345678', 50000.00, 'Cheque'),
(2, 'Lucía', 'Martínez', 'González', 'LUMG850202MDFMZN02', 'LUMG8502022B2', '03800', 'Insurgentes Sur', 202, 'Napoles', 'CDMX', '5543219876', 35000.00, 'Efectivo'),
(3, 'Miguel', 'Santos', 'Hernández', 'MISH920303HDFTRD03', 'MISH9203033C3', '04300', 'Cuauhtémoc', 303, 'Del Valle', 'CDMX', '5567894321', 40000.00, 'Tarjeta'),
(4, 'Andrea', 'López', 'Ramírez', 'ANLR940404MDFRMR04', 'ANLR9404044D4', '06100', 'Álvaro Obregón', 404, 'Roma Norte', 'CDMX', '5523456789', 42000.00, 'Cheque'),
(5, 'Diego', 'Hernández', 'Vega', 'DIHV960505HDFVGA05', 'DIHV9605055E5', '06700', 'Av Juárez', 505, 'Doctores', 'CDMX', '5576543210', 38000.00, 'Efectivo'),
(6, 'Sofía', 'García', 'Núñez', 'SOGN970606MDFNZU06', 'SOGN9706066F6', '06900', 'Balderas', 606, 'Guerrero', 'CDMX', '5598765432', 37000.00, 'Tarjeta');

