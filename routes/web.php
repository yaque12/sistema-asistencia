<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;

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

/*
||--------------------------------------------------------------------------
|| Módulo de Gestión de Usuarios
||--------------------------------------------------------------------------
||
|| Rutas para gestionar usuarios del sistema.
|| Todas las rutas requieren autenticación.
||
*/

// Mostrar la vista de usuarios (GET)
Route::get('/usuarios', [UsuarioController::class, 'index'])
    ->middleware('auth')
    ->name('usuarios.index');

// Crear nuevo usuario (POST)
Route::post('/usuarios', [UsuarioController::class, 'store'])
    ->middleware('auth')
    ->name('usuarios.store');

// Actualizar usuario existente (PUT)
Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])
    ->middleware('auth')
    ->name('usuarios.update');

// Eliminar usuario (DELETE)
Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])
    ->middleware('auth')
    ->name('usuarios.destroy');

/*
|||--------------------------------------------------------------------------
||| Módulo de Gestión de Empleados (Solo Frontend)
|||--------------------------------------------------------------------------
|||
||| Ruta para mostrar la vista de empleados.
||| La funcionalidad se maneja completamente en el frontend.
|||
*/

// Mostrar la vista de empleados (GET)
Route::get('/empleados', function () {
    return view('empleados.index');
})
    ->middleware('auth')
    ->name('empleados.index');
