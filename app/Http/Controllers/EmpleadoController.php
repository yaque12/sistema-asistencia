<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Http\Requests\StoreEmpleadoRequest;
use App\Http\Requests\UpdateEmpleadoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Controlador para la gestión de empleados
 * 
 * Este controlador maneja todas las operaciones CRUD para los empleados
 * del sistema, incluyendo creación, lectura, actualización y eliminación.
 */
class EmpleadoController extends Controller
{
    /**
     * Mostrar la lista de empleados con paginación
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Verificar autorización
        if (!Gate::allows('viewAny', Empleado::class)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver la lista de empleados.',
                ], 403);
            }
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        // Obtener parámetros de búsqueda y paginación
        $busqueda = $request->get('buscar', '');
        $pagina = $request->get('pagina', 1);
        $porPagina = $request->get('por_pagina', 15);

        // Construir la consulta
        $query = Empleado::query();

        // Aplicar búsqueda si se proporciona
        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombres', 'LIKE', "%{$busqueda}%")
                  ->orWhere('apellidos', 'LIKE', "%{$busqueda}%")
                  ->orWhere('departamento', 'LIKE', "%{$busqueda}%")
                  ->orWhere('codigo_empleado', 'LIKE', "%{$busqueda}%");
            });
        }

        // Ordenar por fecha de creación (más recientes primero)
        $query->orderBy('created_at', 'desc');

        // Si es una petición AJAX, retornar JSON
        if ($request->expectsJson() || $request->ajax()) {
            $empleados = $query->paginate($porPagina, ['*'], 'pagina', $pagina);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'empleados' => $empleados->items(),
                    'paginacion' => [
                        'pagina_actual' => $empleados->currentPage(),
                        'ultima_pagina' => $empleados->lastPage(),
                        'total' => $empleados->total(),
                        'por_pagina' => $empleados->perPage(),
                        'desde' => $empleados->firstItem() ?? 0,
                        'hasta' => $empleados->lastItem() ?? 0,
                    ],
                ],
            ]);
        }

        // Para peticiones normales, retornar la vista con datos iniciales
        $empleados = $query->paginate($porPagina);
        
        return view('empleados.index', [
            'empleados' => $empleados,
        ]);
    }

    /**
     * Crear un nuevo empleado
     * 
     * @param StoreEmpleadoRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreEmpleadoRequest $request)
    {
        // Verificar autorización
        if (!Gate::allows('create', Empleado::class)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para crear empleados.',
            ], 403);
        }

        try {
            // Crear el empleado con los datos validados
            $empleado = Empleado::create([
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'departamento' => $request->departamento,
                'codigo_empleado' => $request->codigo_empleado,
                'fecha_ingreso' => $request->fecha_ingreso,
            ]);

            // Retornar respuesta JSON con el empleado creado
            return response()->json([
                'success' => true,
                'message' => 'Empleado creado exitosamente.',
                'data' => $empleado,
            ], 201);

        } catch (\Exception $e) {
            // Registrar el error en los logs
            \Log::error('Error al crear empleado: ' . $e->getMessage());

            // Retornar error genérico
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al crear el empleado. Por favor, intenta nuevamente.',
            ], 500);
        }
    }

    /**
     * Actualizar un empleado existente
     * 
     * @param UpdateEmpleadoRequest $request
     * @param Empleado $empleado
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateEmpleadoRequest $request, Empleado $empleado)
    {
        // Verificar autorización
        if (!Gate::allows('update', $empleado)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para actualizar este empleado.',
            ], 403);
        }

        try {
            // Actualizar los campos del empleado
            $empleado->nombres = $request->nombres;
            $empleado->apellidos = $request->apellidos;
            $empleado->departamento = $request->departamento;
            $empleado->codigo_empleado = $request->codigo_empleado;
            $empleado->fecha_ingreso = $request->fecha_ingreso;

            $empleado->save();

            // Retornar respuesta JSON con el empleado actualizado
            return response()->json([
                'success' => true,
                'message' => 'Empleado actualizado exitosamente.',
                'data' => $empleado,
            ]);

        } catch (\Exception $e) {
            // Registrar el error en los logs
            \Log::error('Error al actualizar empleado: ' . $e->getMessage());

            // Retornar error genérico
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar el empleado. Por favor, intenta nuevamente.',
            ], 500);
        }
    }

    /**
     * Eliminar un empleado
     * 
     * @param Empleado $empleado
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Empleado $empleado)
    {
        // Verificar autorización
        if (!Gate::allows('delete', $empleado)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar este empleado.',
            ], 403);
        }

        try {
            // Eliminar el empleado
            $empleado->delete();

            // Retornar respuesta JSON de confirmación
            return response()->json([
                'success' => true,
                'message' => 'Empleado eliminado exitosamente.',
            ]);

        } catch (\Exception $e) {
            // Registrar el error en los logs
            \Log::error('Error al eliminar empleado: ' . $e->getMessage());

            // Retornar error genérico
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al eliminar el empleado. Por favor, intenta nuevamente.',
            ], 500);
        }
    }
}

