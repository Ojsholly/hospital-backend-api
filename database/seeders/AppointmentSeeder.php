<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Appointment::factory()->for(User::factory())->for(Doctor::factory())->pending()->create();
        Appointment::factory()->for(User::factory())->for(Doctor::factory())->confirmed()->create();

        Appointment::factory()->for(User::factory())->for(Doctor::factory())->cancelled()->create();
        Appointment::factory()->for(User::factory())->for(Doctor::factory())->completed()->create();
    }
}
