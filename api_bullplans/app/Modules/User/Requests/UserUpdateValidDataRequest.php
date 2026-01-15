<?php

namespace App\Modules\User\Requests;

use App\Modules\BaseRequest;

class UserUpdateValidDataRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email'      => ['required', 'email', 'exists:Users,email'],
            'valid_data' => ['required', 'array']
        ];
    }
}
