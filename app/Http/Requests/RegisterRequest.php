<?php

namespace App\Http\Requests;

use Illuminate\Support\MessageBag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\Api;
use Illuminate\Http\Exceptions\HttpResponseException;


class RegisterRequest extends FormRequest
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
            'username' => 'required|string|max:100|unique:users',
            'email' => 'required|string|email|max:150|unique:users',
            'phone' => 'required|string|max:20|regex:/^62[0-9]{9,18}$/|unique:users',
            'password' => 'required|string|min:8|confirmed'
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
