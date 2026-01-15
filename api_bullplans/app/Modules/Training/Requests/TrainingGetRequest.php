<?php

namespace App\Modules\Training\Requests;

use App\Modules\BaseRequest;

class TrainingGetRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email']
        ];
    }
}
