<?php

namespace App\Modules\Offer\Requests;

use App\Modules\BaseRequest;

class OfferTrainerGetRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
