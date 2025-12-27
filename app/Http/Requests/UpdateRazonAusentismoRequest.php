<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request para validar la actualización de razones de ausentismos
 * 
 * Este Form Request valida todos los datos necesarios para actualizar
 * una razón de ausentismo existente en el sistema.
 */
class UpdateRazonAusentismoRequest extends FormRequest
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
     * Reglas de validación para la actualización de razones de ausentismos
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $razonId = $this->route('razonAusentismo')->id_razon;

        return [
            'razon' => [
                'required',
                'string',
                'max:255',
            ],
            'codigo_razon_ausentismo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('razones_ausentismos', 'codigo_razon_ausentismo')->ignore($razonId, 'id_razon'),
            ],
            'descripcion' => [
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
            'razon.required' => 'La razón es obligatoria.',
            'razon.max' => 'La razón no puede tener más de 255 caracteres.',
            'codigo_razon_ausentismo.required' => 'El código de razón de ausentismo es obligatorio.',
            'codigo_razon_ausentismo.unique' => 'El código de razón de ausentismo ya está en uso.',
            'codigo_razon_ausentismo.max' => 'El código de razón de ausentismo no puede tener más de 50 caracteres.',
            'descripcion.max' => 'La descripción no puede tener más de 1000 caracteres.',
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
        if ($this->has('razon')) {
            $this->merge([
                'razon' => trim($this->razon),
            ]);
        }

        if ($this->has('codigo_razon_ausentismo')) {
            $this->merge([
                'codigo_razon_ausentismo' => trim($this->codigo_razon_ausentismo),
            ]);
        }

        if ($this->has('descripcion')) {
            $this->merge([
                'descripcion' => trim($this->descripcion) ?: null,
            ]);
        }
    }
}

