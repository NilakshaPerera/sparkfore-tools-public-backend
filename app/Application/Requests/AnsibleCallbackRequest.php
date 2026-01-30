<?php

namespace App\Application\Requests;

use App\Domain\Exception\SparkforeException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AnsibleCallbackRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'job_id' => 'required|exists:remote_jobs,id',
            'status' => 'required|in:RECEIVED,PROCESSING,COMPLETE,ERROR,ANSIBLE_COMPLETE',
            'message' => 'required|max:500',
            'uri' => 'nullable|string|max:500',
            'registry_url' => 'nullable|string|max:500'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new SparkforeException($validator->errors());
    }
}
