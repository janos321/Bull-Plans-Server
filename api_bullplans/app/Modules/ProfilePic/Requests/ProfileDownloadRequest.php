<?php

namespace App\Modules\ProfilePic\Requests;

use App\Modules\BaseRequest;

class ProfileDownloadRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
