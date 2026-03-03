<?php

namespace App\Http\Requests\Api\Cook;

use Illuminate\Foundation\Http\FormRequest;

class CookRequest extends FormRequest
{
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
            'status' => 'required|in:taken,preparing,ready,paid-up,canceled'
        ];
    }
}
