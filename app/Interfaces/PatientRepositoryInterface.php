<?php

namespace App\Interfaces;

use App\Models\User;

interface PatientRepositoryInterface
{
    public function create(array $data): User;
}
