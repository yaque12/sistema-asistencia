<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request para validar la actualización de reportes
 * 
 * Este Form Request valida todos los datos necesarios para actualizar
 * un reporte existente en el sistema.
 */
class UpdateReporteRequest extends FormRequest
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
     * Reglas de validación para la actualización de reportes
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'estado' => [
                'required',
                'string',
                'in:activo,inactivo',
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
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
        ];
    }
}

