USE iot_pets;

-- Insertar usuarios de prueba
INSERT INTO usuarios (nombre, email, password, rol_id) VALUES
('Juan Pérez', 'juan@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBAQN3J5J5QK8i', 3),
('María García', 'maria@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBAQN3J5J5QK8i', 3),
('Carlos López', 'carlos@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBAQN3J5J5QK8i', 3);

-- Insertar mascotas
INSERT INTO mascotas (nombre, especie, raza, fecha_nacimiento, peso, usuario_id) VALUES
('Max', 'Perro', 'Labrador', '2020-05-15', 25.5, 2),
('Luna', 'Gato', 'Siamés', '2021-03-20', 4.2, 2),
('Rocky', 'Perro', 'Pastor Alemán', '2019-11-10', 30.0, 3),
('Milo', 'Gato', 'Persa', '2022-01-05', 5.0, 3),
('Nina', 'Perro', 'Golden Retriever', '2021-07-22', 28.5, 3),
('Bella', 'Gato', 'Maine Coon', '2020-09-15', 6.5, 4),
('Simba', 'Perro', 'Husky Siberiano', '2021-12-01', 22.0, 4);

-- Insertar dispositivos
INSERT INTO dispositivos (nombre, mac, estado, bateria, usuario_id, mascota_id) VALUES
('Collar Max', '00:1A:2B:3C:4D:01', 'activo', 85, 2, 1),
('Collar Luna', '00:1A:2B:3C:4D:02', 'activo', 92, 2, 2),
('Collar Rocky', '00:1A:2B:3C:4D:03', 'activo', 78, 3, 3),
('Collar Milo', '00:1A:2B:3C:4D:04', 'activo', 95, 3, 4),
('Collar Nina', '00:1A:2B:3C:4D:05', 'activo', 88, 3, 5),
('Collar Bella', '00:1A:2B:3C:4D:06', 'activo', 82, 4, 6),
('Collar Simba', '00:1A:2B:3C:4D:07', 'activo', 90, 4, 7);

-- Insertar datos de sensores
INSERT INTO datos_sensores (dispositivo_id, fecha, latitude, longitude, altitude, speed, bpm, temperatura, bateria) VALUES
(1, NOW(), 19.4326, -99.1332, 2240.0, 0.0, 85, 38.2, 85),
(1, NOW(), 19.4327, -99.1333, 2240.0, 0.0, 82, 38.1, 85),
(2, NOW(), 19.4328, -99.1334, 2240.0, 0.0, 120, 38.5, 92),
(2, NOW(), 19.4329, -99.1335, 2240.0, 0.0, 118, 38.4, 92),
(3, NOW(), 19.4330, -99.1336, 2240.0, 0.0, 90, 38.3, 78),
(3, NOW(), 19.4331, -99.1337, 2240.0, 0.0, 88, 38.2, 78),
(4, NOW(), 19.4332, -99.1338, 2240.0, 0.0, 95, 38.1, 95),
(4, NOW(), 19.4333, -99.1339, 2240.0, 0.0, 93, 38.0, 95),
(5, NOW(), 19.4334, -99.1340, 2240.0, 0.0, 125, 38.6, 88),
(5, NOW(), 19.4335, -99.1341, 2240.0, 0.0, 123, 38.5, 88);

-- Insertar alertas de monitoreo
INSERT INTO alertas_monitoreo (dispositivo_id, tipo_alerta, mensaje, nivel) VALUES
(1, 'temperatura', 'Temperatura elevada detectada', 'alta'),
(1, 'actividad', 'Actividad inusual detectada', 'media'),
(2, 'frecuencia_cardiaca', 'Frecuencia cardíaca elevada', 'alta'),
(2, 'bateria', 'Batería baja', 'baja'),
(3, 'temperatura', 'Temperatura elevada detectada', 'alta'),
(3, 'actividad', 'Actividad inusual detectada', 'media');

-- Insertar configuración de notificaciones
INSERT INTO configuracion_notificaciones (usuario_id, tipos, metodos) VALUES
(1, '["alertas", "recordatorios", "estado"]', '["email", "push"]'),
(2, '["alertas", "recordatorios"]', '["email", "push"]'),
(3, '["alertas", "estado"]', '["push"]'),
(4, '["alertas", "recordatorios", "estado"]', '["email", "push"]');

-- Insertar notificaciones
INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES
(1, 'Bienvenido al Sistema', 'Bienvenido al Sistema de Monitoreo de Mascotas', 'info'),
(2, 'Alerta de Temperatura', 'Max tiene una temperatura elevada', 'alerta'),
(3, 'Batería Baja', 'El collar de Rocky tiene poca batería', 'alerta'),
(4, 'Actividad Normal', 'Simba está activo y saludable', 'exito');

-- Insertar tokens FCM de prueba
INSERT INTO fcm_tokens (usuario_id, token, dispositivo, navegador) VALUES
(1, 'fcm_token_super_admin_1', 'iPhone 12', 'Safari'),
(2, 'fcm_token_juan_1', 'Samsung Galaxy S21', 'Chrome'),
(3, 'fcm_token_maria_1', 'Google Pixel 6', 'Firefox'),
(4, 'fcm_token_carlos_1', 'iPhone 13', 'Safari'); 