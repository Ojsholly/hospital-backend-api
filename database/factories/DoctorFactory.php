<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();

        $user->removeRole(RoleEnum::PATIENT);
        $user->assignRole(RoleEnum::DOCTOR);

        return [
            'user_id' => $user->id,
            'specialty' => fake()->jobTitle,
            'medical_license_number' => Str::random(32),
            'medical_school' => fake()->company,
            'year_of_graduation' => fake()->year,
            'biography' => fake()->paragraph,
            'address' => fake()->address,
            'consultation_fee' => fake()->randomFloat(2, 1000, 10000),
        ];
    }
}
