<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Razón de Ausentismo
 * 
 * Este modelo representa las razones de ausentismos del sistema de asistencia.
 * 
 * Campos principales:
 * - id_razon: Identificador único
 * - razon: Nombre de la razón de ausentismo
 * - codigo_razon_ausentismo: Código único de la razón de ausentismo
 * - descripcion: Descripción detallada (opcional)
 */
class RazonAusentismo extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'razones_ausentismos';

    /**
     * Nombre de la clave primaria
     */
    protected $primaryKey = 'id_razon';
    
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
     * Estos son los campos que podemos asignar cuando creamos o actualizamos una razón de ausentismo
     *
     * @var list<string>
     */
    protected $fillable = [
        'razon',                      // Nombre de la razón de ausentismo
        'codigo_razon_ausentismo',    // Código único de la razón de ausentismo
        'descripcion',                // Descripción detallada
    ];
}

