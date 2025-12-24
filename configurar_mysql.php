<?php
/**
 * Script para Configurar MySQL Automáticamente
 * 
 * Este script modifica el archivo .env para usar MySQL en lugar de SQLite.
 * Ejecuta: php configurar_mysql.php
 */

echo "========================================\n";
echo "CONFIGURACIÓN AUTOMÁTICA DE MYSQL\n";
echo "========================================\n\n";

$envFile = '.env';

if (!file_exists($envFile)) {
    echo "❌ Archivo .env no encontrado\n";
    echo "   Copiando desde .env.example...\n";
    
    if (file_exists('.env.example')) {
        copy('.env.example', $envFile);
        echo "   ✅ Archivo .env creado desde .env.example\n";
    } else {
        echo "   ❌ Archivo .env.example tampoco existe\n";
        exit(1);
    }
}

// Leer el contenido del archivo .env
$content = file_get_contents($envFile);

// Configuraciones a aplicar
$configs = [
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_DATABASE' => 'sistema_asistencia',
    'DB_USERNAME' => 'root',
    'DB_PASSWORD' => '',
];

echo "Configurando variables de base de datos...\n\n";

$modified = false;

foreach ($configs as $key => $value) {
    // Buscar si la variable ya existe
    $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
    
    if (preg_match($pattern, $content)) {
        // Reemplazar el valor existente
        $newLine = "$key=$value";
        $content = preg_replace($pattern, $newLine, $content);
        echo "   ✅ $key actualizado a: $value\n";
        $modified = true;
    } else {
        // Agregar la variable si no existe
        // Buscar la sección de base de datos o agregar al final
        if (preg_match('/^DB_CONNECTION=/m', $content)) {
            // Ya hay alguna configuración DB_, agregar después
            $content .= "\n$key=$value";
        } else {
            // No hay configuración DB_, agregar sección completa
            $content .= "\n\n# Database Configuration\n";
            foreach ($configs as $k => $v) {
                $content .= "$k=$v\n";
            }
            break; // Ya agregamos todas
        }
        echo "   ✅ $key agregado: $value\n";
        $modified = true;
    }
}

// Guardar el archivo
if ($modified) {
    file_put_contents($envFile, $content);
    echo "\n✅ Archivo .env actualizado correctamente\n";
} else {
    echo "\nℹ️  El archivo .env ya estaba configurado correctamente\n";
}

echo "\n========================================\n";
echo "PRÓXIMOS PASOS\n";
echo "========================================\n";
echo "1. Ejecuta: php artisan config:clear\n";
echo "2. Reinicia el servidor: php artisan serve\n";
echo "3. Intenta iniciar sesión nuevamente\n";
echo "========================================\n";


