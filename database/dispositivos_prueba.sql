-- Insertar dispositivos
INSERT INTO dispositivos (nombre, mac, estado, bateria, usuario_id, mascota_id, ultima_conexion) VALUES
-- Dispositivos para Juan Pérez
('Collar Max', '00:1A:2B:3C:4D:01', 'activo', 85, 3, 1, NOW()),
('Collar Luna', '00:1A:2B:3C:4D:02', 'activo', 92, 3, 2, NOW()),

-- Dispositivos para María García
('Collar Rocky', '00:1A:2B:3C:4D:03', 'activo', 78, 4, 3, NOW()),
('Collar Milo', '00:1A:2B:3C:4D:04', 'activo', 95, 4, 4, NOW()),
('Collar Nina', '00:1A:2B:3C:4D:05', 'activo', 88, 4, 5, NOW()),

-- Dispositivos para Carlos López
('Collar Bella', '00:1A:2B:3C:4D:06', 'activo', 82, 5, 6, NOW()),
('Collar Simba', '00:1A:2B:3C:4D:07', 'activo', 90, 5, 7, NOW()),

-- Dispositivos para Ana Martínez
('Collar Coco', '00:1A:2B:3C:4D:08', 'activo', 75, 6, 8, NOW()),
('Collar Lola', '00:1A:2B:3C:4D:09', 'activo', 93, 6, 9, NOW()),
('Collar Thor', '00:1A:2B:3C:4D:10', 'activo', 80, 6, 10, NOW()),

-- Dispositivos para Roberto Sánchez
('Collar Lucky', '00:1A:2B:3C:4D:11', 'activo', 87, 7, 11, NOW()),
('Collar Mia', '00:1A:2B:3C:4D:12', 'activo', 91, 7, 12, NOW()),
('Collar Rex', '00:1A:2B:3C:4D:13', 'activo', 79, 7, 13, NOW()),
('Collar Lily', '00:1A:2B:3C:4D:14', 'activo', 94, 7, 14, NOW());

-- Insertar datos de monitoreo (últimas 24 horas)
INSERT INTO datos_monitoreo (dispositivo_id, temperatura, frecuencia_cardiaca, actividad, bateria, fecha_registro) VALUES
-- Datos para el dispositivo de Max (últimas 24 horas)
(1, 38.2, 85, 'activo', 85, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, 38.1, 82, 'activo', 85, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 38.3, 88, 'activo', 86, DATE_SUB(NOW(), INTERVAL 3 HOUR)),

-- Datos para el dispositivo de Luna
(2, 38.5, 120, 'activo', 92, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2, 38.4, 118, 'activo', 92, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(2, 38.6, 122, 'activo', 93, DATE_SUB(NOW(), INTERVAL 3 HOUR)),

-- Datos para el dispositivo de Rocky
(3, 38.3, 90, 'activo', 78, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(3, 38.2, 88, 'activo', 78, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(3, 38.4, 92, 'activo', 79, DATE_SUB(NOW(), INTERVAL 3 HOUR)),

-- Datos para el dispositivo de Milo
(4, 38.1, 95, 'activo', 95, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(4, 38.0, 93, 'activo', 95, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(4, 38.2, 97, 'activo', 95, DATE_SUB(NOW(), INTERVAL 3 HOUR)),

-- Datos para el dispositivo de Nina
(5, 38.6, 125, 'activo', 88, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(5, 38.5, 123, 'activo', 88, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(5, 38.7, 127, 'activo', 89, DATE_SUB(NOW(), INTERVAL 3 HOUR));

-- Insertar alertas de monitoreo
INSERT INTO alertas_monitoreo (dispositivo_id, tipo_alerta, mensaje, nivel, fecha_registro) VALUES
-- Alertas para Max
(1, 'temperatura', 'Temperatura elevada detectada', 'alta', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 'actividad', 'Actividad inusual detectada', 'media', DATE_SUB(NOW(), INTERVAL 4 HOUR)),

-- Alertas para Luna
(2, 'frecuencia_cardiaca', 'Frecuencia cardíaca elevada', 'alta', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(2, 'bateria', 'Batería baja', 'baja', DATE_SUB(NOW(), INTERVAL 5 HOUR)),

-- Alertas para Rocky
(3, 'temperatura', 'Temperatura elevada detectada', 'alta', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(3, 'actividad', 'Actividad inusual detectada', 'media', DATE_SUB(NOW(), INTERVAL 6 HOUR)),

-- Alertas para Milo
(4, 'frecuencia_cardiaca', 'Frecuencia cardíaca elevada', 'alta', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(4, 'bateria', 'Batería baja', 'baja', DATE_SUB(NOW(), INTERVAL 4 HOUR)),

-- Alertas para Nina
(5, 'temperatura', 'Temperatura elevada detectada', 'alta', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(5, 'actividad', 'Actividad inusual detectada', 'media', DATE_SUB(NOW(), INTERVAL 5 HOUR)); 