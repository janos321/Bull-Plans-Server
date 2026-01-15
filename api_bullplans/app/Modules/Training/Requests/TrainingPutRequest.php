<?php

namespace App\Modules\Training\Requests;

use App\Modules\BaseRequest;

class TrainingPutRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email'          => ['required', 'email'],
            'training_data'  => ['required', 'array'],
        ];
    }
}
