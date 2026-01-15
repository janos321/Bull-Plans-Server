<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

use App\Modules\Offer\OfferController;
use App\Modules\Message\MessageController;
use App\Modules\Motivation\MotivationController;
use App\Modules\Customer\CustomerController;
use App\Modules\LoginReg\LoginRegController;
use App\Modules\Training\TrainingController;
use App\Modules\Trainer\TrainerController;
use App\Modules\User\UserController;
use App\Modules\ProfilePic\ProfilePicController;

Route::group(['middleware' => 'security.code'], function () {

    // --- AUTH endpoints throttling ---
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/loginreg/login', [LoginRegController::class, 'login']);
        Route::post('/loginreg/user/register', [LoginRegController::class, 'registerUser']);
        Route::post('/loginreg/trainer/register', [LoginRegController::class, 'registerTrainer']);
        Route::post('/loginreg/password/update', [LoginRegController::class, 'updatePassword']);
        Route::post('/loginreg/email/check', [LoginRegController::class, 'checkEmail']);
    });

    // --- Other endpoints ---
    Route::post('/offers/get', [OfferController::class, 'getOffers']);
    Route::post('/offers/get/trainer', [OfferController::class, 'getTrainerOffers']);
    Route::post('/offers/post', [OfferController::class, 'postOffers']);

    Route::post('/messages/get', [MessageController::class, 'getMessages']);
    Route::put('/messages/put', [MessageController::class, 'putMessages']);

    Route::post("/motivation/get", [MotivationController::class, "getMotivation"]);
    Route::post("/motivation/post", [MotivationController::class, "postMotivation"]);

    Route::post("/customer/get", [CustomerController::class, "getCustomers"]);
    Route::put("/customer/put", [CustomerController::class, "putCustomer"]);

    Route::post('/training/get', [TrainingController::class, 'getTrainingData']);
    Route::put('/training/put', [TrainingController::class, 'saveTrainingData']);

    Route::put('/trainer/logout', [TrainerController::class, 'logout']);
    Route::post('/trainer/get', [TrainerController::class, 'get']);
    Route::put('/trainer/update/profileData', [TrainerController::class, 'updateProfileData']);

    Route::prefix('user')->group(function () {
        Route::post('get', [UserController::class, 'get']);
        Route::post('get/validData', [UserController::class, 'getValidData']);
        Route::put('logout', [UserController::class, 'logout']);
        Route::put('update/validData', [UserController::class, 'updateValidData']);
        Route::put('update/profileData', [UserController::class, 'updateProfileData']);
    });

    Route::post('/profile/upload', [ProfilePicController::class, 'upload']);
    Route::post('/profile/download', [ProfilePicController::class, 'download']);
});

Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        return response()->json(['success' => true, 'message' => 'Database connected!']);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
});
