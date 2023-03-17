<?php

namespace App\Services\Auth;

use App\Interfaces\AuthInterface;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
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

    public function verifyEmail(string $id): void
    {
        $user = User::findOrFail($id);

        $user->markEmailAsVerified();
    }
}
