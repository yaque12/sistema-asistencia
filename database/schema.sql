-- ============================================
-- SISTEMA DE ASISTENCIA - ESQUEMA DE BASE DE DATOS
-- ============================================
-- Este archivo contiene las sentencias SQL que debes ejecutar manualmente
-- en MySQL (phpMyAdmin, MySQL Workbench, o línea de comandos)
-- ============================================

-- Paso 1: Crear la base de datos
CREATE DATABASE IF NOT EXISTS sistema_asistencia 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Paso 2: Seleccionar la base de datos
USE sistema_asistencia;

-- Paso 3: Crear la tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    -- ID: Identificador único de cada usuario (se genera automáticamente)
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Nombre de usuario: Para iniciar sesión (debe ser único)
    nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
    
    -- Nombres: Primer y segundo nombre del usuario
    nombres VARCHAR(150) NOT NULL,
    
    -- Apellidos: Apellidos del usuario
    apellidos VARCHAR(150) NOT NULL,
    
    -- Departamento de trabajo: Área donde trabaja (puede ser nulo)
    departamento_trabajo VARCHAR(100) NULL,
    
    -- Código de empleado: Identificador único del empleado (puede ser nulo)
    codigo_empleado VARCHAR(50) NULL,
    
    -- Clave: Contraseña encriptada (nunca se guarda en texto plano)
    clave VARCHAR(255) NOT NULL,
    
    -- Marcas de tiempo: Laravel las usa para saber cuándo se creó/actualizó
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Paso 4: Crear la tabla de sesiones (necesaria para Laravel)
CREATE TABLE IF NOT EXISTS sessions (
    -- ID de la sesión (clave primaria)
    id VARCHAR(255) PRIMARY KEY,
    
    -- ID del usuario (opcional, puede ser nulo si no está autenticado)
    user_id BIGINT UNSIGNED NULL,
    
    -- Dirección IP del usuario
    ip_address VARCHAR(45) NULL,
    
    -- Información del navegador del usuario
    user_agent TEXT NULL,
    
    -- Datos de la sesión (encriptados)
    payload LONGTEXT NOT NULL,
    
    -- Última actividad (timestamp Unix)
    last_activity INT NOT NULL,
    
    -- Índices para mejorar el rendimiento
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSTRUCCIONES:
-- ============================================
-- 1. Abre phpMyAdmin (http://localhost/phpmyadmin) o MySQL Workbench
-- 2. Copia y pega estas sentencias SQL
-- 3. Ejecuta el script completo
-- 4. Verifica que la base de datos y la tabla se hayan creado correctamente
-- ============================================

