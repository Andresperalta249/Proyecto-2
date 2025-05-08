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

-- Índices para optimizar consultas
CREATE INDEX idx_notificaciones_usuario ON notificaciones(usuario_id);
CREATE INDEX idx_notificaciones_fecha ON notificaciones(fecha_creacion);
CREATE INDEX idx_notificaciones_leida ON notificaciones(leida);
CREATE INDEX idx_fcm_tokens_usuario ON fcm_tokens(usuario_id);
CREATE INDEX idx_fcm_tokens_activo ON fcm_tokens(activo);

-- Trigger para actualizar fecha_lectura al marcar como leída
DELIMITER //
CREATE TRIGGER actualizar_fecha_lectura
BEFORE UPDATE ON notificaciones
FOR EACH ROW
BEGIN
    IF NEW.leida = TRUE AND OLD.leida = FALSE THEN
        SET NEW.fecha_lectura = CURRENT_TIMESTAMP;
    END IF;
END//
DELIMITER ;

-- Procedimiento almacenado para limpiar notificaciones antiguas
DELIMITER //
CREATE PROCEDURE limpiar_notificaciones_antiguas(IN dias INT)
BEGIN
    DELETE FROM notificaciones 
    WHERE fecha_creacion < DATE_SUB(NOW(), INTERVAL dias DAY);
END//
DELIMITER ;

-- Procedimiento almacenado para obtener estadísticas de notificaciones
DELIMITER //
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

-- Agregar columna fcm_token a la tabla usuarios
ALTER TABLE `usuarios` 
ADD COLUMN `fcm_token`