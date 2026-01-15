<?php
namespace App\Modules\Offer\Requests;

use App\Modules\BaseRequest;

class OfferPostRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email'  => ['required', 'email'],
            'offers' => ['array'],
        ];
    }
}