<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request para validar la actualización de empleados
 * 
 * Este Form Request valida todos los datos necesarios para actualizar
 * un empleado existente en el sistema.
 */
class UpdateEmpleadoRequest extends FormRequest
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
     * Reglas de validación para la actualización de empleados
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
            'departamento' => [
                'nullable',
                'string',
                'max:255',
            ],
            'codigo_empleado' => [
                'nullable',
                'string',
                'max:50',
            ],
            'fecha_ingreso' => [
                'required',
                'date',
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
            'nombres.required' => 'Los nombres son obligatorios.',
            'nombres.max' => 'Los nombres no pueden tener más de 255 caracteres.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.max' => 'Los apellidos no pueden tener más de 255 caracteres.',
            'departamento.max' => 'El departamento no puede tener más de 255 caracteres.',
            'codigo_empleado.max' => 'El código de empleado no puede tener más de 50 caracteres.',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
            'fecha_ingreso.date' => 'La fecha de ingreso debe ser una fecha válida.',
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

        if ($this->has('departamento')) {
            $this->merge([
                'departamento' => trim($this->departamento) ?: null,
            ]);
        }

        if ($this->has('codigo_empleado')) {
            $this->merge([
                'codigo_empleado' => trim($this->codigo_empleado) ?: null,
            ]);
        }
    }
}

