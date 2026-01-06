<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\ReporteDiario;
use Carbon\Carbon;

/**
 * Servicio de Estadísticas de Asistencia
 * 
 * Este servicio calcula estadísticas de asistencia diarias y semanales
 * para el panel de control de asistencia.
 */
class AsistenciaService
{
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
        // Obtener el total de empleados
        $totalEmpleados = Empleado::count();
        
        // Obtener empleados con horas trabajadas en la fecha especificada
        // Un empleado tiene horas trabajadas si tiene un reporte con horas_trabajadas > 0
        // Usamos groupBy para obtener empleados únicos y luego contamos
        $personasConHoras = ReporteDiario::where('fecha', $fecha)
            ->where('horas_trabajadas', '>', 0)
            ->groupBy('id_empleado')
            ->get()
            ->count();
        
        // Calcular el porcentaje
        // Evitar división por cero
        $porcentaje = 0;
        if ($totalEmpleados > 0) {
            $porcentaje = round(($personasConHoras / $totalEmpleados) * 100, 2);
        }
        
        return [
            'fecha' => $fecha,
            'total_empleados' => $totalEmpleados,
            'personas_con_horas' => $personasConHoras,
            'porcentaje' => $porcentaje,
        ];
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
            $fecha = Carbon::today()->format('Y-m-d');
        }
        
        // Obtener el lunes de la semana actual
        $fechaCarbon = Carbon::parse($fecha);
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

