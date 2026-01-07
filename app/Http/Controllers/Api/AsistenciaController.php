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
                // Usar la fecha de hoy según la zona horaria configurada en Laravel
                // La zona horaria se configura en config/app.php
                $timezone = config('app.timezone', 'America/El_Salvador');
                Carbon::setLocale('es');
                
                // Obtener la fecha de hoy en la zona horaria correcta
                // IMPORTANTE: Usar now() y luego startOfDay() para evitar problemas de zona horaria
                $fechaCarbon = Carbon::now($timezone)->startOfDay();
                $fecha = $fechaCarbon->format('Y-m-d');
                
                // Log para depuración
                \Log::info('Fecha calculada en API', [
                    'timezone_config' => config('app.timezone'),
                    'timezone_usada' => $timezone,
                    'fecha_calculada' => $fecha,
                    'carbon_today' => Carbon::today($timezone)->format('Y-m-d'),
                    'carbon_now' => Carbon::now($timezone)->format('Y-m-d H:i:s'),
                    'dia_semana' => $fechaCarbon->locale('es')->dayName,
                ]);
            }
            
            // Obtener estadísticas del día actual
            $estadisticasDia = $this->asistenciaService->obtenerEstadisticasDia($fecha);
            
            // Obtener estadísticas de la semana actual
            $estadisticasSemana = $this->asistenciaService->obtenerEstadisticasSemana($fecha);
            
            // Log para depuración
            \Log::info('Respuesta de estadísticas', [
                'fecha' => $fecha,
                'estadisticas_dia' => $estadisticasDia,
                'total_semana' => count($estadisticasSemana),
            ]);
            
            // Retornar respuesta JSON
            return response()->json([
                'success' => true,
                'data' => [
                    'dia_actual' => $estadisticasDia,
                    'semana' => $estadisticasSemana,
                ],
            ], 200, [], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            // Log del error para debugging
            \Log::error('Error en AsistenciaController::obtenerEstadisticas', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // En caso de error, retornar mensaje de error
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Obtener estadísticas de ausentismo agrupadas por razón de ausentismo
     * 
     * Endpoint que retorna la cantidad de personas que faltaron ordenadas por razón
     * de ausentismo para el día actual o una fecha específica.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function obtenerAusentismoPorRazon(Request $request): JsonResponse
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
                // Usar la fecha de hoy según la zona horaria configurada
                $timezone = config('app.timezone', 'America/El_Salvador');
                Carbon::setLocale('es');
                
                // Obtener la fecha de hoy en la zona horaria correcta
                $fechaCarbon = Carbon::now($timezone)->startOfDay();
                $fecha = $fechaCarbon->format('Y-m-d');
            }
            
            // Obtener estadísticas de ausentismo por razón
            $ausentismoPorRazon = $this->asistenciaService->obtenerAusentismoPorRazon($fecha);
            
            // Calcular total de personas con ausentismo
            $totalPersonasAusentes = array_sum(array_column($ausentismoPorRazon, 'cantidad_personas'));
            
            // Log para depuración
            \Log::info('Respuesta de ausentismo por razón', [
                'fecha' => $fecha,
                'total_razones' => count($ausentismoPorRazon),
                'total_personas_ausentes' => $totalPersonasAusentes,
            ]);
            
            // Retornar respuesta JSON
            return response()->json([
                'success' => true,
                'data' => [
                    'fecha' => $fecha,
                    'ausentismo_por_razon' => $ausentismoPorRazon,
                    'total_personas_ausentes' => $totalPersonasAusentes,
                ],
            ], 200, [], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            // Log del error para debugging
            \Log::error('Error en AsistenciaController::obtenerAusentismoPorRazon', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // En caso de error, retornar mensaje de error
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de ausentismo: ' . $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}

