<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Http\Requests\StoreReporteRequest;
use App\Http\Requests\UpdateReporteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Controlador para la gestión de reportes generados
 * 
 * Este controlador maneja la creación de reportes en el sistema,
 * incluyendo la validación y almacenamiento de los datos.
 */
class GenerarReporteController extends Controller
{
    /**
     * Mostrar la vista principal de generar reportes
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Verificar autorización
        if (!Gate::allows('viewAny', Reporte::class)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver los reportes.',
                ], 403);
            }
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        // Si es una petición AJAX, retornar JSON con los reportes
        if ($request->expectsJson() || $request->ajax()) {
            $reportes = Reporte::orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'reportes' => $reportes,
                ],
            ]);
        }

        // Retornar la vista
        return view('generar-reporte.index');
    }

    /**
     * Crear un nuevo reporte
     * 
     * @param StoreReporteRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreReporteRequest $request)
    {
        // Verificar autorización
        if (!Gate::allows('create', Reporte::class)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para crear reportes.',
            ], 403);
        }

        try {
            // Crear el reporte con los datos validados
            $reporte = Reporte::create([
                'fecha' => $request->fecha,
                'estado' => $request->estado,
                'comentarios' => $request->comentarios,
            ]);

            // Retornar respuesta JSON con el reporte creado
            return response()->json([
                'success' => true,
                'message' => 'Reporte creado exitosamente.',
                'data' => $reporte,
            ], 201);

        } catch (\Exception $e) {
            // Registrar el error en los logs
            \Log::error('Error al crear reporte: ' . $e->getMessage());

            // Retornar error genérico
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al crear el reporte. Por favor, intenta nuevamente.',
            ], 500);
        }
    }

    /**
     * Actualizar un reporte existente (cambiar estado)
     * 
     * @param UpdateReporteRequest $request
     * @param Reporte $reporte
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateReporteRequest $request, Reporte $reporte)
    {
        // Verificar autorización
        if (!Gate::allows('update', $reporte)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para actualizar este reporte.',
            ], 403);
        }

        try {
            // Actualizar solo el estado
            $reporte->estado = $request->estado;
            $reporte->save();

            // Retornar respuesta JSON con el reporte actualizado
            return response()->json([
                'success' => true,
                'message' => 'Reporte actualizado exitosamente.',
                'data' => $reporte,
            ]);

        } catch (\Exception $e) {
            // Registrar el error en los logs
            \Log::error('Error al actualizar reporte: ' . $e->getMessage());

            // Retornar error genérico
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar el reporte. Por favor, intenta nuevamente.',
            ], 500);
        }
    }

    /**
     * Eliminar un reporte
     * 
     * @param Reporte $reporte
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Reporte $reporte)
    {
        // Verificar autorización
        if (!Gate::allows('delete', $reporte)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar este reporte.',
            ], 403);
        }

        try {
            // Eliminar el reporte
            $reporte->delete();

            // Retornar respuesta JSON de confirmación
            return response()->json([
                'success' => true,
                'message' => 'Reporte eliminado exitosamente.',
            ]);

        } catch (\Exception $e) {
            // Registrar el error en los logs
            \Log::error('Error al eliminar reporte: ' . $e->getMessage());

            // Retornar error genérico
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al eliminar el reporte. Por favor, intenta nuevamente.',
            ], 500);
        }
    }
}

