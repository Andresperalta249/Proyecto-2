-- Insertar roles predeterminados si no existen
INSERT IGNORE INTO roles (id, nombre, descripcion) VALUES
(1, 'Superadministrador', 'Control total del sistema'),
(2, 'Administrador', 'Administración del sistema'),
(3, 'Usuario', 'Usuario normal del sistema');

-- Insertar permisos básicos si no existen
INSERT IGNORE INTO permisos (id, nombre, codigo) VALUES
(1, 'Gestionar Usuarios', 'gestionar_usuarios'),
(2, 'Gestionar Mascotas', 'gestionar_mascotas'),
(3, 'Gestionar Dispositivos', 'gestionar_dispositivos'),
(4, 'Ver Reportes', 'ver_reportes'),
(5, 'Gestionar Roles', 'gestionar_roles');

-- Insertar permisos adicionales si no existen
INSERT IGNORE INTO permisos (nombre, codigo) VALUES
('Ver Alertas', 'ver_alertas'),
('Ver Monitor', 'ver_monitor'),
('Ver Configuración', 'ver_configuracion');

-- Asignar permisos a roles
-- Superadministrador: todos los permisos
INSERT IGNORE INTO roles_permisos (rol_id, permiso_id) VALUES
(1, 1), -- gestionar_usuarios
(1, 2), -- gestionar_mascotas
(1, 3), -- gestionar_dispositivos
(1, 4), -- ver_reportes
(1, 5); -- gestionar_roles

-- Administrador: todos los permisos excepto gestionar_roles
INSERT IGNORE INTO roles_permisos (rol_id, permiso_id) VALUES
(2, 1), -- gestionar_usuarios
(2, 2), -- gestionar_mascotas
(2, 3), -- gestionar_dispositivos
(2, 4); -- ver_reportes

-- Usuario: solo gestionar mascotas
INSERT IGNORE INTO roles_permisos (rol_id, permiso_id) VALUES
(3, 2); -- gestionar_mascotas

-- Asignar nuevos permisos a Superadministrador (rol_id=1) y Administrador (rol_id=2)
INSERT IGNORE INTO roles_permisos (rol_id, permiso_id)
SELECT 1, id FROM permisos WHERE codigo IN ('ver_alertas', 'ver_monitor', 'ver_configuracion');
INSERT IGNORE INTO roles_permisos (rol_id, permiso_id)
SELECT 2, id FROM permisos WHERE codigo IN ('ver_alertas', 'ver_monitor', 'ver_configuracion');

-- Insertar usuarios predeterminados
INSERT IGNORE INTO usuarios (id, nombre, email, password, rol_id, estado) VALUES
(1, 'Super Administrador', 'superadmin@petcare.com', '$2y$10$1E7sTFivf5TR9XyZvRrYY.9KEI8vD1U5YFL0Cx3Lc80pVCxocx4Sa', 1, 'activo'),
(2, 'Administrador', 'admin@petmonitor.com', '$2y$10$1E7sTFivf5TR9XyZvRrYY.9KEI8vD1U5YFL0Cx3Lc80pVCxocx4Sa', 2, 'activo');

-- Nota: La contraseña encriptada corresponde a 'Admin123!' y 'Usuario123!' respectivamente 