<?php

namespace Tests\Feature\Patient;

use App\Enums\AppointmentStatusEnum;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Database\Seeders\DoctorSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            DoctorSeeder::class,
        ]);
    }

    public function user(array $attributes = [])
    {
        return User::factory()->create($attributes);
    }

    public function doctor(array $attributes = [])
    {
        return Doctor::factory()->create($attributes);
    }

    public function test_appointment_booking_fields_validated_properly()
    {
        $user = $this->user([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->postJson(route('patients.appointments.store', ['patient' => $user]), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'doctor_id', 'datetime', 'reason', 'symptoms', 'address', 'payment_gateway',
            ])
            ->assertJsonStructure([
                'message', 'errors' => [
                    'doctor_id', 'datetime', 'reason', 'symptoms', 'address', 'payment_gateway',
                ],
            ]);
    }

    public function test_patient_can_book_appointment_with_paystack()
    {
        $user = $this->user([
            'email_verified_at' => now(),
        ]);

        $doctor = $this->doctor();

        $appointmentCount = Appointment::count();

        $data = [
            'doctor_id' => $doctor->id,
            'datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'reason' => $this->faker->sentence,
            'symptoms' => $this->faker->sentence,
            'address' => $this->faker->address,
            'payment_gateway' => 'paystack',
        ];

        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'message' => 'Authorization URL created',
                'data' => [
                    'authorization_url' => 'https://paystack.com/redirect',
                    'access_code' => 'access_code',
                    'reference' => 'reference',
                ],
            ], 200),
        ]);

        $this->actingAs($user)->postJson(route('patients.appointments.store', ['patient' => $user]), $data)
            ->assertCreated()
            ->assertJsonStructure([
                'status', 'message', 'data' => [
                    'payment' => [
                        'payment_url', 'reference', 'access_code',
                    ],
                    'appointment' => [
                        'id', 'reference', 'user', 'datetime', 'reason', 'symptoms', 'diagnosis', 'prescription', 'comment', 'address', 'price',
                        'status', 'created_at', 'updated_at',
                    ],
                ],
            ]);

        $this->assertEquals($appointmentCount + 1, Appointment::count());

        $this->assertDatabaseHas('appointments', [
            ['reference', '!=', null],
            'user_id' => $user->id,
            'doctor_id' => $doctor->id,
            'datetime' => $data['datetime'],
            'reason' => $data['reason'],
            'symptoms' => $data['symptoms'],
            'address' => $data['address'],
            'status' => AppointmentStatusEnum::PENDING,
        ]);
    }

    public function test_patient_can_book_appointment_with_flutterwave()
    {
        $user = $this->user([
            'email_verified_at' => now(),
        ]);

        $doctor = $this->doctor();

        $appointmentCount = Appointment::count();

        $data = [
            'doctor_id' => $doctor->id,
            'datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'reason' => $this->faker->sentence,
            'symptoms' => $this->faker->sentence,
            'address' => $this->faker->address,
            'payment_gateway' => 'flutterwave',
        ];

        Http::fake([
            'https://api.flutterwave.com/v3/payments' => Http::response([
                'status' => 'success',
                'message' => 'Authorization URL created',
                'data' => [
                    'link' => 'https://flutterwave.com/redirect',
                    'tx_ref' => 'tx_ref',
                    'flw_ref' => 'flw_ref',
                ],
            ], 200),
        ]);

        $this->actingAs($user)->postJson(route('patients.appointments.store', ['patient' => $user]), $data)
            ->assertCreated()
            ->assertJsonStructure([
                'status', 'message', 'data' => [
                    'payment' => [
                        'payment_url', 'reference', 'access_code',
                    ],
                    'appointment' => [
                        'id', 'reference', 'user', 'datetime', 'reason', 'symptoms', 'diagnosis', 'prescription', 'comment', 'address', 'price',
                        'status', 'created_at', 'updated_at',
                    ],
                ],
            ]);

        $this->assertEquals($appointmentCount + 1, Appointment::count());

        $this->assertDatabaseHas('appointments', [
            ['reference', '!=', null],
            'user_id' => $user->id,
            'doctor_id' => $doctor->id,
            'datetime' => $data['datetime'],
            'reason' => $data['reason'],
            'symptoms' => $data['symptoms'],
            'address' => $data['address'],
            'status' => AppointmentStatusEnum::PENDING,
        ]);
    }
}
