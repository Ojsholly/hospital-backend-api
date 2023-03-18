<?php

namespace Tests\Feature\Patient;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;
use Auth;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
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
            'gender' => ['M', 'F'][mt_rand(0, 1)],
            'date_of_birth' => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
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

        // Visit the verification URL and assert that the user's email is verified
        $this->get($verificationUrl)
            ->assertOk()
            ->assertViewIs('auth.verify-email')
            ->assertViewHas('status', 'success')
            ->assertViewHas('message', 'Email address verified successfully. You can now close this window.');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_login_fields_are_validated_properly()
    {
        $this->postJson(route('login'))->assertUnprocessable()->assertJsonValidationErrors([
            'email', 'password', 'role',
        ])->assertJsonStructure([
            'message', 'errors' => [
                'email', 'password',
            ],
        ]);
    }

    public function test_patient_cannot_login_with_invalid_credentials()
    {
        $this->postJson(route('login'), [
            'email' => $this->faker->email(),
            'password' => Str::random(16),
            'role' => RoleEnum::PATIENT,
        ])->assertUnauthorized()->assertJsonStructure([
            'message', 'status',
        ]);
    }

    public function test_patient_cannot_login_with_unverified_email_address()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'password',
            'role' => RoleEnum::PATIENT,
        ])->assertForbidden()->assertJsonStructure([
            'message', 'status',
        ]);
    }

    public function test_patient_can_login_successfully()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'password',
            'role' => RoleEnum::PATIENT,
        ])->assertOk()->assertJsonStructure([
            'message', 'data' => [
                'token', 'user' => [
                    'id', 'first_name', 'last_name', 'email', 'phone', 'profile_picture', 'created_at', 'updated_at',
                ],
            ],
        ]);
    }
}
