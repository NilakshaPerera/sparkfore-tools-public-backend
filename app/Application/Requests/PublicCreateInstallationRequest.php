<?php

namespace App\Application\Requests;

use App\Rules\UniqueStandardInstallation;
use Illuminate\Foundation\Http\FormRequest;

class PublicCreateInstallationRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'siteName' => ['required', 'string', 'max:255', 'blasp_check', 'regex:/^[a-zA-Z0-9\-]+$/', new UniqueStandardInstallation],
            'password' => 'required|string|min:8|max:20|confirmed',
            'password_confirmation' => 'required|string|min:8|max:20|same:password',
            'firstName' => 'required|string|max:255|blasp_check|regex:/^[a-zA-Z]+$/',
            'lastName' => 'required|string|max:255|blasp_check|regex:/^[a-zA-Z]+$/',
            'email' => 'required|email|max:255|unique:customers,invoice_email|unique:users,email',
            'phone' => 'max:20',
            'terms_and_conditions' => 'required|accepted',
            //'captcha' => 'required|max:10|captcha',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'terms_and_conditions.*' => trans("validation.custom.public_installation.terms_and_conditions"),
            'siteName.regex' => trans("validation.custom.public_installation.siteName_regex"),
            'password.confirmed' => trans("validation.custom.public_installation.password_confirmed"),
            'confirmPassword.same' => trans("validation.custom.public_installation.confirmPassword_same"),
            'firstName.regex' => trans("validation.custom.public_installation.firstName_regex"),
            'lastName.regex' => trans("validation.custom.public_installation.lastName_regex"),
            //'captcha.captcha' => trans("validation.custom.public_installation.captcha_captcha"),
            'email.unique' => trans("validation.custom.public_installation.email_unique"),
        ];
    }
}
