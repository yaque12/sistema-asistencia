<?php
/**
 * Script de Limpieza de Caché de Laravel
 * 
 * Este script ejecuta todos los comandos necesarios para limpiar
 * y optimizar el caché de Laravel, asegurando que todo funcione correctamente.
 * 
 * Uso: php limpiar-cache.php
 */

echo "========================================\n";
echo "LIMPIANDO CACHÉ DE LARAVEL\n";
echo "========================================\n\n";

// Verificar que estamos en el directorio correcto
if (!file_exists('artisan')) {
    echo "❌ Error: No se encontró el archivo 'artisan'\n";
    echo "   Asegúrate de ejecutar este script desde la raíz del proyecto Laravel.\n";
    exit(1);
}

// Función para ejecutar comandos y mostrar resultados
function ejecutarComando($comando, $descripcion) {
    echo "▶️  $descripcion...\n";
    
    $salida = [];
    $codigo = 0;
    
    exec($comando . ' 2>&1', $salida, $codigo);
    
    if ($codigo === 0) {
        echo "   ✅ Completado correctamente\n";
        if (!empty($salida)) {
            foreach ($salida as $linea) {
                if (!empty(trim($linea))) {
                    echo "      $linea\n";
                }
            }
        }
    } else {
        echo "   ⚠️  Advertencia: El comando puede no haberse ejecutado correctamente\n";
        if (!empty($salida)) {
            foreach ($salida as $linea) {
                if (!empty(trim($linea))) {
                    echo "      $linea\n";
                }
            }
        }
    }
    
    echo "\n";
    return $codigo === 0;
}

// Lista de comandos a ejecutar
$comandos = [
    [
        'comando' => 'php artisan optimize:clear',
        'descripcion' => 'Limpiando todos los cachés (config, route, view, cache, compiled)'
    ],
    [
        'comando' => 'php artisan config:clear',
        'descripcion' => 'Limpiando caché de configuración'
    ],
    [
        'comando' => 'php artisan cache:clear',
        'descripcion' => 'Limpiando caché de aplicación'
    ],
    [
        'comando' => 'php artisan route:clear',
        'descripcion' => 'Limpiando caché de rutas'
    ],
    [
        'comando' => 'php artisan view:clear',
        'descripcion' => 'Limpiando caché de vistas'
    ],
    [
        'comando' => 'php artisan config:cache',
        'descripcion' => 'Regenerando caché de configuración (para mejor rendimiento)'
    ],
];

$exitosos = 0;
$fallidos = 0;

// Ejecutar cada comando
foreach ($comandos as $item) {
    if (ejecutarComando($item['comando'], $item['descripcion'])) {
        $exitosos++;
    } else {
        $fallidos++;
    }
}

// Resumen final
echo "========================================\n";
echo "RESUMEN\n";
echo "========================================\n";
echo "✅ Comandos exitosos: $exitosos\n";
if ($fallidos > 0) {
    echo "⚠️  Comandos con advertencias: $fallidos\n";
}
echo "\n";

if ($fallidos === 0) {
    echo "🎉 ¡Todos los cachés se han limpiado correctamente!\n";
    echo "\n";
    echo "PRÓXIMOS PASOS:\n";
    echo "1. Si cambiaste algo en config/database.php o .env,\n";
    echo "   ejecuta: php artisan config:clear\n";
    echo "2. Reinicia el servidor si está corriendo\n";
    echo "3. Prueba tu aplicación\n";
} else {
    echo "⚠️  Algunos comandos tuvieron problemas.\n";
    echo "   Revisa los mensajes anteriores para más detalles.\n";
}

echo "========================================\n";

