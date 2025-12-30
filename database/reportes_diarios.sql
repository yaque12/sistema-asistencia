-- Crear tabla de reportes diarios (asistencia diaria de empleados)
CREATE TABLE IF NOT EXISTS `reportes_diarios` (
  `id_reporte` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fecha` DATE NOT NULL COMMENT 'Fecha del reporte de asistencia',
  `id_empleado` INT NOT NULL COMMENT 'ID del empleado (FK a tabla empleados)',
  `horas_trabajadas` DECIMAL(5,2) NULL DEFAULT 0.00 COMMENT 'Horas trabajadas en el día',
  `horas_ausentes` DECIMAL(5,2) NULL DEFAULT 0.00 COMMENT 'Horas ausentes en el día',
  `id_razon` INT UNSIGNED NULL COMMENT 'ID de la razón de ausentismo (FK a razones_ausentismos)',
  `comentarios` TEXT NULL COMMENT 'Comentarios adicionales sobre la asistencia',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_reporte`),
  UNIQUE KEY `reportes_diarios_fecha_empleado_unique` (`fecha`, `id_empleado`),
  KEY `reportes_diarios_fecha_index` (`fecha`),
  KEY `reportes_diarios_id_empleado_index` (`id_empleado`),
  KEY `reportes_diarios_id_razon_foreign` (`id_razon`),
  CONSTRAINT `reportes_diarios_id_empleado_foreign` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reportes_diarios_id_razon_foreign` FOREIGN KEY (`id_razon`) REFERENCES `razones_ausentismos` (`id_razon`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla para almacenar los reportes diarios de asistencia de empleados';

