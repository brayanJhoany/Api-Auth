<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Illuminate\Support\Facades\Http;

use \Firebase\JWT\JWT;

class JWTMiddleware extends BaseMiddleware {
    private $baseUrl='https://5fbbd1f2c09c200016d412a5.mockapi.io/';

    public function handle($request,Closure $next){
        $token = $request->bearerToken();
        $users = Http::get($this->baseUrl.'user');
        $users = $users->json();
        foreach($users as $user){
            if($token==$user['token']){
                return $next($request);
            }
        }
        return response()->json([
            'success' => false,
            'message' => "token invalido",
            'data' => null
        ], 401);
    }

}
