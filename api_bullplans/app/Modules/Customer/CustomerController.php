<?php

namespace App\Modules\Customer;

use App\Http\Controllers\Controller;
use App\Modules\ApiResponse;

use App\Modules\Customer\Requests\CustomerGetRequest;
use App\Modules\Customer\Requests\CustomerPutRequest;

class CustomerController extends Controller
{
    private CustomerService $service;

    public function __construct()
    {
        $this->service = new CustomerService();
    }

    public function getCustomers(CustomerGetRequest $request)
    {
        try {
            $email = $request->email;
            $data = $this->service->getCustomers($email);

            return ApiResponse::success($data, "Customers retrieved");
        } catch (\Throwable $e) {
            return ApiResponse::exception($e);
        }
    }

    public function putCustomer(CustomerPutRequest $request)
    {
        try {
            $success = $this->service->saveCustomer(
                $request->trainer_email,
                $request->customer_email,
                $request->customer_data
            );

            return ApiResponse::success($success, "Customer saved");
        } catch (\Throwable $e) {
            return ApiResponse::exception($e);
        }
    }
}
