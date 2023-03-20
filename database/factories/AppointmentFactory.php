<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Traits\ReferenceTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    use ReferenceTrait;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => $this->appointmentReference(),
            'datetime' => $this->faker->dateTimeBetween('now', '+1 month'),
            'price' => $this->faker->randomFloat(2, 100, 1000),
            'reason' => $this->faker->sentence,
            'symptoms' => $this->faker->sentence,
            'address' => $this->faker->address,
            'diagnosis' => $this->faker->sentence,
            'prescription' => $this->faker->sentence,
            'comment' => $this->faker->sentence,
        ];
    }

    public function pending(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }

    public function confirmed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'confirmed',
            ];
        });
    }

    public function cancelled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
            ];
        });
    }

    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
            ];
        });
    }

    public function configure()
    {
        return $this->afterCreating(function (Appointment $appointment) {
            if ($appointment->status === 'confirmed') {
                $appointment->transaction()->create([
                    'reference' => $this->transactionReference(),
                    'amount' => $appointment->price,
                    'status' => 'success',
                    'gateway' => $this->faker->randomElement(['paystack', 'flutterwave']),
                    'user_id' => $appointment->user_id,
                    'description' => 'Appointment Payment for '.$appointment->reference,
                    'type' => 'debit',
                ]);
            }
        });
    }
}
