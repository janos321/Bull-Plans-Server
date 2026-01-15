<?php

namespace App\Modules\User\Requests;

use App\Modules\BaseRequest;

class UserGetValidDataRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:Users,email'],
        ];
    }
}
