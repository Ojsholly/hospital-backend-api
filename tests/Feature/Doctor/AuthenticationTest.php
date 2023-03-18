<?php

namespace Tests\Feature\Doctor;

use App\Enums\RoleEnum;
use App\Models\Doctor;
use Database\Seeders\DoctorSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
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

    public function test_doctor_wallet_is_created_after_doctor_verifies_email_successfully()
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

        $this->assertDatabaseMissing('wallets', [
            'doctor_id' => $doctor->id,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $doctor->user_id, 'hash' => sha1($doctor->user->getEmailForVerification())]
        );

        Auth::login($doctor->user);
        $doctor->user->sendEmailVerificationNotification();

        // Visit the verification URL and assert that the user's email is verified
        $this->get($verificationUrl)
            ->assertOk()
            ->assertViewIs('auth.verify-email')
            ->assertViewHas('status', 'success')
            ->assertViewHas('message', 'Email address verified successfully. You can now close this window.');

        $this->assertDatabaseHas('wallets', [
            'doctor_id' => $doctor->id,
            'available_balance' => 0,
            'ledger_balance' => 0,
            'is_locked' => false,
        ]);

        $this->assertTrue($doctor->user->fresh()->hasVerifiedEmail());
    }

    public function test_doctors_can_reset_their_passwords_successfully()
    {
        $doctor = Doctor::factory()->create();

        $token = $this->app->make('auth.password.broker')->createToken($doctor->user);

        $this->postJson(route('password.update'), [
            'token' => $token,
            'email' => $doctor->user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertOk()
        ->assertViewIs('auth.reset-password')
        ->assertViewHas('status', 'success')
        ->assertViewHas('message', 'Password reset successfully. You can now close this window.');

        $this->assertTrue($doctor->user->fresh()->hasVerifiedEmail());
    }
}
