<?php
namespace App\Modules\LoginReg;

use App\Http\Controllers\Controller;
use App\Modules\ApiResponse;
use App\Modules\LoginReg\Requests\{
    LoginRequest,
    RegisterUserRequest,
    RegisterTrainerRequest,
    PasswordUpdateRequest,
    CheckEmailRequest
};

class LoginRegController extends Controller
{
    private LoginRegService $service;

    public function __construct()
    {
        $this->service = new LoginRegService();
    }

    public function login(LoginRequest $request)
    {
        $role = $this->service->login(
            $request->email,
            $request->password
        );

        return ApiResponse::success($role);
    }

    public function registerUser(RegisterUserRequest $request)
    {
        return ApiResponse::success(
            $this->service->registerUser($request->validated())
        );
    }

    public function registerTrainer(RegisterTrainerRequest $request)
    {
        return ApiResponse::success(
            $this->service->registerTrainer($request->validated())
        );
    }

    public function updatePassword(PasswordUpdateRequest $request)
    {
        return ApiResponse::success(
            $this->service->updatePassword(
                $request->email,
                $request->new_password
            )
        );
    }
    
    public function checkEmail(CheckEmailRequest $request)
    {
        try {
            return ApiResponse::success($this->service->checkEmail($request->email));
        } catch (\Throwable $e) {
            return ApiResponse::exception($e);
        }
    }
}
