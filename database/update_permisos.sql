-- Crear tabla de permisos si no existe
CREATE TABLE IF NOT EXISTS permisos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de relación roles-permisos si no existe
CREATE TABLE IF NOT EXISTS roles_permisos (
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (rol_id, permiso_id),
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar todos los permisos necesarios
INSERT IGNORE INTO permisos (nombre, codigo, descripcion) VALUES
('Gestionar Usuarios', 'gestionar_usuarios', 'Permite gestionar usuarios del sistema'),
('Gestionar Mascotas', 'gestionar_mascotas', 'Permite gestionar mascotas'),
('Gestionar Dispositivos', 'gestionar_dispositivos', 'Permite gestionar dispositivos'),
('Ver Reportes', 'ver_reportes', 'Permite ver reportes del sistema'),
('Gestionar Roles', 'gestionar_roles', 'Permite gestionar roles y permisos'),
('Ver Alertas', 'ver_alertas', 'Permite ver alertas del sistema'),
('Ver Monitor', 'ver_monitor', 'Permite ver el monitor en tiempo real'),
('Ver Configuración', 'ver_configuracion', 'Permite ver y modificar la configuración'),
('Editar Mascotas', 'editar_mascotas', 'Permite editar información de mascotas'),
('Eliminar Mascotas', 'eliminar_mascotas', 'Permite eliminar mascotas'),
('Crear Mascotas', 'crear_mascotas', 'Permite crear nuevas mascotas'),
('Ver Mascotas', 'ver_mascotas', 'Permite ver información de mascotas');

-- Asignar todos los permisos al Superadministrador (rol_id = 1)
INSERT IGNORE INTO roles_permisos (rol_id, permiso_id)
SELECT 1, id FROM permisos;

-- Limpiar permisos duplicados si existen
DELETE rp1 FROM roles_permisos rp1
INNER JOIN roles_permisos rp2
WHERE rp1.rol_id = rp2.rol_id 
AND rp1.permiso_id = rp2.permiso_id
AND rp1.creado_en > rp2.creado_en; 