-- Eliminar la tabla si existe
DROP TABLE IF EXISTS `configuraciones_alertas`;

-- Crear la tabla con la nueva estructura
CREATE TABLE `configuraciones_alertas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_sensor` varchar(50) NOT NULL COMMENT 'Temperatura, RitmoCardiaco, Bateria',
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre descriptivo de la alerta',
  `valor_minimo` decimal(10,2) NOT NULL COMMENT 'Valor mínimo para alerta amarilla',
  `valor_maximo` decimal(10,2) NOT NULL COMMENT 'Valor máximo para alerta amarilla',
  `valor_critico_minimo` decimal(10,2) NOT NULL COMMENT 'Valor mínimo para alerta roja',
  `valor_critico_maximo` decimal(10,2) NOT NULL COMMENT 'Valor máximo para alerta roja',
  `unidad_medida` varchar(20) NOT NULL COMMENT 'C, BPM, %, etc',
  `mensaje_alerta` text NOT NULL COMMENT 'Mensaje personalizado para la alerta',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipo_sensor` (`tipo_sensor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar configuraciones por defecto
INSERT INTO `configuraciones_alertas` 
(`tipo_sensor`, `nombre`, `valor_minimo`, `valor_maximo`, `valor_critico_minimo`, `valor_critico_maximo`, `unidad_medida`, `mensaje_alerta`) VALUES
('temperatura', 'Temperatura Corporal', 36.0, 39.0, 35.0, 40.0, 'C', 'La temperatura está fuera del rango normal'),
('ritmo_cardiaco', 'Ritmo Cardíaco', 60, 100, 50, 120, 'BPM', 'El ritmo cardíaco está fuera del rango normal'),
('bateria', 'Nivel de Batería', 20, 50, 10, 60, '%', 'El nivel de batería está bajo'); 