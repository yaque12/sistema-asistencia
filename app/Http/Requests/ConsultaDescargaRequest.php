<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request para validar consultas y descargas
 * 
 * Este Form Request valida los filtros necesarios para consultar
 * y descargar reportes de asistencia.
 */
class ConsultaDescargaRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado a hacer esta petición
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        // La autorización se manejará en el controlador
        return true;
    }

    /**
     * Reglas de validación para las consultas y descargas
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha_desde' => [
                'required',
                'date',
            ],
            'fecha_hasta' => [
                'required',
                'date',
                'after_or_equal:fecha_desde',
            ],
            'departamento' => [
                'nullable',
                'string',
                'max:255',
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
            'fecha_desde.required' => 'La fecha desde es obligatoria.',
            'fecha_desde.date' => 'La fecha desde debe ser una fecha válida.',
            'fecha_hasta.required' => 'La fecha hasta es obligatoria.',
            'fecha_hasta.date' => 'La fecha hasta debe ser una fecha válida.',
            'fecha_hasta.after_or_equal' => 'La fecha hasta debe ser mayor o igual a la fecha desde.',
            'departamento.max' => 'El departamento no puede tener más de 255 caracteres.',
        ];
    }

    /**
     * Preparar los datos para la validación
     * 
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Limpiar y normalizar el departamento
        if ($this->has('departamento')) {
            $departamento = trim($this->departamento);
            
            // Convertir "todos" a null para consultar todos los departamentos
            if ($departamento === 'todos' || $departamento === '') {
                $departamento = null;
            }
            
            $this->merge([
                'departamento' => $departamento,
            ]);
        }
    }
}

