<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CargaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_lote' => ['required', 'integer', 'exists:lotes,id_lote'],
            'fecha_carga' => ['required', 'date', 'before_or_equal:today'],
            'peso_neto' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_carga.before_or_equal' => 'La fecha de la carga no puede ser futura.',
        ];
    }
}
