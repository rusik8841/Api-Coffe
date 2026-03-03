<?php

namespace App\Http\Requests\Api\Waiter;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role->code == 'cook';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'work_shift_id' => 'required|integer|exists:work_shifts,id',
            'table_id' => 'required|integer|exists:tables,id',
            'number_of_person' => 'required|integer|min:1',
        ];
    }
}
