<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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
}
