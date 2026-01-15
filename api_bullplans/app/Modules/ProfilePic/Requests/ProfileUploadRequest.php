<?php

namespace App\Modules\ProfilePic\Requests;

use App\Modules\BaseRequest;

class ProfileUploadRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'file'  => ['required', 'file', 'mimes:jpg,jpeg,png,webp'],
        ];
    }
}
