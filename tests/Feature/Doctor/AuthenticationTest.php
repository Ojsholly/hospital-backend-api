<?php

namespace Tests\Feature\Doctor;

use App\Enums\RoleEnum;
use App\Models\Doctor;
use Database\Seeders\DoctorSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthenticationTest extends TestCase
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

    public function test_doctor_login_fields_are_properly_validated()
    {
        $this->postJson(route('login'))->assertUnprocessable()->assertJsonValidationErrors([
            'email', 'password', 'role',
        ])->assertJsonStructure([
            'message', 'errors' => [
                'email', 'password', 'role',
            ],
        ]);
    }

    public function test_doctor_cannot_login_with_invalid_credentials()
    {
        $this->postJson(route('login'), [
            'email' => fake()->email,
            'password' => fake()->password,
            'role' => RoleEnum::DOCTOR,
        ])->assertUnauthorized()->assertJsonStructure([
            'status', 'message',
        ]);
    }

    public function test_doctor_cannot_login_with_invalid_role()
    {
        $doctor = Doctor::factory()->create();

        $this->postJson(route('login'), [
            'email' => $doctor->user->email,
            'password' => 'password',
            'role' => [RoleEnum::PATIENT, RoleEnum::ADMIN][mt_rand(0, 1)],
        ])->assertUnauthorized()->assertJsonStructure([
            'status', 'message',
        ]);
    }

    public function test_doctor_cannot_login_with_unverified_email()
    {
        $doctor = Doctor::factory()->create();

        $doctor->user->forceFill([
            'email_verified_at' => null,
        ])->save();

        Mail::fake();

        $this->postJson(route('login'), [
            'email' => $doctor->user->email,
            'password' => 'password',
            'role' => RoleEnum::DOCTOR,
        ])->assertForbidden()->assertJsonStructure([
            'status', 'message',
        ]);
    }

    public function test_doctor_can_login_successfully()
    {
        $doctor = Doctor::factory()->create();

        $this->postJson(route('login'), [
            'email' => $doctor->user->email,
            'password' => 'password',
            'role' => RoleEnum::DOCTOR,
        ])->assertOk()->assertJsonStructure([
            'status', 'message', 'data' => [
                'token', 'doctor' => [
                    'id', 'first_name', 'last_name', 'email', 'phone', 'gender', 'date_of_birth', 'profile_picture', 'specialty', 'medical_license_number', 'medical_school',
                    'year_of_graduation', 'biography', 'address', 'consultation_fee', 'created_at', 'updated_at', 'deleted_at',
                ],
            ],
        ]);
    }
}
