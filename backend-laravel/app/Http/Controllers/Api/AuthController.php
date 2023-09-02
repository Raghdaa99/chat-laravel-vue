<?php

namespace App\Http\Controllers\Api;

use App\Events\OnlineStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{

    public function register(RegisterRequest $request)
    {

        // encrypt password before save to database
        $data = $request->validated();
        $data['password'] = bcrypt($request->password);

        // create user
        $user = User::create($data);
        $token = $user->createToken('chatApp')->plainTextToken;


        $user = new UserResource($user);

        return $this->sendResponse([
            "user" => new UserResource($user),
            "token" => $token],
            "success Created User");
    }

    public function login(LoginRequest $request)
    {

        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return $this->sendError('Invalid username or password', 422);
        } else {
            /** @var User $user */
            $user = Auth::user();
            $token = $user->createToken('chatApp')->plainTextToken;


            $user = new UserResource($user);

            return $this->sendResponse([
                "user" => new UserResource($user),
                "token" => $token],
                "success login");
        }

    }

    public function logout(Request $request)
    {
        $user = $request->user();


        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out'
        ], 200);
    }

}
