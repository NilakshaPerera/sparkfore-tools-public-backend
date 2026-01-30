<?php

namespace App\Application\Requests\AccessControl;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRolePermission extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return can('ACCESS_CONTROL_ACCESS_CONTROL_ROLE_PERMISSIONS_UPDATE');
    }

    /**
     * Get the validation rulereturn true;lluminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
