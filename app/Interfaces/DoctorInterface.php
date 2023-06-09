<?php

namespace App\Interfaces;

use App\Models\Doctor;

interface DoctorInterface
{
    public function createDoctorAccount(array $accountData, array $profileData): Doctor;

    public function getDoctorById(string $id): Doctor;
}
