<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;

/**
 * Controlador para la gestión de usuarios
 * 
 * Este controlador maneja todas las operaciones CRUD para los usuarios
 * del sistema, incluyendo creación, lectura, actualización y eliminación.
 */
class UsuarioController extends Controller
{
    /**
     * Mostrar la lista de usuarios con paginación
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Verificar autorización
        if (!Gate::allows('viewAny', User::class)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver la lista de usuarios.',
                ], 403);
            }
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        // Obtener parámetros de búsqueda y paginación
        $busqueda = $request->get('buscar', '');
        $pagina = $request->get('pagina', 1);
        $porPagina = $request->get('por_pagina', 15);

        // Construir la consulta
        $query = User::with('roles');

        // Aplicar búsqueda si se proporciona
        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre_usuario', 'LIKE', "%{$busqueda}%")
                  ->orWhere('nombres', 'LIKE', "%{$busqueda}%")
                  ->orWhere('apellidos', 'LIKE', "%{$busqueda}%")
                  ->orWhere('departamento_trabajo', 'LIKE', "%{$busqueda}%")
                  ->orWhere('codigo_empleado', 'LIKE', "%{$busqueda}%");
            });
        }

        // Ordenar por fecha de creación (más recientes primero)
        $query->orderBy('created_at', 'desc');

        // Si es una petición AJAX, retornar JSON
        if ($request->expectsJson() || $request->ajax()) {
            $usuarios = $query->paginate($porPagina, ['*'], 'pagina', $pagina);
            
            // Cargar roles para cada usuario
            $usuarios->getCollection()->transform(function ($usuario) {
                $usuario->roles_nombres = $usuario->roles->pluck('nombre_rol')->toArray();
                return $usuario;
            });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'usuarios' => $usuarios->items(),
                    'paginacion' => [
                        'pagina_actual' => $usuarios->currentPage(),
                        'ultima_pagina' => $usuarios->lastPage(),
                        'total' => $usuarios->total(),
                        'por_pagina' => $usuarios->perPage(),
                        'desde' => $usuarios->firstItem() ?? 0,
                        'hasta' => $usuarios->lastItem() ?? 0,
                    ],
                ],
            ]);
        }

        // Para peticiones normales, retornar la vista con datos iniciales
        $usuarios = $query->paginate($porPagina);
        
        return view('usuarios.index', [
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Crear un nuevo usuario
     * 
     * @param StoreUsuarioRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreUsuarioRequest $request)
    {
        // Verificar autorización
        if (!Gate::allows('create', User::class)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para crear usuarios.',
            ], 403);
        }

        try {
            // Crear el usuario con los datos validados
            $usuario = User::create([
                'nombre_usuario' => $request->nombre_usuario,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'departamento_trabajo' => $request->departamento_trabajo,
                'codigo_empleado' => $request->codigo_empleado,
                'clave' => Hash::make($request->clave), // Encriptar contraseña
            ]);

            // Sincronizar roles si se proporcionan
            if ($request->has('roles') && is_array($request->roles)) {
                $rolesIds = Role::whereIn('codigo_rol', $request->roles)->pluck('id_rol')->toArray();
                $usuario->roles()->sync($rolesIds);
            }

            // Cargar roles para la respuesta
            $usuario->load('roles');

            // Retornar respuesta JSON con el usuario creado
            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente.',
                'data' => $usuario->makeHidden(['clave']), // No exponer la contraseña
            ], 201);

        } catch (\Exception $e) {
            // Registrar el error en los logs
            \Log::error('Error al crear usuario: ' . $e->getMessage());

            // Retornar error genérico
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al crear el usuario. Por favor, intenta nuevamente.',
            ], 500);
        }
    }

    /**
     * Actualizar un usuario existente
     * 
     * @param UpdateUsuarioRequest $request
     * @param User $usuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUsuarioRequest $request, User $usuario)
    {
        // Verificar autorización
        if (!Gate::allows('update', $usuario)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para actualizar este usuario.',
            ], 403);
        }

        try {
            // Actualizar los campos del usuario
            $usuario->nombre_usuario = $request->nombre_usuario;
            $usuario->nombres = $request->nombres;
            $usuario->apellidos = $request->apellidos;
            $usuario->departamento_trabajo = $request->departamento_trabajo;
            $usuario->codigo_empleado = $request->codigo_empleado;

            // Actualizar contraseña solo si se proporciona
            if ($request->filled('clave')) {
                $usuario->clave = Hash::make($request->clave);
            }

            $usuario->save();

            // Sincronizar roles si se proporcionan
            if ($request->has('roles') && is_array($request->roles)) {
                $rolesIds = Role::whereIn('codigo_rol', $request->roles)->pluck('id_rol')->toArray();
                $usuario->roles()->sync($rolesIds);
            }

            // Cargar roles para la respuesta
            $usuario->load('roles');

            // Retornar respuesta JSON con el usuario actualizado
            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente.',
                'data' => $usuario->makeHidden(['clave']), // No exponer la contraseña
            ]);

        } catch (\Exception $e) {
            // Registrar el error en los logs
            \Log::error('Error al actualizar usuario: ' . $e->getMessage());

            // Retornar error genérico
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar el usuario. Por favor, intenta nuevamente.',
            ], 500);
        }
    }

    /**
     * Eliminar un usuario
     * 
     * @param User $usuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $usuario)
    {
        // Verificar autorización
        if (!Gate::allows('delete', $usuario)) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminarte a ti mismo.',
            ], 403);
        }

        try {
            // Eliminar el usuario
            $usuario->delete();

            // Retornar respuesta JSON de confirmación
            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente.',
            ]);

        } catch (\Exception $e) {
            // Registrar el error en los logs
            \Log::error('Error al eliminar usuario: ' . $e->getMessage());

            // Retornar error genérico
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al eliminar el usuario. Por favor, intenta nuevamente.',
            ], 500);
        }
    }

    /**
     * Obtener un usuario con sus roles (para edición)
     * 
     * @param User $usuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $usuario)
    {
        // Verificar autorización
        if (!Gate::allows('viewAny', User::class)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para ver este usuario.',
            ], 403);
        }

        // Cargar roles del usuario
        $usuario->load('roles');

        return response()->json([
            'success' => true,
            'data' => [
                'usuario' => $usuario->makeHidden(['clave']),
                'roles' => $usuario->roles->pluck('codigo_rol')->toArray(),
            ],
        ]);
    }
}

