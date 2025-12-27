-- Crear tabla de razones de ausentismos
CREATE TABLE IF NOT EXISTS `razones_ausentismos` (
  `id_razon` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `razon` VARCHAR(255) NOT NULL COMMENT 'Nombre de la razón de ausentismo',
  `codigo_razon_ausentismo` VARCHAR(50) NOT NULL COMMENT 'Código único de la razón de ausentismo',
  `descripcion` TEXT NULL COMMENT 'Descripción detallada de la razón de ausentismo',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_razon`),
  UNIQUE KEY `razones_ausentismos_codigo_unique` (`codigo_razon_ausentismo`),
  KEY `razones_ausentismos_razon_index` (`razon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla para almacenar las razones de ausentismos';

