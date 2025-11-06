<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\MessageBag;
use App\Helpers\Api;

class ProductRequest extends FormRequest
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
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:100',
            'category_id' => 'required|exists:category,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'unit' => 'required|string|max:100',
            'price' => 'required|numeric',
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
