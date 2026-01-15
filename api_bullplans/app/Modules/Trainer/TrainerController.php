<?php

namespace App\Modules\Trainer;

use App\Http\Controllers\Controller;
use App\Modules\ApiResponse;
use App\Modules\Trainer\Requests\TrainerGetRequest;
use App\Modules\Trainer\Requests\TrainerLogoutRequest;
use App\Modules\Trainer\Requests\TrainerUpdateProfileRequest;

class TrainerController extends Controller
{
    private TrainerService $service;

    public function __construct()
    {
        $this->service = new TrainerService();
    }

    public function get(TrainerGetRequest $request)
    {
        try {
            $trainer = $this->service->get($request->validated()['email']);
            return ApiResponse::success($trainer, 'Trainer loaded.');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, 'Failed to load trainer', 500);
        }
    }

    public function updateProfileData(TrainerUpdateProfileRequest $request)
    {
        $validated = $request->validated();

        try {
            $success = $this->service->updateProfileData(
                $validated['email'],
                $validated['profile_data']
            );

            return ApiResponse::success($success, 'Trainer profile updated.');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, 'Profile update failed', 500);
        }
    }

    public function logout(TrainerLogoutRequest $request)
    {
        $validated = $request->validated();

        try {
            $success = $this->service->logout(
                $validated['email'],
                $validated['profile_data']
            );

            return ApiResponse::success($success, 'Trainer logged out.');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, 'Logout failed', 500);
        }
    }
}
