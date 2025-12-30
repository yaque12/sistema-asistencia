<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Reporte Diario
 * 
 * Este modelo representa los reportes diarios de asistencia de empleados.
 * 
 * Campos principales:
 * - id_reporte: Identificador único
 * - fecha: Fecha del reporte de asistencia
 * - id_empleado: ID del empleado
 * - horas_trabajadas: Horas trabajadas en el día
 * - horas_ausentes: Horas ausentes en el día
 * - id_razon: ID de la razón de ausentismo (opcional)
 * - comentarios: Comentarios adicionales (opcional)
 */
class ReporteDiario extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'reportes_diarios';

    /**
     * Nombre de la clave primaria
     */
    protected $primaryKey = 'id_reporte';
    
    /**
     * Indica si los IDs son auto-incrementales
     */
    public $incrementing = true;
    
    /**
     * El tipo de dato de la clave primaria
     */
    protected $keyType = 'int';

    /**
     * Los campos que se pueden llenar masivamente
     *
     * @var list<string>
     */
    protected $fillable = [
        'fecha',                // Fecha del reporte
        'id_empleado',          // ID del empleado
        'horas_trabajadas',     // Horas trabajadas
        'horas_ausentes',       // Horas ausentes
        'id_razon',             // ID de la razón de ausentismo
        'comentarios',          // Comentarios
    ];

    /**
     * Configurar los tipos de datos de los campos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'horas_trabajadas' => 'decimal:2',
            'horas_ausentes' => 'decimal:2',
        ];
    }

    /**
     * Relación: Un reporte pertenece a un empleado
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }

    /**
     * Relación: Un reporte puede tener una razón de ausentismo
     */
    public function razonAusentismo()
    {
        return $this->belongsTo(RazonAusentismo::class, 'id_razon', 'id_razon');
    }
}

