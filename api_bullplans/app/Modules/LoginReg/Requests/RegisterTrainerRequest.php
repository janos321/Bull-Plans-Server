<?php

namespace App\Modules\LoginReg\Requests;

use App\Modules\BaseRequest;

class RegisterTrainerRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name'         => ['required', 'string'],
            'email'        => ['required', 'email', 'unique:Trainer,email', 'unique:Users,email'],
            'date'         => ['required', 'date'],
            'password'     => ['required', 'string'],
            'profile_data' => ['required', 'array'],
            'valid_data'   => ['nullable', 'array'],
        ];
    }
}
