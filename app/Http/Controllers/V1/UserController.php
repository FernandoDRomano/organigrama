<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserCollection;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\V1\UserResource;

class UserController extends Controller
{
    
    public function profile(){
        $user = User::withCount('organizations')->findOrFail(auth()->id());

        return (UserResource::make($user))
               ->additional(["message" => "User profile"])
               ->response()
               ->setStatusCode(200);
    }

    public function status(User $user){
        Gate::authorize('update-status-user');

        $user->updateStatusAndTokens();

        $user->save();
        
        return (UserResource::make($user))
               ->additional(["message" => "User status updated!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function index(){
        $this->authorize('viewAny', User::class);
        
        $users = User::withCount('organizations')->orderBy('id', 'DESC')->paginate(10);
        return (UserCollection::make($users))
               ->additional(["message" => "Users all!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function show(User $user){
        $this->authorize('view', $user);
        
        $user->loadMissing('organizations')->loadCount('organizations');

        return (UserResource::make($user))
               ->additional(["message" => "User"])
               ->response()
               ->setStatusCode(200);
    }

    public function destroy(User $user){
        $this->authorize('delete', $user);
        
        $user->delete();

        return response()->json([
            "message" => "User delete!!"
        ], 204);
    }

}
