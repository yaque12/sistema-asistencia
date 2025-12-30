<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request para validar la creación de reportes diarios
 * 
 * Este Form Request valida todos los datos necesarios para crear
 * un nuevo reporte diario de asistencia en el sistema.
 */
class StoreReporteDiarioRequest extends FormRequest
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
     * Reglas de validación para la creación de reportes diarios
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'id_empleado' => [
                'required',
                'integer',
                'exists:empleados,id_empleado',
                Rule::unique('reportes_diarios')->where(function ($query) {
                    return $query->where('fecha', $this->fecha);
                }),
            ],
            'horas_trabajadas' => [
                'nullable',
                'numeric',
                'min:0',
                'max:24',
            ],
            'horas_ausentes' => [
                'nullable',
                'numeric',
                'min:0',
                'max:24',
            ],
            'id_razon' => [
                'nullable',
                'integer',
                'exists:razones_ausentismos,id_razon',
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
            'fecha.before_or_equal' => 'La fecha no puede ser posterior a hoy.',
            'id_empleado.required' => 'El empleado es obligatorio.',
            'id_empleado.exists' => 'El empleado seleccionado no existe.',
            'id_empleado.unique' => 'Ya existe un reporte para este empleado en esta fecha.',
            'horas_trabajadas.numeric' => 'Las horas trabajadas deben ser un número.',
            'horas_trabajadas.min' => 'Las horas trabajadas no pueden ser negativas.',
            'horas_trabajadas.max' => 'Las horas trabajadas no pueden ser más de 24.',
            'horas_ausentes.numeric' => 'Las horas ausentes deben ser un número.',
            'horas_ausentes.min' => 'Las horas ausentes no pueden ser negativas.',
            'horas_ausentes.max' => 'Las horas ausentes no pueden ser más de 24.',
            'id_razon.exists' => 'La razón de ausentismo seleccionada no existe.',
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
        if ($this->has('horas_trabajadas')) {
            $this->merge([
                'horas_trabajadas' => $this->horas_trabajadas ?: null,
            ]);
        }

        if ($this->has('horas_ausentes')) {
            $this->merge([
                'horas_ausentes' => $this->horas_ausentes ?: null,
            ]);
        }

        if ($this->has('id_razon')) {
            $this->merge([
                'id_razon' => $this->id_razon ?: null,
            ]);
        }

        if ($this->has('comentarios')) {
            $this->merge([
                'comentarios' => trim($this->comentarios) ?: null,
            ]);
        }
    }
}

