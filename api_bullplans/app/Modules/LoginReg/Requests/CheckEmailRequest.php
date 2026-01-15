<?php

namespace App\Modules\LoginReg\Requests;

use App\Modules\BaseRequest;

class CheckEmailRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email']
        ];
    }
}
