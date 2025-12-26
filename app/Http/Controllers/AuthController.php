<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Controlador de Autenticación
 * 
 * Este controlador maneja todo lo relacionado con el inicio de sesión,
 * cierre de sesión y la creación automática del usuario supervisor.
 * 
 * El código está escrito de forma simple y educativa para que sea
 * fácil de entender para estudiantes de ingeniería.
 */
class AuthController extends Controller
{
    /**
     * Mostrar el formulario de login
     * 
     * Este método simplemente muestra la página de inicio de sesión.
     * Es la primera página que ve el usuario cuando accede al sistema.
     * 
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        // Retornar la vista del formulario de login
        // La vista está en: resources/views/auth/login.blade.php
        return view('auth.login');
    }

    /**
     * Procesar el inicio de sesión
     * 
     * Este método hace varias cosas importantes:
     * 1. Valida que los datos del formulario sean correctos
     * 2. Verifica si hay usuarios en la base de datos
     * 3. Si no hay usuarios, crea automáticamente el usuario "supervisor"
     * 4. Intenta iniciar sesión con las credenciales proporcionadas
     * 5. Si el login es exitoso, redirige a la página de bienvenida
     * 6. Si falla, muestra un mensaje de error
     * 
     * @param Request $request Los datos del formulario
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // PASO 1: Validar los datos del formulario
        // Esto asegura que el usuario haya llenado todos los campos correctamente
        $request->validate([
            'nombre_usuario' => 'required|string',  // El nombre de usuario es obligatorio
            'clave' => 'required|string|min:6',     // La contraseña es obligatoria y mínimo 6 caracteres
        ], [
            // Mensajes de error personalizados en español
            'nombre_usuario.required' => 'El nombre de usuario es obligatorio',
            'clave.required' => 'La contraseña es obligatoria',
            'clave.min' => 'La contraseña debe tener al menos 6 caracteres',
        ]);

        // PASO 2: Verificar si existe el usuario supervisor
        // Si no existe, lo creamos automáticamente
        $supervisorExiste = User::where('nombre_usuario', 'supervisor')->exists();

        // PASO 3: Si el supervisor no existe, crearlo automáticamente
        if (!$supervisorExiste) {
            // Crear el usuario supervisor con datos predeterminados
            // Usamos Hash::make() para encriptar la contraseña con bcrypt
            User::create([
                'nombre_usuario' => 'supervisor',           // Usuario: supervisor
                'clave' => Hash::make('supervisor'),        // Contraseña: supervisor (encriptada con bcrypt)
                'nombres' => 'Supervisor',                  // Nombre
                'apellidos' => 'Sistema',                   // Apellido
                'departamento_trabajo' => 'Administración', // Departamento
                'codigo_empleado' => 'SUP001',              // Código de empleado
            ]);

            // Mensaje informativo (opcional, puedes comentarlo si no quieres mostrarlo)
            // Este mensaje aparecerá cuando se cree el usuario supervisor
            session()->flash('info', 'Usuario supervisor creado automáticamente. Puede iniciar sesión con: supervisor / supervisor');
        }

        // PASO 4: Intentar iniciar sesión con las credenciales proporcionadas
        // Buscamos al usuario por nombre de usuario
        $usuario = User::where('nombre_usuario', $request->nombre_usuario)->first();

        // Verificar si el usuario existe
        if (!$usuario) {
            return back()->withErrors([
                'nombre_usuario' => 'El usuario no existe.',
            ])->withInput($request->only('nombre_usuario'));
        }
        
        // Verificar si la contraseña es correcta
        if (!Hash::check($request->clave, $usuario->clave)) {
            return back()->withErrors([
                'nombre_usuario' => 'La contraseña es incorrecta.',
            ])->withInput($request->only('nombre_usuario'));
        }
        
        // PASO 5: Si el login es exitoso
        
        // Iniciar sesión usando el objeto usuario completo
        // El segundo parámetro 'false' significa que NO recordar la sesión
        Auth::login($usuario, false);
        
        // Guardar la sesión explícitamente
        $request->session()->save();

        // Mensaje de éxito
        session()->flash('success', '¡Bienvenido al sistema!');

        // Redirigir a la página de bienvenida
        return redirect('/bienvenida');
    }

    /**
     * Cerrar sesión
     * 
     * Este método cierra la sesión del usuario actual y lo redirige
     * de vuelta al formulario de login.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Cerrar la sesión del usuario
        Auth::logout();

        // Invalidar la sesión actual
        $request->session()->invalidate();

        // Regenerar el token de la sesión para seguridad
        $request->session()->regenerateToken();

        // Mensaje de despedida
        session()->flash('success', 'Sesión cerrada correctamente');

        // Redirigir al formulario de login
        return redirect('/login');
    }

    /**
     * Mostrar la página de bienvenida
     * 
     * Esta página solo es accesible para usuarios autenticados.
     * Muestra información del usuario y opciones del sistema.
     * 
     * @return \Illuminate\View\View
     */
    public function bienvenida()
    {
        // Obtener el usuario autenticado
        // Auth::user() nos da toda la información del usuario que inició sesión
        $usuario = Auth::user();

        // Retornar la vista de bienvenida con los datos del usuario
        // La vista está en: resources/views/bienvenida.blade.php
        return view('bienvenida', [
            'usuario' => $usuario
        ]);
    }
}

