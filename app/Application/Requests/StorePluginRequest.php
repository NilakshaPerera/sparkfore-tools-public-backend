<?php

namespace App\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StorePluginRequest extends FormRequest
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
            'git_url' => 'required',
            'git_version_type_id' => 'required',
            'accessibility_type' => [
                Rule::requiredIf(function () {
                    return auth()->user()->role->name == 'admin';
                }),
            ],
            'availability' => [
                Rule::requiredIf(function () {
                    return auth()->user()->role->name == 'admin';
                }),
            ],
            'softwares' => 'required',
            'customers' => [
                Rule::requiredIf(function () {
                    return auth()->user()->role->name == 'admin' && $this->availability == 'private';
                }),
            ],
        ];
    }
}
