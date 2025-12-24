<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Modelo de Usuario
 * 
 * Este modelo representa a los usuarios del sistema de asistencia.
 * Usa la tabla 'usuarios' en lugar de 'users' (tabla por defecto de Laravel).
 * 
 * Campos principales:
 * - id_usuario: Identificador único
 * - nombre_usuario: Para iniciar sesión (en lugar de email)
 * - clave: Contraseña encriptada (en lugar de password)
 * - nombres, apellidos, departamento_trabajo, codigo_empleado: Datos del usuario
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Nombre de la tabla en la base de datos
     * Por defecto Laravel usa 'users', pero nosotros usamos 'usuarios'
     */
    protected $table = 'usuarios';

    /**
     * Nombre de la clave primaria
     * Por defecto Laravel usa 'id', pero nosotros usamos 'id_usuario'
     */
    protected $primaryKey = 'id_usuario';

    /**
     * Los campos que se pueden llenar masivamente
     * Estos son los campos que podemos asignar cuando creamos o actualizamos un usuario
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre_usuario',    // Usuario para login
        'nombres',           // Primer y segundo nombre
        'apellidos',         // Apellidos
        'departamento_trabajo', // Departamento donde trabaja
        'codigo_empleado',   // Código único del empleado
        'clave',             // Contraseña encriptada
    ];

    /**
     * Los campos que deben ocultarse cuando el modelo se convierte a JSON
     * Esto es importante para no exponer la contraseña en las respuestas API
     *
     * @var list<string>
     */
    protected $hidden = [
        'clave',            // Nunca mostrar la contraseña
        'remember_token',   // Token para "recordarme"
    ];

    /**
     * Obtener el nombre del campo de contraseña para autenticación
     * Laravel por defecto usa 'password', pero nosotros usamos 'clave'
     * 
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->clave;
    }

    /**
     * Obtener el nombre del campo de contraseña (requerido para Laravel 11+)
     * 
     * @return string
     */
    public function getAuthPasswordName()
    {
        return 'clave';
    }

    /**
     * Obtener el nombre del campo de usuario para autenticación
     * Laravel por defecto usa 'email', pero nosotros usamos 'nombre_usuario'
     * 
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'nombre_usuario';
    }

    /**
     * Configurar los tipos de datos de los campos
     * Esto le dice a Laravel cómo debe tratar cada campo
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Removido el cast 'hashed' porque puede causar conflictos
            // La encriptación se manejará manualmente en el controlador
        ];
    }
}
