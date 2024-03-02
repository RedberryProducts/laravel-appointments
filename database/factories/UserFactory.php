<?php

namespace RedberryProducts\Appointment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RedberryProducts\Appointment\Tests\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'type' => $this->faker->randomElement(['doctor', 'patient']),
        ];
    }

    public function doctor(): UserFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'doctor',
            ];
        });
    }

    public function patient(): UserFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'patient',
            ];
        });
    }
}
