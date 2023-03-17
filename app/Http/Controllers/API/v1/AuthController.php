<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function verify(string $user_id): Application|Factory|View
    {
        if (! request()->hasValidSignature()) {
            return view('auth.verify-email', ['status' => 'error', 'message' => 'Verification link expired. Kindly try again.']);
        }

        try {
            $this->authService->verifyEmail($user_id);
        } catch (Throwable $exception) {
            report($exception);

            return view('auth.verify-email', ['status' => 'error', 'message' => $exception->getMessage()]);
        }

        return view('auth.verify-email', ['status' => 'success', 'message' => 'Email address verified successfully. You can now close this window.']);
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = $this->authService->findUserByEmail($request->email);

            if ((! $user) || ($user->doesNotHaveRole($request->role)) || (! Hash::check($request->password, $user->password))) {
                return response()->unauthorized('Invalid credentials.');
            }

            if (! $user->hasVerifiedEmail()) {
                dispatch(function () use ($user) {
                    $user->sendEmailVerificationNotification();
                });

                return response()->forbidden('Please click the link we just sent to you in an email to verify your email address.');
            }

            $token = $user->createToken($request->userAgent())->plainTextToken;
        } catch (Throwable $exception) {
            report($exception);

            return response()->internalServerError('An error occurred while trying to process your request.');
        }

        $data = match ($request->role) {
            RoleEnum::PATIENT => [
                'user' => new UserResource($user),
            ],
        };

        return response()->success(compact('token') + $data, 'Login successful.');
    }
}
