<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Rutas Web del Sistema de Asistencia
|--------------------------------------------------------------------------
|
| Aquí definimos todas las rutas (URLs) de nuestra aplicación.
| Cada ruta está asociada a un controlador y un método específico.
|
*/

// Ruta principal: Redirige al login
Route::get('/', function () {
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación (Login/Logout)
|--------------------------------------------------------------------------
*/

// Mostrar el formulario de login
// URL: http://localhost/login
// Método HTTP: GET (para mostrar la página)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// Procesar el login (cuando el usuario envía el formulario)
// URL: http://localhost/login
// Método HTTP: POST (para enviar datos)
Route::post('/login', [AuthController::class, 'login']);

// Cerrar sesión
// URL: http://localhost/logout
// Método HTTP: POST (por seguridad, siempre usar POST para logout)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Solo para usuarios autenticados)
|--------------------------------------------------------------------------
|
| Estas rutas usan el middleware 'auth' que verifica que el usuario
| haya iniciado sesión. Si no ha iniciado sesión, lo redirige al login.
|
*/

// Página de bienvenida (protegida)
// URL: http://localhost/bienvenida
// Solo accesible si el usuario está autenticado
Route::get('/bienvenida', [AuthController::class, 'bienvenida'])
    ->middleware('auth')
    ->name('bienvenida');

// Módulo de Gestión de Usuarios (protegida)
// URL: http://localhost/usuarios
// Solo accesible si el usuario está autenticado
Route::get('/usuarios', function () {
    // Datos mock para el frontend
    $usuariosMock = collect([
        (object)[
            'id_usuario' => 1,
            'nombre_usuario' => 'jperez',
            'nombres' => 'Juan',
            'apellidos' => 'Pérez',
            'departamento_trabajo' => 'Recursos Humanos',
            'codigo_empleado' => 'EMP001',
            'created_at' => now()->subDays(30),
        ],
        (object)[
            'id_usuario' => 2,
            'nombre_usuario' => 'mgarcia',
            'nombres' => 'María',
            'apellidos' => 'García',
            'departamento_trabajo' => 'Contabilidad',
            'codigo_empleado' => 'EMP002',
            'created_at' => now()->subDays(25),
        ],
        
    ]);
    
    return view('usuarios.index', ['usuarios' => $usuariosMock]);
})->middleware('auth')->name('usuarios.index');
