<?php

namespace App\Modules\User\Requests;

use App\Modules\BaseRequest;

class UserLogoutRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email'        => ['required', 'email', 'exists:Users,email'],
            'profile_data' => ['required', 'array'],
        ];
    }
}
