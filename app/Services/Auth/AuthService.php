<?php

namespace App\Services\Auth;

use App\Interfaces\AuthInterface;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class AuthService implements AuthInterface
{
    /**
     * @throws Throwable
     */
    public function createAccount(array $data, string $role): User
    {
        return DB::transaction(function () use ($data, $role) {
            $user = User::create($data);

            $user->assignRole($role);

            return $user;
        });
    }

    /**
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
    }

    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
