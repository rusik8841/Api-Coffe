<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\Api\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'last_name' => 'required|string|min:2|max:64',
            'first_name' => 'required|string|min:2|max:64',
            'middle_name' => 'nullable|string|min:2|max:64',
            'login' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|max:255',
            'photo_file.*' => 'file|mimes:jpg,jpeg,png|max:8192',
            'role_id' => 'required|integer|exists:roles,id',
        ];
    }

    // Кастомные сообщения об ошибки
    public function messages(): array {
        return [

        ];
    }
}
