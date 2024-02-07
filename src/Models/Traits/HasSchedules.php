<?php

namespace RedberryProducts\Appointment\Models\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use RedberryProducts\Appointment\Models\Appointment;

trait HasSchedules
{
    public function schedules(): MorphMany
    {
        return $this->morphMany(Appointment::class, 'scheduleable');
    }

    public function scheduleAppointment(mixed $with, Carbon $at, ?string $title = null): Appointment
    {
        return $this->schedules()->create([
            'starts_at' => $at,
            'status' => 'pending', //TODO: change to enum
            'title' => $title,
        ])->appointable()->associate($with);
    }
}
