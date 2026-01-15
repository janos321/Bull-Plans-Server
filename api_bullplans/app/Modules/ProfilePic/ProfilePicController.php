<?php

namespace App\Modules\ProfilePic;

use App\Http\Controllers\Controller;
use App\Modules\ApiResponse;
use App\Modules\ProfilePic\Requests\ProfileUploadRequest;
use App\Modules\ProfilePic\Requests\ProfileDownloadRequest;

class ProfilePicController extends Controller
{
    private ProfilePicService $service;

    public function __construct()
    {
        $this->service = new ProfilePicService();
    }

    public function upload(ProfileUploadRequest $request)
    {
        try {
            $this->service->upload($request->email, $request->file('file'));
            return ApiResponse::success(true, 'Profile picture uploaded.');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, 'Upload failed', 500);
        }
    }

    public function download(ProfileDownloadRequest $request)
    {
        try {
            return $this->service->download($request->email);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Download failed',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
