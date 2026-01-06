<?php

namespace App\Http\Controllers;

use App\Models\ReporteDiario;
use App\Models\Reporte;
use App\Http\Requests\StoreReporteDiarioRequest;
use App\Http\Requests\UpdateReporteDiarioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

/**
 * Controlador para la gestión de reportes diarios
 * 
 * Este controlador maneja todas las operaciones CRUD para los reportes diarios
 * de asistencia, incluyendo creación, lectura, actualización y eliminación.
 */
class ReporteDiarioController extends Controller
{
    /**
     * Mostrar la vista principal de reportes diarios
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Verificar autorización
        if (!Gate::allows('viewAny', ReporteDiario::class)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver los reportes diarios.',
                ], 403);
            }
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        // Obtener parámetros
        $fecha = $request->get('fecha', null);
        $departamento = $request->get('departamento', '');

        // Si es una petición AJAX para obtener reportes por fecha
        if ($request->expectsJson() || $request->ajax()) {
            if (!$fecha) {
                return response()->json([
                    'success' => false,
                    'message' => 'La fecha es requerida.',
                ], 400);
            }

            // Verificar si existe un reporte generado para esta fecha
            $reporteGenerado = Reporte::where('fecha', $fecha)->first();

            // Si no existe el reporte o está inactivo, retornar mensaje
            if (!$reporteGenerado || $reporteGenerado->estado !== 'activo') {
                return response()->json([
                    'success' => false,
                    'message' => 'La fecha no está generada',
                    'data' => [
                        'reportes' => [],
                    ],
                ]);
            }

            // Construir consulta con joins para obtener información del empleado
            $query = ReporteDiario::query()
                ->where('reportes_diarios.fecha', $fecha)
                ->join('empleados', 'reportes_diarios.id_empleado', '=', 'empleados.id_empleado')
                ->leftJoin('razones_ausentismos', 'reportes_diarios.id_razon', '=', 'razones_ausentismos.id_razon')
                ->select(
                    'reportes_diarios.*',
                    'empleados.codigo_empleado',
                    'empleados.nombres',
                    'empleados.apellidos',
                    'empleados.departamento',
                    'razones_ausentismos.razon as razon_nombre'
                );

            // Filtrar por departamento si se proporciona
            if (!empty($departamento)) {
                $query->where('empleados.departamento', $departamento);
            }

            // Ordenar por código de empleado
            $query->orderByRaw('CAST(empleados.codigo_empleado AS UNSIGNED) ASC, empleados.codigo_empleado ASC');

            $reportes = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'reportes' => $reportes,
                ],
            ]);
        }

        // Para peticiones normales, retornar la vista
        return view('reporte-diario.index');
    }

    /**
     * Guardar múltiples reportes diarios (guardado masivo)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function guardarMasivo(Request $request)
    {
        // Verificar autorización
        if (!Gate::allows('create', ReporteDiario::class)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para crear reportes diarios.',
            ], 403);
        }

        // Validar datos generales
        $request->validate([
            'fecha' => 'required|date|before_or_equal:today',
            'registros' => 'required|array|min:1',
            'registros.*.id_empleado' => 'required|integer|exists:empleados,id_empleado',
            'registros.*.horas_trabajadas' => 'nullable|numeric|min:0|max:24',
            'registros.*.horas_ausentes' => 'nullable|numeric|min:0|max:24',
            'registros.*.id_razon' => 'nullable|integer|exists:razones_ausentismos,id_razon',
            'registros.*.comentarios' => 'nullable|string|max:1000',
        ], [
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.before_or_equal' => 'La fecha no puede ser posterior a hoy.',
            'registros.required' => 'Debe proporcionar al menos un registro.',
            'registros.*.id_empleado.required' => 'El empleado es obligatorio en cada registro.',
            'registros.*.id_empleado.exists' => 'Uno de los empleados seleccionados no existe.',
            'registros.*.horas_trabajadas.max' => 'Las horas trabajadas no pueden ser más de 24.',
            'registros.*.horas_ausentes.max' => 'Las horas ausentes no pueden ser más de 24.',
            'registros.*.id_razon.exists' => 'Una de las razones de ausentismo no existe.',
        ]);

        try {
            $fecha = $request->fecha;
            $registros = $request->registros;
            $guardados = 0;
            $actualizados = 0;
            $errores = [];

            DB::beginTransaction();

            foreach ($registros as $registro) {
                // Solo procesar si hay datos
                if (empty($registro['horas_trabajadas']) && 
                    empty($registro['horas_ausentes']) && 
                    empty($registro['id_razon']) && 
                    empty($registro['comentarios'])) {
                    continue;
                }

                $datos = [
                    'fecha' => $fecha,
                    'id_empleado' => $registro['id_empleado'],
                    'horas_trabajadas' => $registro['horas_trabajadas'] ?? 0,
                    'horas_ausentes' => $registro['horas_ausentes'] ?? 0,
                    'id_razon' => $registro['id_razon'] ?? null,
                    'comentarios' => $registro['comentarios'] ?? null,
                ];

                // Usar updateOrCreate para crear o actualizar
                $reporte = ReporteDiario::updateOrCreate(
                    [
                        'fecha' => $fecha,
                        'id_empleado' => $registro['id_empleado'],
                    ],
                    $datos
                );

                if ($reporte->wasRecentlyCreated) {
                    $guardados++;
                } else {
                    $actualizados++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Reporte guardado exitosamente. {$guardados} registros nuevos, {$actualizados} actualizados.",
                'data' => [
                    'guardados' => $guardados,
                    'actualizados' => $actualizados,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el reporte: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear un reporte diario individual
     * 
     * @param StoreReporteDiarioRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreReporteDiarioRequest $request)
    {
        // Verificar autorización
        if (!Gate::allows('create', ReporteDiario::class)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para crear reportes diarios.',
            ], 403);
        }

        try {
            $reporte = ReporteDiario::create($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Reporte diario creado exitosamente.',
                'data' => $reporte,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el reporte diario: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar un reporte diario existente
     * 
     * @param UpdateReporteDiarioRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateReporteDiarioRequest $request, $id)
    {
        $reporte = ReporteDiario::find($id);

        if (!$reporte) {
            return response()->json([
                'success' => false,
                'message' => 'Reporte diario no encontrado.',
            ], 404);
        }

        // Verificar autorización
        if (!Gate::allows('update', $reporte)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para actualizar este reporte diario.',
            ], 403);
        }

        try {
            $reporte->update($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Reporte diario actualizado exitosamente.',
                'data' => $reporte,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el reporte diario: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar un reporte diario
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $reporte = ReporteDiario::find($id);

        if (!$reporte) {
            return response()->json([
                'success' => false,
                'message' => 'Reporte diario no encontrado.',
            ], 404);
        }

        // Verificar autorización
        if (!Gate::allows('delete', $reporte)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar este reporte diario.',
            ], 403);
        }

        try {
            $reporte->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Reporte diario eliminado exitosamente.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el reporte diario: ' . $e->getMessage(),
            ], 500);
        }
    }
}

