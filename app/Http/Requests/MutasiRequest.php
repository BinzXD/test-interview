<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\MessageBag;
use App\Helpers\Api;

class MutasiRequest extends FormRequest
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
            'location_id' => 'required|exists:locations,id',
            'date' => 'required|date',
            'type' => 'required|in:in,out',
            'reason' => 'required|string|max:200',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric',
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
