<?php

namespace App\Modules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Modules\ApiResponse;

class BaseRequest extends FormRequest
{
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error('Validation error', 422, $validator->errors())
        );
    }

    public function authorize()
    {
        return true;
    }
}
