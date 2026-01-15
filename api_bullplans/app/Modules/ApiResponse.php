<?php

namespace App\Modules;

use Illuminate\Support\Facades\Log;

class ApiResponse
{
    /**
     * Success Response
     */
    public static function success($data = null, string $message = 'OK', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], $code);
    }

    /**
     * Error Response
     */
    public static function error(string $message, int $code = 400, $data = null)
    {
        Log::error('[API ERROR] ' . $message, [
            'status_code' => $code,
            'data'        => $data
        ]);

        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => $data
        ], $code);
    }

    /**
     * Exception Handler (Controllers use this)
     */
    public static function exception(\Throwable $e, string $message = 'Unexpected error', int $code = 500)
    {
        Log::error('[API EXCEPTION] ' . $message, [
            'error' => $e->getMessage(),
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => app()->environment('local') ? $e->getMessage() : null
        ], $code);
    }
}