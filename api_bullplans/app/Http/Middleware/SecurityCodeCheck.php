<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityCodeCheck
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->input('biztonsagiKod') !== config('app.security_code')) {
            return response()->json(['success' => false, 'message' => 'Invalid security code'], 401);
        }

        return $next($request);
    }
}
