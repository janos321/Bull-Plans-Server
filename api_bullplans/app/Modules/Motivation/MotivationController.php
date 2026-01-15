<?php

namespace App\Modules\Motivation;

use App\Http\Controllers\Controller;
use App\Modules\ApiResponse;

use App\Modules\Motivation\Requests\MotivationGetRequest;
use App\Modules\Motivation\Requests\MotivationPostRequest;

class MotivationController extends Controller
{
    private MotivationService $service;

    public function __construct()
    {
        $this->service = new MotivationService();
    }

    public function getMotivation(MotivationGetRequest $request)
    {
        try {
            $lang = $request->lang;
            $mot = $this->service->getTodayMotivation($lang);

            if (!$mot) {
                return ApiResponse::error("No motivation found");
            }

            return ApiResponse::success($mot, "Motivation retrieved");
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, "Database error", 500);
        }
    }

    public function postMotivation(MotivationPostRequest $request)
    {
        try {
            $success = $this->service->storeNewMotivation(
                $request->translations
            );

            return ApiResponse::success($success, "Motivation saved");
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, "Database error", 500);
        }
    }
}
