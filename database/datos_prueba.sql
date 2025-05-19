-- Insertar usuarios de prueba
INSERT INTO usuarios (nombre, email, password, telefono, direccion, rol_id, estado) VALUES
('Juan Pérez', 'juan@example.com', '$2y$10$1E7sTFivf5TR9XyZvRrYY.9KEI8vD1U5YFL0Cx3Lc80pVCxocx4Sa', '555-0101', 'Calle Principal 123', 3, 'activo'),
('María García', 'maria@example.com', '$2y$10$1E7sTFivf5TR9XyZvRrYY.9KEI8vD1U5YFL0Cx3Lc80pVCxocx4Sa', '555-0102', 'Avenida Central 456', 3, 'activo'),
('Carlos López', 'carlos@example.com', '$2y$10$1E7sTFivf5TR9XyZvRrYY.9KEI8vD1U5YFL0Cx3Lc80pVCxocx4Sa', '555-0103', 'Plaza Mayor 789', 3, 'activo'),
('Ana Martínez', 'ana@example.com', '$2y$10$1E7sTFivf5TR9XyZvRrYY.9KEI8vD1U5YFL0Cx3Lc80pVCxocx4Sa', '555-0104', 'Calle Nueva 321', 3, 'activo'),
('Roberto Sánchez', 'roberto@example.com', '$2y$10$1E7sTFivf5TR9XyZvRrYY.9KEI8vD1U5YFL0Cx3Lc80pVCxocx4Sa', '555-0105', 'Avenida Sur 654', 3, 'activo');

-- Insertar mascotas para los usuarios
INSERT INTO mascotas (nombre, especie, tamano, fecha_nacimiento, usuario_id, estado, genero) VALUES
-- Mascotas de Juan Pérez
('Max', 'Perro', 'Mediano', '2020-05-15', 3, 'activo', 'Macho'),
('Luna', 'Gato', 'Pequeño', '2021-03-20', 3, 'activo', 'Hembra'),

-- Mascotas de María García
('Rocky', 'Perro', 'Grande', '2019-11-10', 4, 'activo', 'Macho'),
('Milo', 'Perro', 'Pequeño', '2022-01-05', 4, 'activo', 'Macho'),
('Nina', 'Gato', 'Mediano', '2020-08-15', 4, 'activo', 'Hembra'),

-- Mascotas de Carlos López
('Bella', 'Perro', 'Grande', '2018-07-22', 5, 'activo', 'Hembra'),
('Simba', 'Gato', 'Mediano', '2021-06-30', 5, 'activo', 'Macho'),

-- Mascotas de Ana Martínez
('Coco', 'Perro', 'Pequeño', '2022-02-18', 6, 'activo', 'Macho'),
('Lola', 'Gato', 'Pequeño', '2021-12-01', 6, 'activo', 'Hembra'),
('Thor', 'Perro', 'Grande', '2019-04-25', 6, 'activo', 'Macho'),

-- Mascotas de Roberto Sánchez
('Lucky', 'Perro', 'Mediano', '2020-09-12', 7, 'activo', 'Macho'),
('Mia', 'Gato', 'Mediano', '2021-07-15', 7, 'activo', 'Hembra'),
('Rex', 'Perro', 'Grande', '2018-11-30', 7, 'activo', 'Macho'),
('Lily', 'Gato', 'Pequeño', '2022-03-10', 7, 'activo', 'Hembra'); 