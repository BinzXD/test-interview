<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\MessageBag;
use App\Helpers\Api;

class ListRequest extends FormRequest
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
            'page' => ['nullable', 'numeric'],
            'per_page' => ['nullable', 'numeric'],
            'q' => ['nullable', 'string'],
            'limit' => ['nullable', 'numeric'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $error = new MessageBag($validator->errors()->messages());
        $errorFirst = $error->first();

        throw new HttpResponseException(
            Api::send([
                'errors' => [
                    'code' => 422,
                    'message' => $errorFirst,
                ]
            ], 422)
        );
    }
}
