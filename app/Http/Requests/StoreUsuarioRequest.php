<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request para validar la creación de usuarios
 * 
 * Este Form Request valida todos los datos necesarios para crear
 * un nuevo usuario en el sistema.
 */
class StoreUsuarioRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado a hacer esta petición
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        // La autorización se manejará en el controlador usando Policies
        return true;
    }

    /**
     * Reglas de validación para la creación de usuarios
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre_usuario' => [
                'required',
                'string',
                'max:255',
                Rule::unique('usuarios', 'nombre_usuario'),
            ],
            'nombres' => [
                'required',
                'string',
                'max:255',
            ],
            'apellidos' => [
                'required',
                'string',
                'max:255',
            ],
            'departamento_trabajo' => [
                'nullable',
                'string',
                'max:255',
            ],
            'codigo_empleado' => [
                'nullable',
                'string',
                'max:50',
            ],
            'clave' => [
                'required',
                'string',
                'min:6',
            ],
            'confirmar_clave' => [
                'required',
                'string',
                'same:clave',
            ],
            'roles' => [
                'nullable',
                'array',
            ],
            'roles.*' => [
                'string',
                'exists:roles,codigo_rol',
            ],
        ];
    }

    /**
     * Mensajes de error personalizados en español
     * 
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
            'nombre_usuario.unique' => 'El nombre de usuario ya está en uso.',
            'nombre_usuario.max' => 'El nombre de usuario no puede tener más de 255 caracteres.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'nombres.max' => 'Los nombres no pueden tener más de 255 caracteres.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.max' => 'Los apellidos no pueden tener más de 255 caracteres.',
            'departamento_trabajo.max' => 'El departamento no puede tener más de 255 caracteres.',
            'codigo_empleado.max' => 'El código de empleado no puede tener más de 50 caracteres.',
            'clave.required' => 'La contraseña es obligatoria.',
            'clave.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'confirmar_clave.required' => 'Debe confirmar la contraseña.',
            'confirmar_clave.same' => 'Las contraseñas no coinciden.',
        ];
    }

    /**
     * Preparar los datos para la validación
     * 
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Limpiar espacios en blanco de los campos de texto
        if ($this->has('nombre_usuario')) {
            $this->merge([
                'nombre_usuario' => trim($this->nombre_usuario),
            ]);
        }

        if ($this->has('nombres')) {
            $this->merge([
                'nombres' => trim($this->nombres),
            ]);
        }

        if ($this->has('apellidos')) {
            $this->merge([
                'apellidos' => trim($this->apellidos),
            ]);
        }
    }
}

