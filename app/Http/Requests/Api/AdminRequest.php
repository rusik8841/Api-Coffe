<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends ApiRequest
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
            'name'       => 'required|string|max:100',
            'surname'    => 'nullable|string|max:100',
            'patronymic' => 'nullable|string|max:100',
            'login'      => 'required|string|max:255|unique:users,login',
            'password'   => 'required|string|min:3|max:255',
            'role_id'    => 'required|exists:roles,id',
            'photo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8048',
        ];
    }
}
