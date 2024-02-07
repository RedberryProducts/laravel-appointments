<?php

namespace RedberryProducts\Appointment\Models\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use RedberryProducts\Appointment\Models\Appointment;

trait HasAppointments
{
    public function appointments(): MorphMany
    {
        return $this->morphMany(Appointment::class, 'appointable');
    }
}
