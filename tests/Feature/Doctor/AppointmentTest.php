<?php

namespace Tests\Feature\Doctor;

use App\Enums\AppointmentStatusEnum;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
        ]);
    }

    public function doctor(array $attributes = [])
    {
        return Doctor::factory()->create($attributes);
    }

    public function test_doctors_can_fetch_their_appointments()
    {
        $doctor = $this->doctor();
        $user = User::factory()->create();

        $appointments = Appointment::factory(10)->pending()->create([
            'doctor_id' => $doctor->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($doctor->user)->getJson(route('doctors.appointments.index', [
            'doctor' => $doctor->id,
        ]))->assertOk()->assertJsonStructure([
            'status', 'message', 'data' => [
                'meta', 'appointments',
            ],
        ])->assertSee([
            'total' => 10,
        ]);
    }

    public function test_doctor_can_see_appointment_details()
    {
        $doctor = $this->doctor();
        $user = User::factory()->create();

        $appointment = Appointment::factory()->pending()->create([
            'doctor_id' => $doctor->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($doctor->user)->getJson(route('doctors.appointments.show', [
            'doctor' => $doctor->id,
            'appointment' => $appointment->id,
        ]))->assertOk()->assertJsonStructure([
            'status', 'message', 'data',
        ])->assertSee([
            'id' => $appointment->id,
        ]);
    }

    public function test_doctor_can_complete_appointment()
    {
        $doctor = $this->doctor();
        $user = User::factory()->create();

        $doctor->wallet()->create([
            'available_balance' => 0.00,
            'ledger_balance' => 0.00,
        ]);

        $appointment = Appointment::factory()->pending()->create([
            'doctor_id' => $doctor->id,
            'user_id' => $user->id,
        ]);

        $data = [
            'diagnosis' => $this->faker->sentence,
            'prescription' => $this->faker->sentence,
            'comment' => $this->faker->sentence,
        ];

        $this->actingAs($doctor->user)->putJson(route('doctors.appointments.update', [
            'doctor' => $doctor->id,
            'appointment' => $appointment->id,
        ]), $data)->assertOk()->assertJsonStructure([
            'status', 'message', 'data',
        ])->assertSee([
            'id' => $appointment->id,
        ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => AppointmentStatusEnum::COMPLETED,
        ]);

        $this->assertDatabaseHas('wallets', [
            'doctor_id' => $doctor->id,
            'available_balance' => $appointment->price - config('settings.service-charge'),
            'ledger_balance' => $appointment->price - config('settings.service-charge'),
        ]);
    }
}
