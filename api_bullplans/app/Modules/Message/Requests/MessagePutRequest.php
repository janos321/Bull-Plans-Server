<?php

namespace App\Modules\Message\Requests;

use App\Modules\BaseRequest;

class MessagePutRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'from'      => ['required', 'email'],
            'emails'    => ['required', 'array', 'min:1'],
            'emails.*'  => ['required', 'email'],
            'text'      => ['required', 'string', 'min:1', 'max:2000'],
        ];
    }
}
