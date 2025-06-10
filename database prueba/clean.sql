USE iot_pets;

-- Desactivar verificación de claves foráneas temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- Limpiar todas las tablas
TRUNCATE TABLE fcm_tokens;
TRUNCATE TABLE notificaciones;
TRUNCATE TABLE configuracion_notificaciones;
TRUNCATE TABLE alertas_monitoreo;
TRUNCATE TABLE datos_sensores;
TRUNCATE TABLE dispositivos;
TRUNCATE TABLE mascotas;
TRUNCATE TABLE usuarios;
TRUNCATE TABLE roles_permisos;
TRUNCATE TABLE permisos;
TRUNCATE TABLE roles;

-- Reactivar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1; 