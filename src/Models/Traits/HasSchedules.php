<?php

namespace RedberryProducts\Appointment\Models\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use RedberryProducts\Appointment\Facades\Appointment as AppointmentFacade;
use RedberryProducts\Appointment\Models\Appointment;

trait HasSchedules
{
    public function schedules(): MorphMany
    {
        return $this->morphMany(Appointment::class, 'scheduleable');
    }

    public function scheduleAppointment(mixed $with, Carbon $at, ?string $title = null): \RedberryProducts\Appointment\Appointment
    {
        return AppointmentFacade::with($with)
            ->for($this)
            ->schedule($at, $title);
    }
}
