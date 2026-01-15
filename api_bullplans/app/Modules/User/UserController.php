<?php

namespace App\Modules\User;

use App\Http\Controllers\Controller;
use App\Modules\ApiResponse;
use App\Modules\User\Requests\UserGetRequest;
use App\Modules\User\Requests\UserGetValidDataRequest;
use App\Modules\User\Requests\UserLogoutRequest;
use App\Modules\User\Requests\UserUpdateValidDataRequest;
use App\Modules\User\Requests\UserUpdateProfileRequest;

class UserController extends Controller
{
    private UserService $service;

    public function __construct()
    {
        $this->service = new UserService();
    }

    public function get(UserGetRequest $request)
    {
        $validated = $request->validated();

        try {
            $data = $this->service->getUser($validated['email']);

            return ApiResponse::success($data, 'User loaded.');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, 'Could not load user.');
        }
    }

    public function getValidData(UserGetValidDataRequest $request)
    {
        $validated = $request->validated();

        try {
            $valid = $this->service->getValidData($validated['email']);

            return ApiResponse::success($valid, 'Valid data loaded.');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, 'Could not load valid data.');
        }
    }

    public function logout(UserLogoutRequest $request)
    {
        $validated = $request->validated();

        try {
            $ok = $this->service->logout($validated['email'], $validated['profile_data']);

            return ApiResponse::success($ok, 'User logged out.');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, 'Logout failed.');
        }
    }

    public function updateValidData(UserUpdateValidDataRequest $request)
    {
        $validated = $request->validated();

        try {
            $ok = $this->service->updateValidData($validated['email'], $validated['valid_data']);

            return ApiResponse::success($ok, 'Valid data updated.');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, 'Update failed.');
        }
    }

    public function updateProfileData(UserUpdateProfileRequest $request)
    {
        $validated = $request->validated();

        try {
            $ok = $this->service->updateProfile($validated['email'], $validated['profile_data']);

            return ApiResponse::success($ok, 'Profile data updated.');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, 'Update failed.');
        }
    }
}
