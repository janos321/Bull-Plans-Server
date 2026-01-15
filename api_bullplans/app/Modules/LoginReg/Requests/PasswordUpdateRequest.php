<?php

namespace App\Modules\LoginReg\Requests;

use App\Modules\BaseRequest;

class PasswordUpdateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email'        => ['required', 'email'],
            'new_password' => ['required', 'string', 'min:3'],
        ];
    }
}
