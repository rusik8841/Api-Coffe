<?php

namespace App\Http\Requests\Api\Work;

use App\Http\Requests\Api\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class WorkShiftRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role->code == 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start' => [
                'required',
                'date_format:Y-m-d H:i',
                'after:now',
            ],
            'end' => [
                'required',
                'date_format:Y-m-d H:i',
                'after:start',
            ],

        ];
    }
}
