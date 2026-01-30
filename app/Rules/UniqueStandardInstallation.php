<?php

namespace App\Rules;

use App\Domain\Models\Installation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueStandardInstallation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $installationUrl = $value . '.' . SPARKFORE_DOMAIN;

        if (Installation::where('url', $installationUrl)->exists()) {
            $fail(trans('validation.custom.unique_standard_installation', ['value' => $value]));
        }
    }
}
