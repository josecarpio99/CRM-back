<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    public function login(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if ( ! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken('User ' . $user->name . ' token')->plainTextToken;

        return $this->responseSuccess([
            'token' => $token,
            'user'  => new UserResource($user)
        ]);
    }

    public function user()
    {
        if (! Auth::check()) return null;

        $user = Auth::user()->load(
            'lastIncompletedTasks.taskable',

        );

        $user->load(['notifications' => function($query) {
            $query->limit(50);
        }]);

        return new UserResource($user);
    }
}
