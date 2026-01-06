<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Rol
 * 
 * Este modelo representa los roles del sistema de asistencia.
 * Cada rol define los permisos y accesos que tiene un usuario.
 * 
 * Campos principales:
 * - id_rol: Identificador único
 * - codigo_rol: Código único del rol (ej: ADMIN, supervisor)
 * - nombre_rol: Nombre descriptivo del rol
 * - descripcion: Descripción del rol y sus permisos
 */
class Role extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'roles';

    /**
     * Nombre de la clave primaria
     */
    protected $primaryKey = 'id_rol';
    
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
        'codigo_rol',
        'nombre_rol',
        'descripcion',
    ];

    /**
     * Relación muchos a muchos con usuarios
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function usuarios()
    {
        return $this->belongsToMany(
            User::class,
            'usuario_roles',
            'id_rol',
            'id_usuario'
        )->withTimestamps();
    }
}

