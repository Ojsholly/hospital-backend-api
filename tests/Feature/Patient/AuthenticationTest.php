<?php

namespace Tests\Feature\Patient;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;
use Auth;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_patient_registration_fields_are_successfully_validated()
    {
        $this->postJson(route('patients.store'))->assertUnprocessable()->assertJsonValidationErrors([
            'first_name', 'last_name', 'email', 'phone', 'password', 'profile_picture',
        ])->assertJsonStructure([
            'message', 'errors' => [
                'first_name', 'last_name', 'email', 'phone', 'password', 'profile_picture',
            ],
        ]);
    }

    public function test_patient_registration_is_successful()
    {
        $patientCount = User::whereHas('roles', function ($query) {
            $query->where('name', RoleEnum::PATIENT);
        })->count();

        $password = Str::random(16);

        $data = [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->email(),
            'phone' => '0802'.mt_rand(1000000, 9999999),
            'password' => $password,
            'password_confirmation' => $password,
            'profile_picture' => UploadedFile::fake()->image('profile_picture.jpg', 100, 100),
        ];

        Mail::fake();

        $this->postJson(route('patients.store'), $data)->assertCreated()->assertJsonStructure([
            'message', 'data' => [
                'id', 'first_name', 'last_name', 'email', 'phone', 'profile_picture', 'created_at', 'updated_at',
            ],
        ]);

        $this->assertDatabaseCount('users', $patientCount + 1);
        $this->assertDatabaseHas('users', [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'email_verified_at' => null,
        ]);

        $role = Role::where('name', RoleEnum::PATIENT)->first();

        $this->assertDatabaseHas('role_user', [
            'user_id' => User::where('email', $data['email'])->first()->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_user_can_verify_their_emails_successfully()
    {
        // Create a new user with an unverified email address
        $user = User::factory()->create(['email_verified_at' => null]);

        // Generate the verification URL for the user
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        // Send the verification email to the user
        Mail::fake();
        Auth::login($user);
        $user->sendEmailVerificationNotification();

        // Assert that the verification email was sent
//        Mail::assertSent(VerifyEmail::class);

        // Visit the verification URL and assert that the user's email is verified
        $this->get($verificationUrl)
            ->assertOk()
            ->assertViewIs('auth.verify-email')
            ->assertViewHas('status', 'success')
            ->assertViewHas('message', 'Email address verified successfully. You can now close this window.');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
