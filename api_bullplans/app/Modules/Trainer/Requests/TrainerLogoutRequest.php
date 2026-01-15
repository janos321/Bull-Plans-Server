<?php

namespace App\Modules\Trainer\Requests;

use App\Modules\BaseRequest;

class TrainerLogoutRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email'        => ['required', 'email'],
            'profile_data' => ['required', 'array'],
        ];
    }
}
