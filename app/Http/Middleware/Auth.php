<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\User;

class Auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = [
            'message' => 'unauthorized user'
        ];

        if(!$request->token){
            return response()->json($response, 401);
        }

        $user = User::select('users.*')->where('token', $request->token)
                        ->join('login_tokens', 'users.id', 'login_tokens.user_id')
                        ->first();
        
        if(!$user){
            return response()->json($response, 401);
        }

        $request->attributes->set('user', $user);

        return $next($request);
    }
}
