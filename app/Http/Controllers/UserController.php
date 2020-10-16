<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\LoginToken;

class UserController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:2|max:20|alpha',
            'last_name' => 'required|min:2|max:20|alpha',
            'username' => 'required|min:5|max:12|regex: /^[A-Za-z0-9._]*$/|unique:users',
            'password' => 'required|min: 5|max:12',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'invalid field',
            ], 422);
        }else{
            $password = Hash::make($request->password);
            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->username = $request->username;
            $user->password = $password;
            if($user->save()){
                $generateToken = Hash::make($user->id);
                $token = new LoginToken;
                $token->user_id = $user->id;
                $token->token = $generateToken;
                if($token->save()){
                    return response()->json([
                        'token' => $token->token
                    ], 200);
                }else{
                    return response()->json([
                        'message' => 'invalid field',
                    ], 422);
                }
            }else{
                return response()->json([
                    'message' => 'invalid field',
                ], 422);
            }
            
        }

    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'invalid field',
            ], 422);
        }else{
            $user = User::where('username', $request->username)->first();
            if($user){
                if(Hash::check($request->password, $user->password)){
                    $generateToken = Hash::make($user->id);
                    $token = new LoginToken;
                    $token->user_id = $user->id;
                    $token->token = $generateToken;
                    if($token->save()){
                        return response()->json([
                            'token' => $token->token
                        ], 200);
                    }else{
                        return response()->json([
                            'message' => 'invalid login',
                        ], 401);
                    }
                }else{
                    return response()->json([
                        'message' => 'invalid login',
                    ], 401);
                }
            }else{
                return response()->json([
                    'message' => 'invalid login',
                ], 401);
            }
        }
    }

    public function logout(Request $request){
        LoginToken::where('token', $request->token)
                            ->join('users', 'users.id', 'login_tokens.user_id')
                            ->delete();
        return response()->json([
            'message' => 'logout success',
        ], 200);
    }
}
