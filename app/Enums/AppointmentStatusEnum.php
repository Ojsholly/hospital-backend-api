<?php

namespace App\Enums;

enum AppointmentStatusEnum: string
{
    const PENDING = 'pending';

    const CONFIRMED = 'confirmed';

    const CANCELLED = 'cancelled';

    const COMPLETED = 'completed';
}
