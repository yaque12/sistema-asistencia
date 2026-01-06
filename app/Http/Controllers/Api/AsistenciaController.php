<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AsistenciaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

/**
 * Controlador API para Estadísticas de Asistencia
 * 
 * Este controlador proporciona endpoints para obtener estadísticas
 * de asistencia diarias y semanales.
 */
class AsistenciaController extends Controller
{
    /**
     * Servicio de estadísticas de asistencia
     * 
     * @var AsistenciaService
     */
    protected $asistenciaService;

    /**
     * Constructor
     * 
     * @param AsistenciaService $asistenciaService
     */
    public function __construct(AsistenciaService $asistenciaService)
    {
        $this->asistenciaService = $asistenciaService;
    }

    /**
     * Obtener estadísticas de asistencia
     * 
     * Endpoint que retorna estadísticas del día actual y de la semana actual.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function obtenerEstadisticas(Request $request): JsonResponse
    {
        try {
            // Obtener fecha del request o usar hoy por defecto
            $fecha = $request->input('fecha');
            
            // Validar formato de fecha si se proporciona
            if ($fecha !== null) {
                try {
                    Carbon::parse($fecha);
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato de fecha inválido. Use el formato Y-m-d (ejemplo: 2024-01-15)',
                    ], 400);
                }
            } else {
                $fecha = Carbon::today()->format('Y-m-d');
            }
            
            // Obtener estadísticas del día actual
            $estadisticasDia = $this->asistenciaService->obtenerEstadisticasDia($fecha);
            
            // Obtener estadísticas de la semana actual
            $estadisticasSemana = $this->asistenciaService->obtenerEstadisticasSemana($fecha);
            
            // Retornar respuesta JSON
            return response()->json([
                'success' => true,
                'data' => [
                    'dia_actual' => $estadisticasDia,
                    'semana' => $estadisticasSemana,
                ],
            ]);
            
        } catch (\Exception $e) {
            // En caso de error, retornar mensaje de error
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage(),
            ], 500);
        }
    }
}

