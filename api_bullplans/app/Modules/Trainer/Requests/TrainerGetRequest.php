<?php

namespace App\Modules\Trainer\Requests;

use App\Modules\BaseRequest;

class TrainerGetRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:Trainer,email'],
        ];
    }
}
