<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParteDiarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_lote' => ['required', 'integer', 'exists:lotes,id_lote'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'es_dia_caido' => ['required', 'boolean'],
            'costo_insumos' => ['nullable', 'numeric', 'min:0'],
            'costo_maquinaria' => ['nullable', 'numeric', 'min:0'],
            'costo_mano_obra' => ['nullable', 'numeric', 'min:0'],
            'costo_total_dia' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.before_or_equal' => 'La fecha del parte no puede ser futura.',
        ];
    }
}
