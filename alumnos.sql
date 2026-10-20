-- Base de datos de la aplicación de alumnos (XAMPP / MariaDB).
-- Importar en phpMyAdmin o ejecutar con el cliente mysql de XAMPP.
-- anio es SMALLINT y no TINYINT: un año como 2024 no cabe en TINYINT (máximo 255).
-- password es VARCHAR(255) porque password_hash() de PHP ocupa 60 caracteres.

CREATE DATABASE IF NOT EXISTS alumnos
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE alumnos;

CREATE TABLE IF NOT EXISTS usuario (
  id_usuario INT NOT NULL AUTO_INCREMENT,
  correo VARCHAR(30) NOT NULL,
  nombre VARCHAR(30) NOT NULL,
  password VARCHAR(255) NOT NULL,
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uk_usuario_correo (correo)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS alumno (
  id_alumno INT NOT NULL AUTO_INCREMENT,
  nombres VARCHAR(50) NOT NULL,
  apellidos VARCHAR(50) NOT NULL,
  correo VARCHAR(30) NOT NULL,
  aficiones VARCHAR(100) NOT NULL,
  id_usuario INT NOT NULL,
  PRIMARY KEY (id_alumno),
  CONSTRAINT fk_alumno_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cursos (
  id_curso INT NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(50) NOT NULL,
  PRIMARY KEY (id_curso),
  UNIQUE KEY uk_cursos_nombre (nombre)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS alumno_cursos (
  id_alumno INT NOT NULL,
  id_curso INT NOT NULL,
  anio SMALLINT NOT NULL,
  PRIMARY KEY (id_alumno, id_curso, anio),
  CONSTRAINT fk_ac_alumno
    FOREIGN KEY (id_alumno) REFERENCES alumno (id_alumno) ON DELETE CASCADE,
  CONSTRAINT fk_ac_curso
    FOREIGN KEY (id_curso) REFERENCES cursos (id_curso)
) ENGINE=InnoDB;

ALTER TABLE usuario MODIFY password VARCHAR(255) NOT NULL;

INSERT INTO usuario (correo, nombre, password)
SELECT 'admin@local.test', 'Administrador', '$2y$10$hjQqZtjo7d2qzR1ZtmtTie5/xnBBCY4nWtgj4atdBSgeoTxxfrsAG'
WHERE NOT EXISTS (
  SELECT 1 FROM usuario WHERE correo = 'admin@local.test'
);

UPDATE usuario
SET password = '$2y$10$hjQqZtjo7d2qzR1ZtmtTie5/xnBBCY4nWtgj4atdBSgeoTxxfrsAG'
WHERE correo = 'admin@local.test';

INSERT INTO cursos (nombre)
SELECT 'PHP' WHERE NOT EXISTS (SELECT 1 FROM cursos WHERE nombre = 'PHP');
INSERT INTO cursos (nombre)
SELECT 'MySQL' WHERE NOT EXISTS (SELECT 1 FROM cursos WHERE nombre = 'MySQL');
INSERT INTO cursos (nombre)
SELECT 'JavaScript' WHERE NOT EXISTS (SELECT 1 FROM cursos WHERE nombre = 'JavaScript');
INSERT INTO cursos (nombre)
SELECT 'Bootstrap' WHERE NOT EXISTS (SELECT 1 FROM cursos WHERE nombre = 'Bootstrap');
