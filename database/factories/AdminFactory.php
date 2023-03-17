<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => config('app.name'),
            'last_name' => 'Admin',
            'phone' => '0802'.str_shuffle($this->faker->randomNumber(7, true)),
            'email' => config('settings.admin-email', 'olusolaojewunmi@gmail.com'),
            'email_verified_at' => now(),
            'profile_picture' => fake()->imageUrl(300, 300),
            'password' => Hash::make(config('settings.admin-password', 'Olusola12345')),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * @return AdminFactory.\Database\Factories\AdminFactory.afterCreating
     */
    public function configure(): AdminFactory
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole(RoleEnum::ADMIN);
        });
    }
}
