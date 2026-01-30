<?php

namespace App\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InstallationStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->domain_type === 'standard' && $this->filled('sub_domain')) {
            $full = strtolower($this->input('sub_domain')).'.sparkfore.com';
            $this->merge(['_full_url' => $full]);
        }
    }

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
            'disk_size' => [
                'required',
                'numeric',
                'min:0',
                'max:1000',
            ],
            'domain_type' => [
                'required',
                'in:standard,custom'
            ],
            'domain' => [
                Rule::requiredIf(fn () => $this->domain_type == 'custom'),
                'nullable','string','max:255',
                Rule::unique('installations', 'url'),
            ],
            'sub_domain' => [
                Rule::requiredIf(fn () => $this->domain_type == 'standard'),
                'nullable','string','max:255',
                'regex:/^(?!-)[A-Za-z0-9-]+(?<!-)$/',
            ],
            '_full_url' => [
                Rule::requiredIf(fn () => $this->domain_type == 'standard'),
                Rule::unique('installations', 'url')
            ],
        ];
    }

    public function attributes(): array
    {
        return ['_full_url' => 'sub_domain'];
    }

    public function messages(): array
    {
        return [
            '_full_url.unique' => 'This subdomain is already taken.',
            'domain.unique' => 'This domain is already taken.',
        ];
    }
}
