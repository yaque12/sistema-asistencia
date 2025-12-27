<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Empleado
 * 
 * Este modelo representa a los empleados del sistema de asistencia.
 * 
 * Campos principales:
 * - id_empleado: Identificador único
 * - nombres: Nombres del empleado
 * - apellidos: Apellidos del empleado
 * - departamento: Departamento donde trabaja (opcional)
 * - codigo_empleado: Código único del empleado (opcional)
 * - fecha_ingreso: Fecha en que inició a trabajar
 */
class Empleado extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'empleados';

    /**
     * Nombre de la clave primaria
     */
    protected $primaryKey = 'id_empleado';
    
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
     * Estos son los campos que podemos asignar cuando creamos o actualizamos un empleado
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombres',           // Nombres del empleado
        'apellidos',         // Apellidos del empleado
        'departamento',      // Departamento donde trabaja
        'codigo_empleado',   // Código único del empleado
        'fecha_ingreso',      // Fecha en que inició a trabajar
    ];

    /**
     * Configurar los tipos de datos de los campos
     * Esto le dice a Laravel cómo debe tratar cada campo
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
        ];
    }
}

