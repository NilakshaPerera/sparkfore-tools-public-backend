<?php

namespace App\Application\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            "email" =>  'required|email|unique:users,email,'.$this->user->id,
            "f_name" => "required",
            "l_name" => "required",
             "current_password" => "required|min:6",
            "password" => "sometimes|nullable|confirmed|min:6",
            "customer_id" => "required",
            "account_type_id" => "required",
        ];
    }
}
