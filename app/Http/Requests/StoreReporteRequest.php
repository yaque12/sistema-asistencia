<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request para validar la creación de reportes
 * 
 * Este Form Request valida todos los datos necesarios para crear
 * un nuevo reporte en el sistema.
 */
class StoreReporteRequest extends FormRequest
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
     * Reglas de validación para la creación de reportes
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha' => [
                'required',
                'date',
            ],
            'estado' => [
                'required',
                'string',
                'in:activo,inactivo',
            ],
            'comentarios' => [
                'nullable',
                'string',
                'max:1000',
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
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha debe ser una fecha válida.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
            'comentarios.max' => 'Los comentarios no pueden tener más de 1000 caracteres.',
        ];
    }

    /**
     * Preparar los datos para la validación
     * 
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Limpiar y preparar los datos
        if ($this->has('comentarios')) {
            $this->merge([
                'comentarios' => trim($this->comentarios) ?: null,
            ]);
        }
    }
}

