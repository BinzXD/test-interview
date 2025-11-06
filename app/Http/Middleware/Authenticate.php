<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class Authenticate
{
     public function handle($request, Closure $next, ...$guards)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                    'message'   => 'Unauthorized',
                    'success'   => false,
                    'status'    => false,
                    'data'      => null
                ], 401);
            }

            return $next($request);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'message'   => 'Token telah kedaluwarsa.',
                'success'   => false,
                'status'    => false,
                'data'      => null
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'message'   => 'Token tidak valid.',
                'success'   => false,
                'status'    => false,
                'data'      => null
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'message'   => 'Token tidak ditemukan atau terjadi kesalahan JWT.',
                'success'   => false,
                'status'    => false,
                'data'      => null
            ], 401);
        }
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        return null;
    }
}
