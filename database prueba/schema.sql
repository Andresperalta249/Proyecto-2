-- =============================================
-- SCHEMA: Estructura completa de la base de datos
-- =============================================

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS iot_pets;
USE iot_pets;

-- =============================================
-- TABLAS DEL SISTEMA
-- =============================================

-- Tabla de roles
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de permisos
CREATE TABLE IF NOT EXISTS permisos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de relación roles-permisos
CREATE TABLE IF NOT EXISTS roles_permisos (
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (rol_id, permiso_id),
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL DEFAULT NULL,
    fcm_token VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de mascotas
CREATE TABLE IF NOT EXISTS mascotas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    especie VARCHAR(50) NOT NULL,
    raza VARCHAR(50),
    fecha_nacimiento DATE,
    peso DECIMAL(5,2),
    usuario_id INT NOT NULL,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    id_dispositivo INT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_dispositivo) REFERENCES dispositivos(id_dispositivo) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de dispositivos
CREATE TABLE IF NOT EXISTS dispositivos (
    id_dispositivo INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    mac VARCHAR(17) NOT NULL UNIQUE,
    estado ENUM('activo', 'inactivo', 'mantenimiento') NOT NULL DEFAULT 'activo',
    bateria INT NOT NULL DEFAULT 100,
    usuario_id INT NOT NULL,
    mascota_id INT NOT NULL,
    ultima_conexion TIMESTAMP NULL DEFAULT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de datos de sensores
CREATE TABLE IF NOT EXISTS datos_sensores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dispositivo_id INT NOT NULL,
    fecha TIMESTAMP NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    altitude DECIMAL(10,2) NOT NULL,
    speed DECIMAL(5,2) NOT NULL,
    bpm INT NOT NULL,
    temperatura DECIMAL(4,2) NOT NULL,
    bateria INT NOT NULL,
    ultima_conexion TIMESTAMP NULL DEFAULT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dispositivo_id) REFERENCES dispositivos(id_dispositivo) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de alertas de monitoreo
CREATE TABLE IF NOT EXISTS alertas_monitoreo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dispositivo_id INT NOT NULL,
    tipo_alerta ENUM('temperatura', 'frecuencia_cardiaca', 'actividad', 'bateria') NOT NULL,
    mensaje TEXT NOT NULL,
    nivel ENUM('baja', 'media', 'alta') NOT NULL,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dispositivo_id) REFERENCES dispositivos(id_dispositivo) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de configuración de notificaciones
CREATE TABLE IF NOT EXISTS configuracion_notificaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    tipos JSON NOT NULL COMMENT 'Configuración de tipos de notificaciones',
    metodos JSON NOT NULL COMMENT 'Configuración de métodos de notificación',
    frecuencia_email ENUM('inmediato', 'diario', 'semanal') NOT NULL DEFAULT 'inmediato',
    hora_inicio TIME NOT NULL DEFAULT '08:00:00',
    hora_fin TIME NOT NULL DEFAULT '22:00:00',
    notif_urgentes BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de notificaciones
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo ENUM('info', 'alerta', 'exito') NOT NULL DEFAULT 'info',
    enlace VARCHAR(255) DEFAULT NULL,
    leida BOOLEAN NOT NULL DEFAULT FALSE,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_lectura TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de tokens FCM
CREATE TABLE IF NOT EXISTS fcm_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    dispositivo VARCHAR(255) DEFAULT NULL,
    navegador VARCHAR(255) DEFAULT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultima_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ÍNDICES
-- =============================================

-- Índices para optimizar consultas
CREATE INDEX IF NOT EXISTS idx_notificaciones_fecha ON notificaciones(fecha_creacion);
CREATE INDEX IF NOT EXISTS idx_notificaciones_leida ON notificaciones(leida);
CREATE INDEX IF NOT EXISTS idx_fcm_tokens_activo ON fcm_tokens(activo);
CREATE INDEX IF NOT EXISTS idx_datos_sensores_fecha ON datos_sensores(fecha);
CREATE INDEX IF NOT EXISTS idx_alertas_monitoreo_fecha ON alertas_monitoreo(fecha_registro);

-- =============================================
-- TRIGGERS
-- =============================================

DELIMITER //

-- Trigger para actualizar fecha_lectura en notificaciones
CREATE TRIGGER actualizar_fecha_lectura
BEFORE UPDATE ON notificaciones
FOR EACH ROW
BEGIN
    IF NEW.leida = TRUE AND OLD.leida = FALSE THEN
        SET NEW.fecha_lectura = CURRENT_TIMESTAMP;
    END IF;
END//

-- Trigger para cuando se inserta un dispositivo
CREATE TRIGGER after_dispositivo_insert
AFTER INSERT ON dispositivos
FOR EACH ROW
BEGIN
    UPDATE mascotas 
    SET id_dispositivo = NEW.id_dispositivo
    WHERE id = NEW.mascota_id;
END//

-- Trigger para cuando se elimina un dispositivo
CREATE TRIGGER after_dispositivo_delete
AFTER DELETE ON dispositivos
FOR EACH ROW
BEGIN
    UPDATE mascotas 
    SET id_dispositivo = NULL
    WHERE id_dispositivo = OLD.id_dispositivo;
END//

-- Trigger para cuando se actualiza un dispositivo
CREATE TRIGGER after_dispositivo_update
AFTER UPDATE ON dispositivos
FOR EACH ROW
BEGIN
    IF NEW.mascota_id != OLD.mascota_id THEN
        -- Actualizar la mascota anterior
        UPDATE mascotas 
        SET id_dispositivo = NULL
        WHERE id = OLD.mascota_id;
        
        -- Actualizar la nueva mascota
        UPDATE mascotas 
        SET id_dispositivo = NEW.id_dispositivo
        WHERE id = NEW.mascota_id;
    END IF;
END//

DELIMITER ;

-- =============================================
-- PROCEDIMIENTOS ALMACENADOS
-- =============================================

DELIMITER //

-- Procedimiento para limpiar notificaciones antiguas
CREATE PROCEDURE limpiar_notificaciones_antiguas(IN dias INT)
BEGIN
    DELETE FROM notificaciones 
    WHERE fecha_creacion < DATE_SUB(NOW(), INTERVAL dias DAY);
END//

-- Procedimiento para obtener estadísticas de notificaciones
CREATE PROCEDURE obtener_estadisticas_notificaciones(IN usuario_id INT)
BEGIN
    SELECT 
        COUNT(*) as total_notificaciones,
        SUM(CASE WHEN leida = 1 THEN 1 ELSE 0 END) as notificaciones_leidas,
        SUM(CASE WHEN leida = 0 THEN 1 ELSE 0 END) as notificaciones_no_leidas,
        COUNT(DISTINCT tipo) as tipos_diferentes,
        MAX(fecha_creacion) as ultima_notificacion
    FROM notificaciones 
    WHERE usuario_id = usuario_id;
END//

DELIMITER ;

-- =============================================
-- DATOS INICIALES
-- =============================================

-- Insertar roles
INSERT INTO roles (nombre, descripcion) VALUES
('super_admin', 'Super Administrador del sistema'),
('admin', 'Administrador'),
('usuario', 'Usuario regular');

-- Insertar permisos
INSERT INTO permisos (nombre, descripcion) VALUES
-- Permisos del Dashboard
('ver_dashboard', 'Ver el panel de control principal'),
('ver_estadisticas', 'Ver estadísticas generales del sistema'),

-- Permisos de Usuarios
('ver_usuarios', 'Ver lista de usuarios'),
('ver_usuarios_propios', 'Ver información del propio usuario'),
('crear_usuarios', 'Crear nuevos usuarios'),
('editar_usuarios', 'Editar información de usuarios'),
('editar_usuarios_propios', 'Editar información propia'),
('eliminar_usuarios', 'Eliminar usuarios'),
('gestionar_roles_usuarios', 'Asignar roles a usuarios'),

-- Permisos de Mascotas
('ver_mascotas', 'Ver todas las mascotas del sistema'),
('ver_mascotas_propias', 'Ver mascotas propias'),
('crear_mascotas', 'Crear nuevas mascotas'),
('editar_mascotas', 'Editar información de mascotas'),
('editar_mascotas_propias', 'Editar información de mascotas propias'),
('eliminar_mascotas', 'Eliminar mascotas'),
('eliminar_mascotas_propias', 'Eliminar mascotas propias'),

-- Permisos de Dispositivos
('ver_dispositivos', 'Ver todos los dispositivos del sistema'),
('ver_dispositivos_propios', 'Ver dispositivos propios'),
('crear_dispositivos', 'Crear nuevos dispositivos'),
('editar_dispositivos', 'Editar información de dispositivos'),
('editar_dispositivos_propios', 'Editar información de dispositivos propios'),
('eliminar_dispositivos', 'Eliminar dispositivos'),
('eliminar_dispositivos_propios', 'Eliminar dispositivos propios'),
('asignar_dispositivos', 'Asignar dispositivos a mascotas'),

-- Permisos de Monitoreo
('ver_monitoreo', 'Ver datos de monitoreo de todas las mascotas'),
('ver_monitoreo_propio', 'Ver datos de monitoreo de mascotas propias'),
('exportar_monitoreo', 'Exportar datos de monitoreo'),
('configurar_alertas', 'Configurar alertas de monitoreo'),

-- Permisos de Alertas
('ver_alertas', 'Ver todas las alertas del sistema'),
('ver_alertas_propias', 'Ver alertas propias'),
('gestionar_alertas', 'Gestionar alertas del sistema'),
('gestionar_alertas_propias', 'Gestionar alertas propias'),

-- Permisos de Notificaciones
('ver_notificaciones', 'Ver todas las notificaciones'),
('ver_notificaciones_propias', 'Ver notificaciones propias'),
('gestionar_notificaciones', 'Gestionar notificaciones del sistema'),
('configurar_notificaciones', 'Configurar preferencias de notificaciones'),

-- Permisos de Roles y Permisos
('ver_roles', 'Ver roles del sistema'),
('crear_roles', 'Crear nuevos roles'),
('editar_roles', 'Editar roles existentes'),
('eliminar_roles', 'Eliminar roles'),
('gestionar_permisos', 'Gestionar permisos del sistema'),

-- Permisos de Reportes
('ver_reportes', 'Ver reportes del sistema'),
('generar_reportes', 'Generar nuevos reportes'),
('exportar_reportes', 'Exportar reportes en diferentes formatos');

-- Asignar permisos a roles
-- Super Admin: todos los permisos
INSERT INTO roles_permisos (rol_id, permiso_id)
SELECT 1, id FROM permisos;

-- Admin: permisos específicos
INSERT INTO roles_permisos (rol_id, permiso_id)
SELECT 2, id FROM permisos 
WHERE nombre IN (
    'ver_dashboard', 'ver_estadisticas',
    'ver_usuarios', 'crear_usuarios', 'editar_usuarios',
    'ver_mascotas', 'crear_mascotas', 'editar_mascotas',
    'ver_dispositivos', 'crear_dispositivos', 'editar_dispositivos', 'asignar_dispositivos',
    'ver_monitoreo', 'exportar_monitoreo', 'configurar_alertas',
    'ver_alertas', 'gestionar_alertas',
    'ver_notificaciones', 'gestionar_notificaciones',
    'ver_reportes', 'generar_reportes', 'exportar_reportes'
);

-- Usuario: permisos básicos
INSERT INTO roles_permisos (rol_id, permiso_id)
SELECT 3, id FROM permisos 
WHERE nombre IN (
    'ver_dashboard',
    'ver_usuarios_propios', 'editar_usuarios_propios',
    'ver_mascotas_propias', 'crear_mascotas', 'editar_mascotas_propias', 'eliminar_mascotas_propias',
    'ver_dispositivos_propios', 'editar_dispositivos_propios',
    'ver_monitoreo_propio', 'exportar_monitoreo',
    'ver_alertas_propias', 'gestionar_alertas_propias',
    'ver_notificaciones_propias', 'configurar_notificaciones',
    'ver_reportes'
);

-- Insertar super admin
INSERT INTO usuarios (nombre, email, password, rol_id) VALUES
('Super Admin', 'superadmin@petcare.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBAQN3J5J5QK8i', 1); 