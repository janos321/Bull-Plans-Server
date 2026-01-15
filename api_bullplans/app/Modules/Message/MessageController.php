<?php

namespace App\Modules\Message;

use App\Http\Controllers\Controller;
use App\Modules\ApiResponse;

use App\Modules\Message\Requests\MessageGetRequest;
use App\Modules\Message\Requests\MessagePutRequest;

class MessageController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = new MessageService();
    }

    public function getMessages(MessageGetRequest $request)
    {
        try {
            $result = $this->service->getMessagesFor($request->email);
            return ApiResponse::success($result, 'Messages retrieved');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, 'Database error', 500);
        }
    }

    public function putMessages(MessagePutRequest $request)
    {
        $validated = $request->validated();

        try {
            $ok = $this->service->storeMessage(
                $validated['from'],
                $validated['emails'],
                $validated['text']
            );

            return ApiResponse::success($ok, 'Message stored');
        } catch (\Throwable $e) {
            return ApiResponse::exception($e, 'Database error', 500);
        }
    }
}
