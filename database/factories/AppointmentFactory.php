<?php

namespace RedberryProducts\Appointment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RedberryProducts\Appointment\Models\Appointment;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition()
    {
        $startsAt = $this->faker->dateTimeBetween('+1 day', '+1 week');
        $endsAt = (clone $startsAt)->modify('+1 hour'); // Ensure the appointment ends after it starts

        return [
            'title' => $this->faker->sentence,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'canceled']), // Assuming these are your possible statuses
            'type' => $this->faker->randomElement(['default', 'urgent', 'follow-up']), // Assuming you have different types of appointments
        ];

    }

    /**
     * Assign a dynamic appointable entity.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function appointable($model)
    {
        return $this->state(function (array $attributes) use ($model) {
            return [
                'appointable_id' => $model->id,
                'appointable_type' => get_class($model),
            ];
        });
    }

    /**
     * Assign a dynamic scheduleable entity.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function scheduleable($model)
    {
        return $this->state(function (array $attributes) use ($model) {
            return [
                'scheduleable_id' => $model->id,
                'scheduleable_type' => get_class($model),
            ];
        });
    }
}
