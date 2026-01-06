<?php

namespace App\Http\Controllers;

use App\Models\ReporteDiario;
use App\Http\Requests\ConsultaDescargaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Controlador para consultas y descargas
 * 
 * Este controlador maneja las operaciones de consulta y descarga de reportes
 * de asistencia diaria de empleados.
 */
class ConsultaDescargaController extends Controller
{
    /**
     * Mostrar la vista de consultas y descargas
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Verificar autorización usando la Policy
        $user = \Illuminate\Support\Facades\Auth::user();
        $policy = new \App\Policies\ConsultaDescargaPolicy();
        if (!$policy->viewAny($user)) {
            abort(403, 'No tienes permisos para acceder a consultas y descargas.');
        }

        return view('consultas-descargas.index');
    }

    /**
     * Consultar reportes diarios con filtros
     * 
     * @param ConsultaDescargaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function consultar(ConsultaDescargaRequest $request)
    {
        // Verificar autorización usando la Policy
        $user = \Illuminate\Support\Facades\Auth::user();
        $policy = new \App\Policies\ConsultaDescargaPolicy();
        if (!$policy->consultar($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para consultar reportes.',
            ], 403);
        }

        try {
            $fechaDesde = $request->fecha_desde;
            $fechaHasta = $request->fecha_hasta;
            $departamento = $request->departamento;

            // Construir la consulta de reportes diarios con relaciones
            $query = ReporteDiario::with(['empleado', 'razonAusentismo'])
                ->whereBetween('fecha', [$fechaDesde, $fechaHasta]);

            // Filtrar por departamento si se especifica
            if ($departamento) {
                $query->whereHas('empleado', function ($q) use ($departamento) {
                    $q->where('departamento', $departamento);
                });
            }

            // Ordenar por fecha y luego por empleado
            $query->orderBy('fecha', 'asc')
                  ->orderBy('id_empleado', 'asc');

            $reportes = $query->get();

            // Transformar los datos para el frontend
            $resultados = $reportes->map(function ($reporte) {
                return [
                    'fecha' => $reporte->fecha->format('Y-m-d'),
                    'codigo_empleado' => $reporte->empleado->codigo_empleado ?? 'N/A',
                    'nombres' => $reporte->empleado->nombres ?? '',
                    'apellidos' => $reporte->empleado->apellidos ?? '',
                    'departamento' => $reporte->empleado->departamento ?? 'No especificado',
                    'horas_trabajadas' => $reporte->horas_trabajadas ?? 0,
                    'horas_ausentes' => $reporte->horas_ausentes ?? 0,
                    'razon_ausencia' => $reporte->razonAusentismo->razon ?? 'N/A',
                    'comentarios' => $reporte->comentarios ?? '',
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Consulta realizada exitosamente.',
                'data' => $resultados,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al consultar reportes: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al consultar los reportes. Por favor, intenta nuevamente.',
            ], 500);
        }
    }

    /**
     * Descargar reportes diarios en formato Excel (CSV)
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function descargar(Request $request)
    {
        // Verificar autorización usando la Policy
        $user = \Illuminate\Support\Facades\Auth::user();
        $policy = new \App\Policies\ConsultaDescargaPolicy();
        if (!$policy->descargar($user)) {
            abort(403, 'No tienes permisos para descargar reportes.');
        }

        try {
            // Validar manualmente los parámetros de la URL
            $request->validate([
                'fecha_desde' => 'required|date',
                'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
                'departamento' => 'nullable|string',
            ], [
                'fecha_desde.required' => 'La fecha desde es obligatoria.',
                'fecha_desde.date' => 'La fecha desde debe ser una fecha válida.',
                'fecha_hasta.required' => 'La fecha hasta es obligatoria.',
                'fecha_hasta.date' => 'La fecha hasta debe ser una fecha válida.',
                'fecha_hasta.after_or_equal' => 'La fecha hasta debe ser mayor o igual a la fecha desde.',
            ]);

            $fechaDesde = $request->fecha_desde;
            $fechaHasta = $request->fecha_hasta;
            $departamento = $request->departamento;

            // Normalizar departamento
            if ($departamento === 'todos' || $departamento === '') {
                $departamento = null;
            }

            // Construir la consulta de reportes diarios
            $query = ReporteDiario::with(['empleado', 'razonAusentismo'])
                ->whereBetween('fecha', [$fechaDesde, $fechaHasta]);

            // Filtrar por departamento si se especifica
            if ($departamento) {
                $query->whereHas('empleado', function ($q) use ($departamento) {
                    $q->where('departamento', $departamento);
                });
            }

            // Ordenar por fecha y luego por empleado
            $query->orderBy('fecha', 'asc')
                  ->orderBy('id_empleado', 'asc');

            $reportes = $query->get();

            // Generar nombre del archivo
            $nombreArchivo = 'reportes_asistencia_' . date('YmdHis') . '.csv';

            // Crear respuesta de descarga con streaming
            return response()->stream(
                function () use ($reportes) {
                    $handle = fopen('php://output', 'w');

                    // Agregar BOM para UTF-8 (para que Excel lo reconozca)
                    fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

                    // Agregar separador para Excel (esto le indica a Excel qué delimitador usar)
                    fwrite($handle, "sep=;\n");

                    // Escribir encabezados usando punto y coma como delimitador
                    fputcsv($handle, [
                        'Fecha',
                        'Código Empleado',
                        'Nombres',
                        'Apellidos',
                        'Departamento',
                        'Horas Trabajadas',
                        'Horas Ausentes',
                        'Razón de Ausencias',
                        'Comentarios',
                    ], ';');

                    // Escribir datos usando punto y coma como delimitador
                    foreach ($reportes as $reporte) {
                        fputcsv($handle, [
                            $reporte->fecha->format('Y-m-d'),
                            $reporte->empleado->codigo_empleado ?? 'N/A',
                            $reporte->empleado->nombres ?? '',
                            $reporte->empleado->apellidos ?? '',
                            $reporte->empleado->departamento ?? 'No especificado',
                            $reporte->horas_trabajadas ?? 0,
                            $reporte->horas_ausentes ?? 0,
                            $reporte->razonAusentismo->razon ?? 'N/A',
                            $reporte->comentarios ?? '',
                        ], ';');
                    }

                    fclose($handle);
                },
                200,
                [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ]
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Los datos proporcionados no son válidos.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Error al descargar reportes: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al descargar los reportes. Por favor, intenta nuevamente.',
            ], 500);
        }
    }
}

