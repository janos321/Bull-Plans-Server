<?php

namespace App\Modules\Message\Requests;

use App\Modules\BaseRequest;

class MessageGetRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
