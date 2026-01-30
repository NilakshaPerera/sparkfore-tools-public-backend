<?php

namespace App\Application\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
            "email" => "required|email|unique:users,email",
            "f_name" => "required",
            "l_name" => "required",
            "password" => "required|min:6",
            "customer_id" => "required",
            "account_type_id" => "required",
        ];
    }


    public function messages(): array
    {
        return [
            'f_name.required' => 'The First name field is required.',
            'l_name.required' => 'The Last name field is required.',
        ];
    }
}
