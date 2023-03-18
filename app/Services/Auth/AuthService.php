<?php

namespace App\Services\Auth;

use App\Enums\RoleEnum;
use App\Interfaces\AuthInterface;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Verified;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Throwable;

class AuthService implements AuthInterface
{
    /**
     * @param array $data
     * @param string $role
     * @return User
     * @throws Throwable
     */
    public function createAccount(array $data, string $role): User
    {
        return DB::transaction(function () use ($data, $role) {
            $user = User::create($data);

            $user->assignRole($role);

            if ($role === RoleEnum::DOCTOR) {
                Password::sendResetLink(['email' => $user->email]);
            }

            return $user;
        });
    }

    /**
     * @param string $id
     * @return void
     * @throws Throwable
     */
    public function verifyEmail(string $id): void
    {
        $user = User::findOrFail($id);

        throw_if(
            $user->hasVerifiedEmail(),
            new Exception('Email address already verified.')
        );

        $user->markEmailAsVerified();

        event(new Verified($user));
    }

    /**
     * @param string $email
     * @return User|null
     */
    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * @param string $email
     * @param string $token
     * @param string $password
     * @return void
     * @throws Throwable
     */
    public function resetPassword(string $email, string $token, string $password): void
    {
        $status = Password::reset(
            ['email' => $email, 'password' => $password, 'password_confirmation' => $password, 'token' => $token],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password
                ])->save();
            }
        );

        throw_if(
            $status !== Password::PASSWORD_RESET,
            new Exception('Password reset failed.')
        );
    }
}
