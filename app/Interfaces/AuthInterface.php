<?php

namespace App\Interfaces;

use App\Models\User;

interface AuthInterface
{
    public function createAccount(array $data, string $role): User;

    public function verifyEmail(string $id): void;
}