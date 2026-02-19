<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone_number' => ['sometimes', 'string', 'unique:users,phone_number,' . $userId],
            'lang' => ['sometimes', 'string', 'in:uz,ru,en'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
