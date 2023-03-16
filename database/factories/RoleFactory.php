<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    public function definition(): array
    {
        return [

        ];
    }

    public function patient(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => RoleEnum::PATIENT,
        ]);
    }

    public function doctor(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => RoleEnum::DOCTOR,
        ]);
    }

    public function admin(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => RoleEnum::ADMIN,
        ]);
    }

    public function superAdmin(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => RoleEnum::SUPER_ADMIN,
        ]);
    }
}
