<?php

namespace App\Enums;

enum RoleEnum: string
{
    public const PATIENT = 'patient';

    public const DOCTOR = 'doctor';

    public const ADMIN = 'admin';

    public const SUPER_ADMIN = 'super-admin';

    public static function getAllRoles(): array
    {
        return [
            self::PATIENT,
            self::DOCTOR,
            self::ADMIN,
            self::SUPER_ADMIN,
        ];
    }
}
