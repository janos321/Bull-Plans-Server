<?php

namespace App\Modules\Training;

use App\Http\Controllers\Controller;

use App\Modules\ApiResponse;

use App\Modules\Training\Requests\TrainingGetRequest;
use App\Modules\Training\Requests\TrainingPutRequest;

class TrainingController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = new TrainingService();
    }

    public function getTrainingData(TrainingGetRequest $request)
    {
        try {
            $data = $this->service->getTrainingData($request->email);
            return ApiResponse::success($data, 'Training data retrieved');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e);
        }
    }

    public function saveTrainingData(TrainingPutRequest $request)
    {
        try {
            $success = $this->service->saveTrainingData(
                $request->email,
                $request->training_data
            );

            return ApiResponse::success($success, 'Training data saved');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e);
        }
    }
}
