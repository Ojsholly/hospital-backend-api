<?php

namespace App\Services\Doctor;

use App\Enums\RoleEnum;
use App\Interfaces\DoctorInterface;
use App\Models\Doctor;
use App\Services\Auth\AuthService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Throwable;

class DoctorService implements DoctorInterface
{
    public function __construct(private AuthService $authService)
    {
    }

    /**
     * @throws Throwable
     */
    public function createDoctorAccount(array $accountData, array $profileData): Doctor
    {
        return DB::transaction(function () use ($accountData, $profileData) {
            $user = $this->authService->createAccount($accountData, RoleEnum::DOCTOR);

            $user->doctor()->create($profileData);

            return $user->doctor;
        });
    }

    /**
     * @throws Throwable
     */
    public function getDoctorById(string $id): Doctor
    {
        $doctor = Doctor::find($id);

        throw_if(
            ! $doctor,
            new ModelNotFoundException('Requested doctor account not found.')
        );

        return $doctor;
    }
}
