<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\LoginRequest;
use App\Http\Requests\V1\UserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    
    public function register(UserRequest $request){
        
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            "data" => $user,
            "message" => "User created!!!"
        ], 201);

    }

    public function login(LoginRequest $request){

        $user = User::where('email', '=', $request->email)->first();
        
        if ($user && Hash::check($request->password, $user->password)) {
            
            $token = $user->createToken("auth_token")->plainTextToken;

            return response()->json([
                "data" => $user,
                "access_token" => $token
            ], 200);

        }

        return response()->json([
            "message" => "Error: your credentials are incorrects"
        ], 401);
        
    }

    public function profile(){
        return response()->json([
            "data" => auth()->user()
        ]);
    }

    public function logout(){
        auth()->user()->tokens()->delete();
        return response()->json([
            "message" => "User logout"
        ]);
    }

}
