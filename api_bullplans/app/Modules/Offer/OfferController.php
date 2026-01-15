<?php

namespace App\Modules\Offer;

use App\Http\Controllers\Controller;
use App\Modules\ApiResponse;

use App\Modules\Offer\OfferService;
use App\Modules\Offer\Requests\OfferGetRequest;
use App\Modules\Offer\Requests\OfferPostRequest;
use App\Modules\Offer\Requests\OfferTrainerGetRequest;

class OfferController extends Controller
{
    private $offerService;

    public function __construct()
    {
        $this->offerService = new OfferService();
    }

    public function getOffers(OfferGetRequest $request)
    {
        try {
            $result = $this->offerService->getOffers();
            return ApiResponse::success($result, 'Offers retrieved');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, 'Database error', 500);
        }
    }

    public function postOffers(OfferPostRequest $request)
    {
        $validated = $request->validated();

        try {
            $success = $this->offerService->saveOffers(
                $validated['email'],
                $validated['offers']
            );

            return ApiResponse::success($success, 'Offers saved');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, 'Database error', 500);
        }
    }
    
    public function getTrainerOffers(OfferTrainerGetRequest $request)
    {
        $validated = $request->validated();
    
        try {
            $offers = $this->offerService->getOffersByEmail(
                $validated['email']
            );
    
            return ApiResponse::success($offers, 'Trainer offers retrieved');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, 'Database error', 500);
        }
    }

}
