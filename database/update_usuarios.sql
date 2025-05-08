-- Actualizar la tabla usuarios
ALTER TABLE usuarios
ADD COLUMN telefono VARCHAR(15) NOT NULL AFTER password,
ADD COLUMN direccion TEXT NOT NULL AFTER telefono,
ADD COLUMN rol_id INT NOT NULL DEFAULT 3 AFTER direccion,
ADD COLUMN estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo' AFTER rol_id,
ADD COLUMN creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER estado,
ADD COLUMN ultimo_acceso TIMESTAMP NULL DEFAULT NULL AFTER creado_en,
ADD FOREIGN KEY (rol_id) REFERENCES roles(id);

-- Crear tabla roles si no existe
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar roles básicos si no existen
INSERT IGNORE INTO roles (id, nombre, descripcion) VALUES
(1, 'Administrador', 'Acceso total al sistema'),
(2, 'Veterinario', 'Acceso a funciones veterinarias'),
(3, 'Usuario', 'Usuario normal del sistema'); 