<?php

namespace App\Modules\Customer\Requests;

use App\Modules\BaseRequest;

class CustomerGetRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
