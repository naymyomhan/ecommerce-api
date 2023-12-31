<?php

namespace App\Http\Requests\Seller;

use App\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class SellerRegisterRequest extends FormRequest
{
    use ResponseTrait;
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
            'name' => 'required|string|max:30|min:3',
            'email' => 'required|email|unique:sellers,email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // $errors = $validator->errors()->toArray();
        // $formattedErrors = [];
        // foreach ($errors as $field => $errorMessages) {
        //     $formattedErrors[$field] = $errorMessages[0];
        // }

        $response = new JsonResponse([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422);

        throw new HttpResponseException($response);
    }
}
