CREATE TABLE IF NOT EXISTS `configuracion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_sistema` varchar(100) NOT NULL DEFAULT 'Sistema de Monitoreo de Mascotas',
  `email_contacto` varchar(100) NOT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `tiempo_actualizacion` int(11) NOT NULL DEFAULT '30',
  `dias_retener_logs` int(11) NOT NULL DEFAULT '30',
  `notificaciones_email` tinyint(1) NOT NULL DEFAULT '1',
  `notificaciones_push` tinyint(1) NOT NULL DEFAULT '1',
  `tema_oscuro` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_actualizacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar configuración por defecto
INSERT INTO `configuracion` (`id`, `nombre_sistema`, `email_contacto`, `telefono_contacto`, `direccion`, 
                           `tiempo_actualizacion`, `dias_retener_logs`, `notificaciones_email`, 
                           `notificaciones_push`, `tema_oscuro`, `fecha_actualizacion`) 
VALUES (1, 'Sistema de Monitoreo de Mascotas', 'admin@example.com', NULL, NULL, 
        30, 30, 1, 1, 0, CURRENT_TIMESTAMP); 