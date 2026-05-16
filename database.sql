-- =============================================
-- Sistema de Perfil de Usuario
-- Base de datos: sistema_usuarios
-- =============================================

CREATE DATABASE IF NOT EXISTS sistema_usuarios;

USE sistema_usuarios;

CREATE TABLE IF NOT EXISTS usuarios (
    id             INT AUTO_INCREMENT,
    cedula         VARCHAR(20)  NOT NULL UNIQUE,
    nombre         VARCHAR(100) NOT NULL,
    correo         VARCHAR(100) NOT NULL UNIQUE,
    password       VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
