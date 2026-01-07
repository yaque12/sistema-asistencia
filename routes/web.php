<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\RazonAusentismoController;
use App\Http\Controllers\ReporteDiarioController;
use App\Http\Controllers\ConsultaDescargaController;
use App\Http\Controllers\GenerarReporteController;
use App\Http\Controllers\Api\AsistenciaController;

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
|| API - Estadísticas de Asistencia
||--------------------------------------------------------------------------
||
|| Rutas API para obtener estadísticas de asistencia.
|| Todas las rutas requieren autenticación.
||
*/

// Obtener estadísticas de asistencia (GET)
// URL: http://localhost/api/asistencia/estadisticas
// Parámetros opcionales: fecha (formato Y-m-d)
Route::get('/api/asistencia/estadisticas', [AsistenciaController::class, 'obtenerEstadisticas'])
    ->middleware('auth')
    ->name('api.asistencia.estadisticas');

// Obtener estadísticas de ausentismo por razón (GET)
// URL: http://localhost/api/asistencia/ausentismo-por-razon
// Parámetros opcionales: fecha (formato Y-m-d)
Route::get('/api/asistencia/ausentismo-por-razon', [AsistenciaController::class, 'obtenerAusentismoPorRazon'])
    ->middleware('auth')
    ->name('api.asistencia.ausentismo-por-razon');

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

// Obtener usuario con roles (GET) - Para edición
Route::get('/usuarios/{usuario}', [UsuarioController::class, 'show'])
    ->middleware('auth')
    ->name('usuarios.show');

// Obtener roles disponibles (GET)
Route::get('/roles', [RolController::class, 'index'])
    ->middleware('auth')
    ->name('roles.index');

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
Route::get('/empleados', [EmpleadoController::class, 'index'])
    ->middleware('auth')
    ->name('empleados.index');

// Crear nuevo empleado (POST)
Route::post('/empleados', [EmpleadoController::class, 'store'])
    ->middleware('auth')
    ->name('empleados.store');

// Actualizar empleado existente (PUT)
Route::put('/empleados/{empleado}', [EmpleadoController::class, 'update'])
    ->middleware('auth')
    ->name('empleados.update');

// Eliminar empleado (DELETE)
Route::delete('/empleados/{empleado}', [EmpleadoController::class, 'destroy'])
    ->middleware('auth')
    ->name('empleados.destroy');

/*
|--------------------------------------------------------------------------
| Módulo de Gestión de Razones de Ausentismos
|--------------------------------------------------------------------------
|
| Rutas para gestionar razones de ausentismos del sistema.
| Todas las rutas requieren autenticación.
|
*/

// Mostrar la vista de razones de ausentismos (GET)
Route::get('/razones-ausentismos', [RazonAusentismoController::class, 'index'])
    ->middleware('auth')
    ->name('razones-ausentismos.index');

// Crear nueva razón de ausentismo (POST)
Route::post('/razones-ausentismos', [RazonAusentismoController::class, 'store'])
    ->middleware('auth')
    ->name('razones-ausentismos.store');

// Actualizar razón de ausentismo existente (PUT)
Route::put('/razones-ausentismos/{razonAusentismo}', [RazonAusentismoController::class, 'update'])
    ->middleware('auth')
    ->name('razones-ausentismos.update');

// Eliminar razón de ausentismo (DELETE)
Route::delete('/razones-ausentismos/{razonAusentismo}', [RazonAusentismoController::class, 'destroy'])
    ->middleware('auth')
    ->name('razones-ausentismos.destroy');

/*
||--------------------------------------------------------------------------
|| Módulo de Reporte Diario
||--------------------------------------------------------------------------
||
|| Rutas para gestionar reportes diarios de asistencia.
|| Todas las rutas requieren autenticación.
||
*/

// Mostrar la vista de reporte diario (GET)
Route::get('/reporte-diario', [ReporteDiarioController::class, 'index'])
    ->middleware('auth')
    ->name('reporte-diario.index');

// Guardar múltiples reportes diarios (guardado masivo) (POST)
Route::post('/reporte-diario/guardar-masivo', [ReporteDiarioController::class, 'guardarMasivo'])
    ->middleware('auth')
    ->name('reporte-diario.guardar-masivo');

// Crear nuevo reporte diario (POST)
Route::post('/reporte-diario', [ReporteDiarioController::class, 'store'])
    ->middleware('auth')
    ->name('reporte-diario.store');

// Actualizar reporte diario existente (PUT)
Route::put('/reporte-diario/{reporteDiario}', [ReporteDiarioController::class, 'update'])
    ->middleware('auth')
    ->name('reporte-diario.update');

// Eliminar reporte diario (DELETE)
Route::delete('/reporte-diario/{reporteDiario}', [ReporteDiarioController::class, 'destroy'])
    ->middleware('auth')
    ->name('reporte-diario.destroy');

/*
|--------------------------------------------------------------------------
| Módulo de Consultas y Descargas
|--------------------------------------------------------------------------
|
| Rutas para consultas y descargas de reportes.
| Todas las rutas requieren autenticación.
|
*/

// Mostrar la vista de consultas y descargas (GET)
Route::get('/consultas-descargas', [ConsultaDescargaController::class, 'index'])
    ->middleware('auth')
    ->name('consultas-descargas.index');

// Consultar reportes con filtros (POST)
Route::post('/consultas-descargas/consultar', [ConsultaDescargaController::class, 'consultar'])
    ->middleware('auth')
    ->name('consultas-descargas.consultar');

// Descargar reportes en Excel (GET)
Route::get('/consultas-descargas/descargar', [ConsultaDescargaController::class, 'descargar'])
    ->middleware('auth')
    ->name('consultas-descargas.descargar');

/*
|--------------------------------------------------------------------------
| Módulo de Generar Reporte
|--------------------------------------------------------------------------
|
| Rutas para generar y guardar reportes.
| Todas las rutas requieren autenticación.
|
*/

// Mostrar la vista de generar reporte (GET)
Route::get('/generar-reporte', [GenerarReporteController::class, 'index'])
    ->middleware('auth')
    ->name('generar-reporte.index');

// Crear nuevo reporte (POST)
Route::post('/generar-reporte', [GenerarReporteController::class, 'store'])
    ->middleware('auth')
    ->name('generar-reporte.store');

// Actualizar reporte existente (PUT)
Route::put('/generar-reporte/{reporte}', [GenerarReporteController::class, 'update'])
    ->middleware('auth')
    ->name('generar-reporte.update');

// Eliminar reporte (DELETE)
Route::delete('/generar-reporte/{reporte}', [GenerarReporteController::class, 'destroy'])
    ->middleware('auth')
    ->name('generar-reporte.destroy');
