<?php

namespace app\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CustomerStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'organization_no' => 'required',
            'invoice_type' => 'required',
            'invoice_address' => '',
            'invoice_email' => 'email',
            'invoice_reference' => '',
            'invoice_annotation' => ''
        ];
    }
}
