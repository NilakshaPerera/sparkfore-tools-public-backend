<?php

namespace App\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreHostingRequest extends FormRequest
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
            'production_price_month' => 'required',
            'staging_price_month' => 'required',
            'yearly_price_increase' => 'required',
            'description' => 'required',
            'availability' => 'required',
            'hosting_type_id' => 'required',
            'hosting_provider_id' => Rule::requiredIf(function () {
                return $this->hosting_type_id == 1;
            }),
            'base_package_id' => 'required',
            'backup_price_monthly' => 'required',
            'customers' => [
                Rule::requiredIf(function () {
                    return $this->availability == 'private';
                }),
            ],
        ];
    }
}
