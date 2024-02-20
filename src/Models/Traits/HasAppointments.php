<?php

namespace RedberryProducts\Appointment\Models\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use RedberryProducts\Appointment\Facades\Appointment as AppointmentFacade;
use RedberryProducts\Appointment\Models\AppointableTimeSetting;
use RedberryProducts\Appointment\Models\Appointment;
use Spatie\OpeningHours\OpeningHours;

trait HasAppointments
{
    public function appointments(): MorphMany
    {
        return $this->morphMany(Appointment::class, 'appointable');
    }

    public function timeSetting(): MorphOne
    {
        return $this->morphOne(AppointableTimeSetting::class, 'appointable');
    }

    public function setWorkingHours(array $openingHours): void
    {
        AppointmentFacade::with($this)->setWorkingHours($openingHours);
    }

    public function workingHours(): ?OpeningHours
    {
        return AppointmentFacade::with($this)->workingHours();
    }
}
