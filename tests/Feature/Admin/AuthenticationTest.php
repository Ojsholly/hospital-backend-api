<?php

namespace Tests\Feature\Admin;

use App\Enums\RoleEnum;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class, AdminSeeder::class,
        ]);
    }

    public function test_admin_login_fields_are_successfully_validated()
    {
        $this->postJson(route('login'))->assertUnprocessable()->assertJsonValidationErrors([
            'email', 'password', 'role',
        ])->assertJsonStructure([
            'message', 'errors' => [
                'email', 'password', 'role',
            ],
        ]);
    }

    public function test_patient_cannot_login_as_admin()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'password',
            'role' => RoleEnum::ADMIN,
        ])->assertUnauthorized()->assertJsonStructure([
            'status', 'message',
        ]);
    }

    public function test_admin_can_login_successfully()
    {
        $admin = User::whereHas('roles', function ($query) {
            $query->where('name', RoleEnum::ADMIN);
        })->first();

        $this->postJson(route('login'), [
            'email' => config('settings.admin-email', $admin->email),
            'password' => config('settings.admin-password', '12345678'),
            'role' => RoleEnum::ADMIN,
        ])->assertOk()->assertJsonStructure([
            'status', 'message', 'data' => [
                'token', 'user' => [
                    'id', 'first_name', 'last_name', 'email', 'phone', 'profile_picture', 'roles',
                ],
            ],
        ]);
    }
}
