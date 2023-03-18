<?php

namespace Tests\Feature\Admin;

use App\Enums\RoleEnum;
use App\Models\Doctor;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class DoctorManagementTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            AdminSeeder::class,
        ]);
    }

    public function admin(): User|null
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', RoleEnum::ADMIN);
        })->first();
    }

    public function test_patient_cannot_create_doctor_account()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('admin.doctors.store'))
                ->assertForbidden()
                ->assertJsonStructure([
                    'status', 'message',
                ]);
    }

    public function test_doctor_account_fields_are_properly_validated()
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson(route('admin.doctors.store'))
                ->assertUnprocessable()
                ->assertJsonValidationErrors([
                    'first_name', 'last_name', 'email', 'phone', 'profile_picture', 'gender', 'date_of_birth', 'specialty', 'medical_license_number', 'medical_school',
                    'year_of_graduation', 'biography', 'address', 'consultation_fee',
                ]);
    }

    public function test_admin_can_create_doctor_account_successfully()
    {
        $admin = $this->admin();

        $doctorCount = User::whereHas('roles', function ($query) {
            return $query->where('name', RoleEnum::DOCTOR);
        })->count();

        $gender = ['M', 'F'][mt_rand(0, 1)];

        $data = [
            'first_name' => fake()->firstName($gender),
            'last_name' => fake()->lastName($gender),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->phoneNumber(),
            'gender' => $gender,
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-25 years')->format('Y-m-d'),
            'profile_picture' => UploadedFile::fake()->image('profile_picture.jpeg', '300', '300'),
            'specialty' => fake()->jobTitle(),
            'medical_license_number' => Str::random(32),
            'medical_school' => fake()->company(),
            'year_of_graduation' => fake()->dateTimeBetween('-30 years', '-10 years')->format('Y'),
            'biography' => fake()->realText(),
            'address' => fake()->address(),
            'consultation_fee' => fake()->numberBetween(1000, 100000),
        ];

        $this->actingAs($admin)->postJson(route('admin.doctors.store'), $data)
                ->assertCreated()
                ->assertJsonStructure([
                    'status', 'message', 'data',
                ]);

        $this->assertEquals(Doctor::count(), $doctorCount + 1);
        $this->assertDatabaseHas('users', [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        $this->assertDatabaseHas('doctors', [
            'specialty' => $data['specialty'],
            'medical_license_number' => $data['medical_license_number'],
            'medical_school' => $data['medical_school'],
            'year_of_graduation' => $data['year_of_graduation'],
            'biography' => $data['biography'],
            'address' => $data['address'],
            'consultation_fee' => $data['consultation_fee'],
        ]);
    }
}
