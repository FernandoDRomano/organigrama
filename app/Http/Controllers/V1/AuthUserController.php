<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\V1\UserRequest;
use App\Http\Requests\V1\LoginRequest;
use App\Http\Resources\V1\UserResource;

class AuthUserController extends Controller
{
    
    public function register(UserRequest $request){
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        return (UserResource::make($user->refresh()))
               ->additional(["message" => "User created!!"])
               ->response()
               ->setStatusCode(201);
    }

    public function login(LoginRequest $request){

        $user = User::where('email', '=', $request->email)->first();
        
        if ($user && Hash::check($request->password, $user->password)) {

            if($user->status === User::STATUS_ACTIVE){
                $token = $user->createToken("auth_token")->plainTextToken;

                return (UserResource::make($user))
                       ->additional(["access_token" => $token])
                       ->response()
                       ->setStatusCode(200);
            }

            return response()->json([
                "message" => "The account is blocked.",
            ], 401);

        }

        return response()->json([
            "message" => "The given data was invalid.",
            "errors" => [
                "email" => "These credentials do not match our records."
            ]
        ], 401);
        
    }

    public function logout(){
        auth()->user()->tokens()->delete();
        return response()->json([
            "message" => "User logout"
        ], 200);
    }

}
