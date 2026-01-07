<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\ReporteDiario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de Estadísticas de Asistencia
 * 
 * Este servicio calcula estadísticas de asistencia diarias y semanales
 * para el panel de control de asistencia.
 */

/**
 * Servicio de Estadísticas de Asistencia
 * 
 * Este servicio calcula estadísticas de asistencia diarias y semanales
 * para el panel de control de asistencia.
 */
class AsistenciaService
{
    /**
     * Obtener la zona horaria configurada en la aplicación
     * 
     * @return string
     */
    private function getTimezone(): string
    {
        return config('app.timezone', 'America/El_Salvador');
    }

    /**
     * Obtener la fecha de hoy en la zona horaria configurada
     * 
     * @return string Fecha en formato Y-m-d
     */
    private function getFechaHoy(): string
    {
        $timezone = $this->getTimezone();
        // Usar now() y startOfDay() para mayor precisión
        return Carbon::now($timezone)->startOfDay()->format('Y-m-d');
    }
    /**
     * Obtener estadísticas de asistencia para un día específico
     * 
     * Calcula el total de empleados y cuántos tienen horas trabajadas
     * en la fecha especificada.
     * 
     * @param string $fecha Fecha en formato Y-m-d
     * @return array Datos de estadísticas del día
     */
    public function obtenerEstadisticasDia(string $fecha): array
    {
        try {
            // Obtener el total de empleados usando DB::table directamente
            $totalEmpleados = DB::table('empleados')->count();
            
            // Obtener empleados con horas trabajadas en la fecha especificada
            // Un empleado tiene horas trabajadas si tiene un reporte con horas_trabajadas > 0
            // Usamos DB::table directamente para evitar problemas con el cast de fecha del modelo
            // y usamos groupBy para contar empleados únicos
            
            // Primero, verificar cuántos reportes hay para esta fecha
            $totalReportes = DB::table('reportes_diarios')
                ->where('fecha', $fecha)
                ->count();
            
            // Contar empleados únicos con horas trabajadas > 0
            // Usamos una subconsulta más eficiente
            $personasConHoras = DB::table('reportes_diarios')
                ->select('id_empleado')
                ->where('fecha', $fecha)
                ->where('horas_trabajadas', '>', 0)
                ->distinct()
                ->count('id_empleado');
            
            // Calcular el porcentaje
            // Evitar división por cero
            $porcentaje = 0;
            if ($totalEmpleados > 0) {
                $porcentaje = round(($personasConHoras / $totalEmpleados) * 100, 2);
            }
            
            // Log para depuración
            \Log::info('Estadísticas del día', [
                'fecha' => $fecha,
                'total_empleados' => $totalEmpleados,
                'total_reportes' => $totalReportes,
                'personas_con_horas' => $personasConHoras,
                'porcentaje' => $porcentaje,
            ]);
            
            return [
                'fecha' => $fecha,
                'total_empleados' => (int) $totalEmpleados,
                'personas_con_horas' => (int) $personasConHoras,
                'porcentaje' => $porcentaje,
            ];
        } catch (\Exception $e) {
            // En caso de error, retornar valores por defecto
            \Log::error('Error en AsistenciaService::obtenerEstadisticasDia', [
                'fecha' => $fecha,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'fecha' => $fecha,
                'total_empleados' => 0,
                'personas_con_horas' => 0,
                'porcentaje' => 0,
            ];
        }
    }

    /**
     * Obtener estadísticas de asistencia para la semana actual
     * 
     * Calcula estadísticas para cada día de la semana actual
     * (desde el lunes hasta el domingo de la semana que contiene la fecha especificada).
     * 
     * @param string|null $fecha Fecha de referencia (default: hoy)
     * @return array Array con estadísticas de cada día de la semana
     */
    public function obtenerEstadisticasSemana(?string $fecha = null): array
    {
        // Si no se proporciona fecha, usar hoy
        if ($fecha === null) {
            $fecha = $this->getFechaHoy();
        }
        
        // Obtener el lunes de la semana actual
        // Asegurar que se use la zona horaria correcta al parsear
        $timezone = $this->getTimezone();
        $fechaCarbon = Carbon::parse($fecha)->setTimezone($timezone);
        $lunes = $fechaCarbon->copy()->startOfWeek(Carbon::MONDAY);
        $domingo = $fechaCarbon->copy()->endOfWeek(Carbon::SUNDAY);
        
        // Array para almacenar las estadísticas de cada día
        $estadisticasSemana = [];
        
        // Iterar sobre cada día de la semana (lunes a domingo)
        $fechaActual = $lunes->copy();
        while ($fechaActual->lte($domingo)) {
            $fechaStr = $fechaActual->format('Y-m-d');
            
            // Obtener estadísticas del día
            $estadisticasDia = $this->obtenerEstadisticasDia($fechaStr);
            
            // Agregar el nombre del día de la semana
            $estadisticasDia['dia_semana'] = $this->obtenerNombreDia($fechaActual->dayOfWeek);
            $estadisticasDia['dia_semana_corto'] = $this->obtenerNombreDiaCorto($fechaActual->dayOfWeek);
            
            $estadisticasSemana[] = $estadisticasDia;
            
            // Avanzar al siguiente día
            $fechaActual->addDay();
        }
        
        return $estadisticasSemana;
    }

    /**
     * Obtener el nombre completo del día de la semana en español
     * 
     * @param int $diaDeSemana Número del día (0 = domingo, 1 = lunes, ..., 6 = sábado)
     * @return string Nombre del día
     */
    private function obtenerNombreDia(int $diaDeSemana): string
    {
        $dias = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];
        
        return $dias[$diaDeSemana] ?? 'Desconocido';
    }

    /**
     * Obtener el nombre corto del día de la semana en español
     * 
     * @param int $diaDeSemana Número del día (0 = domingo, 1 = lunes, ..., 6 = sábado)
     * @return string Nombre corto del día
     */
    private function obtenerNombreDiaCorto(int $diaDeSemana): string
    {
        $dias = [
            0 => 'Dom',
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mié',
            4 => 'Jue',
            5 => 'Vie',
            6 => 'Sáb',
        ];
        
        return $dias[$diaDeSemana] ?? '???';
    }
}

