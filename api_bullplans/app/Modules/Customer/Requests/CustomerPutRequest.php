<?php

namespace App\Modules\Customer\Requests;

use App\Modules\BaseRequest;

class CustomerPutRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'trainer_email'  => ['required', 'email'],
            'customer_email' => ['required', 'email'],
            'customer_data'  => ['required', 'array'],
        ];
    }
}
