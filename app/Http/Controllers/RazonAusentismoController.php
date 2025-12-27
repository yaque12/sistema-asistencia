<?php

namespace App\Http\Controllers;

use App\Models\RazonAusentismo;
use App\Http\Requests\StoreRazonAusentismoRequest;
use App\Http\Requests\UpdateRazonAusentismoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Controlador para la gestión de razones de ausentismos
 * 
 * Este controlador maneja todas las operaciones CRUD para las razones de ausentismos
 * del sistema, incluyendo creación, lectura, actualización y eliminación.
 */
class RazonAusentismoController extends Controller
{
    /**
     * Mostrar la lista de razones de ausentismos con paginación
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Verificar autorización
        if (!Gate::allows('viewAny', RazonAusentismo::class)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver la lista de razones de ausentismos.',
                ], 403);
            }
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        // Obtener parámetros de búsqueda y paginación
        $busqueda = $request->get('buscar', '');
        $pagina = $request->get('pagina', 1);
        $porPagina = $request->get('por_pagina', 15);

        // Construir la consulta
        $query = RazonAusentismo::query();

        // Aplicar búsqueda si se proporciona
        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('razon', 'LIKE', "%{$busqueda}%")
                  ->orWhere('codigo_razon_ausentismo', 'LIKE', "%{$busqueda}%")
                  ->orWhere('descripcion', 'LIKE', "%{$busqueda}%");
            });
        }

        // Ordenar por fecha de creación (más recientes primero)
        $query->orderBy('created_at', 'desc');

        // Si es una petición AJAX, retornar JSON
        if ($request->expectsJson() || $request->ajax()) {
            $razones = $query->paginate($porPagina, ['*'], 'pagina', $pagina);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'razones' => $razones->items(),
                    'paginacion' => [
                        'pagina_actual' => $razones->currentPage(),
                        'ultima_pagina' => $razones->lastPage(),
                        'total' => $razones->total(),
                        'por_pagina' => $razones->perPage(),
                        'desde' => $razones->firstItem() ?? 0,
                        'hasta' => $razones->lastItem() ?? 0,
                    ],
                ],
            ]);
        }

        // Para peticiones normales, retornar la vista con datos iniciales
        $razones = $query->paginate($porPagina);
        
        return view('razones-ausentismos.index', [
            'razones' => $razones,
        ]);
    }

    /**
     * Crear una nueva razón de ausentismo
     * 
     * @param StoreRazonAusentismoRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRazonAusentismoRequest $request)
    {
        // Verificar autorización
        if (!Gate::allows('create', RazonAusentismo::class)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para crear razones de ausentismos.',
            ], 403);
        }

        try {
            // Crear la razón de ausentismo con los datos validados
            $razon = RazonAusentismo::create([
                'razon' => $request->razon,
                'codigo_razon_ausentismo' => $request->codigo_razon_ausentismo,
                'descripcion' => $request->descripcion,
            ]);

            // Retornar respuesta JSON con la razón de ausentismo creada
            return response()->json([
                'success' => true,
                'message' => 'Razón de ausentismo creada exitosamente.',
                'data' => $razon,
            ], 201);

        } catch (\Exception $e) {
            // Registrar el error en los logs
            \Log::error('Error al crear razón de ausentismo: ' . $e->getMessage());

            // Retornar error genérico
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al crear la razón de ausentismo. Por favor, intenta nuevamente.',
            ], 500);
        }
    }

    /**
     * Actualizar una razón de ausentismo existente
     * 
     * @param UpdateRazonAusentismoRequest $request
     * @param RazonAusentismo $razonAusentismo
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRazonAusentismoRequest $request, RazonAusentismo $razonAusentismo)
    {
        // Verificar autorización
        if (!Gate::allows('update', $razonAusentismo)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para actualizar esta razón de ausentismo.',
            ], 403);
        }

        try {
            // Actualizar los campos de la razón de ausentismo
            $razonAusentismo->razon = $request->razon;
            $razonAusentismo->codigo_razon_ausentismo = $request->codigo_razon_ausentismo;
            $razonAusentismo->descripcion = $request->descripcion;

            $razonAusentismo->save();

            // Retornar respuesta JSON con la razón de ausentismo actualizada
            return response()->json([
                'success' => true,
                'message' => 'Razón de ausentismo actualizada exitosamente.',
                'data' => $razonAusentismo,
            ]);

        } catch (\Exception $e) {
            // Registrar el error en los logs
            \Log::error('Error al actualizar razón de ausentismo: ' . $e->getMessage());

            // Retornar error genérico
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar la razón de ausentismo. Por favor, intenta nuevamente.',
            ], 500);
        }
    }

    /**
     * Eliminar una razón de ausentismo
     * 
     * @param RazonAusentismo $razonAusentismo
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(RazonAusentismo $razonAusentismo)
    {
        // Verificar autorización
        if (!Gate::allows('delete', $razonAusentismo)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar esta razón de ausentismo.',
            ], 403);
        }

        try {
            // Eliminar la razón de ausentismo
            $razonAusentismo->delete();

            // Retornar respuesta JSON de confirmación
            return response()->json([
                'success' => true,
                'message' => 'Razón de ausentismo eliminada exitosamente.',
            ]);

        } catch (\Exception $e) {
            // Registrar el error en los logs
            \Log::error('Error al eliminar razón de ausentismo: ' . $e->getMessage());

            // Retornar error genérico
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al eliminar la razón de ausentismo. Por favor, intenta nuevamente.',
            ], 500);
        }
    }
}
