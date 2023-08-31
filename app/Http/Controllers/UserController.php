<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Models\User;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request) : JsonResponse
    {
        $data = $request->validated();
        $row = User::where('username', $data['username'])->first();

        if($row) {
            throw new HttpResponseException(response([
                "errors" => [
                    "username" => [
                        "username already exists"
                    ]
                ]
            ], 400));
        }
        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->is_admin = 0;

        $user->save();

        return (new UserResource($user))->response()->setStatusCode(201);

    }

    public function register_admin(UserRegisterRequest $request) : JsonResponse
    {
        $data = $request->validated();
        $row = User::where('username', $data['username'])->first();

        if($row) {
            throw new HttpResponseException(response([
                'errors' => [
                    'username' => [
                        'username already exists.'
                    ]
                ]
                    ], 400));
        }

        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->is_admin = 1;
        $user->save();

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function login(UserLoginRequest $request) : UserResource
    {
        $data = $request->validated();
        $user = User::where('username', $data['username'])->first();
        
        if(!$user || !Hash::check($data['password'], $user->password)) {
            throw new HttpResponseException(response([
                'errors' => [
                    'username' => [
                        'username or password wrong.'
                        ]
                        ]
                    ], 401));
        }
                
        $user->token = Str::uuid()->toString();
        $user->save();

        return (new UserResource($user));
    }

    public function get(Request $request): UserResource
    {
        $user = Auth::user();
        return new UserResource($user);
    }
}
