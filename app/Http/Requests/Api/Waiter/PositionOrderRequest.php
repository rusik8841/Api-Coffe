<?php

namespace App\Http\Requests\Api\Waiter;

use Illuminate\Foundation\Http\FormRequest;

class PositionOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role->code == 'waiter';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'menu_id' => 'required|integer|exists:menus,id',
            'count' => 'required|integer|min:1',
        ];
    }
}
