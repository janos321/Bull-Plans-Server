<?php

namespace App\Modules\Motivation\Requests;

use App\Modules\BaseRequest;

class MotivationPostRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'translations' => ['required', 'array'],
            'translations.*' => ['required', 'string'],
        ];
    }
}
