<?php

namespace RedberryProducts\Appointment\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use RedberryProducts\Appointment\Facades\Appointment as AppointmentFacade;
use RedberryProducts\Appointment\Models\AppointableTimeSetting;
use RedberryProducts\Appointment\Models\Appointment;
use Spatie\OpeningHours\OpeningHours;
use Spatie\OpeningHours\OpeningHoursForDay;

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

    public function updateWorkingHours(array $openingHours): void
    {
        AppointmentFacade::with($this)->updateWorkingHours($openingHours);
    }

    public function workingHours(): ?OpeningHours
    {
        return AppointmentFacade::with($this)->workingHours();
    }

    public function getBookedAppointments(\DateTime $date): Collection
    {
        return $this->appointments()
            ->pending()
            ->where('starts_at', 'like', $date->format('Y-m-d') . '%')
            ->get();
    }

    public function getBookedTimeSlots(\DateTime $date): OpeningHoursForDay
    {
        $bookedTimes = $this->getBookedAppointments($date)->pluck('starts_at')
            ->map(fn($date) => $date->format('H:i'));

        $workingHours = $this->workingHours()?->forDate($date);

        foreach ($workingHours->getIterator() as $offset => $openingHour) {
            if (!$bookedTimes->contains($openingHour->start()->format('H:i'))) {
                $workingHours->offsetUnset($offset);
            }
        }

        return $workingHours;
    }

    public function getFreeTimeSlots(\DateTime $date): OpeningHoursForDay
    {
        $bookedTimes = $this->getBookedAppointments($date)->pluck('starts_at')
            ->map(fn($date) => $date->format('H:i'));

        $workingHours = $this->workingHours()?->forDate($date);

        foreach ($workingHours->getIterator() as $offset => $openingHour) {
            if ($bookedTimes->contains($openingHour->start()->format('H:i'))) {
                $workingHours->offsetUnset($offset);
            }
        }

        return $workingHours;
    }
}
