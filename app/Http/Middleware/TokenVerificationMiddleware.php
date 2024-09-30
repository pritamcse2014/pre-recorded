<?php

namespace App\Http\Middleware;

use App\Helper\JWTToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenVerificationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('token');
        $result = JWTToken::VerifyToken($token);
        if ($result == "unauthorized") {
            return redirect('/userLogin');
        }
        // Check if $result is an object or array before accessing its properties
        if (is_object($result) || is_array($result)) {
            $request->headers->set('email', $result->userEmail ?? '');  // Optional chaining with null coalescing
            $request->headers->set('id', $result->userId ?? '');
            return $next($request);
        }

        return redirect('/userLogin'); // Redirect if result isn't valid
//        return $next($request);
    }
}
