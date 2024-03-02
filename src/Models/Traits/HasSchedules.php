<?php

namespace RedberryProducts\Appointment\Models\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use RedberryProducts\Appointment\Facades\Appointment as AppointmentFacade;
use RedberryProducts\Appointment\Models\Appointment;

trait HasSchedules
{
    public function schedules(): MorphMany
    {
        return $this->morphMany(Appointment::class, 'scheduleable');
    }

    public function scheduleAppointment(mixed $with, \DateTime $at, ?string $title = null)
    {
        return AppointmentFacade::with($with)
            ->for($this)
            ->schedule($at, $title);
    }

    public function findSchedule(int $id): AppointmentFacade|\RedberryProducts\Appointment\Appointment
    {
        return AppointmentFacade::makeFromModel($this->schedules()->find($id));
    }
}
