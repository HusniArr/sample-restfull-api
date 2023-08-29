<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function register_member(UserRegisterRequest $request) : JsonResponse
    {
        $data = $request->validated();
        $count = User::where('username', $data['username'])->count();
        if($count == 1) {
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
        $user->is_admin = 2;

        $user->save;

        return (new UserResource($user))->response()->setStatusCode(201);

    }
}
