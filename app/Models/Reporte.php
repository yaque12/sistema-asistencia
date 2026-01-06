<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Reporte
 * 
 * Este modelo representa los reportes generados en el sistema.
 * 
 * Campos principales:
 * - id_reporte: Identificador único
 * - fecha: Fecha del reporte
 * - estado: Estado del reporte (activo/inactivo)
 * - comentarios: Comentarios adicionales (opcional)
 */
class Reporte extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'reportes';

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
        'estado',               // Estado (activo/inactivo)
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
            // No usar cast de fecha para evitar problemas de zona horaria
            // La fecha se guardará como string YYYY-MM-DD directamente
        ];
    }

    /**
     * Accessor para asegurar que la fecha siempre se devuelva como string YYYY-MM-DD
     *
     * @param mixed $value
     * @return string
     */
    public function getFechaAttribute($value)
    {
        // Si es un objeto DateTime, convertir a string
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        
        // Si ya es string, devolverlo tal cual
        return $value;
    }
}

