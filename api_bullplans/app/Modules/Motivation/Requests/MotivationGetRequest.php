<?php

namespace App\Modules\Motivation\Requests;

use App\Modules\BaseRequest;

class MotivationGetRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'lang' => ['required', 'string'],
        ];
    }
}
