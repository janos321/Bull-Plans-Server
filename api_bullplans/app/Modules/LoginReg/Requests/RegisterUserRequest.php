<?php

namespace App\Modules\LoginReg\Requests;

use App\Modules\BaseRequest;

class RegisterUserRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name'         => ['required', 'string'],
            'email'        => ['required', 'email', 'unique:Users,email', 'unique:Trainer,email'],
            'date'         => ['required', 'date'],
            'password'     => ['required', 'string'],
            'profile_data' => ['required', 'array'],
            'valid_data'   => ['nullable', 'array'],
        ];
    }
}
