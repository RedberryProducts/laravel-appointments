<?php

namespace RedberryProducts\Appointment;

use Illuminate\Support\Carbon;
use RedberryProducts\Appointment\Models\AppointableTimeSetting;
use Spatie\OpeningHours\OpeningHours;

class Appointment
{
    public mixed $scheduleable;

    public mixed $appointable = null;

    public Carbon $at;

    public ?string $title = null;

    public bool $ignoreTimeSetting = true;

    public Models\Appointment $dbRecord;

    public ?OpeningHours $workingHours = null;

    public function with(mixed $with): static
    {
        $this->appointable = $with;

        return $this;
    }

    public function for(mixed $for): static
    {
        $this->scheduleable = $for;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function schedule(Carbon $at, ?string $title): static
    {
        if ($this->workingHours() && ! $this->ignoreTimeSetting) {

            $isOpenAt = $this->workingHours->isOpenAt($at->toDateTime());
            if (! $isOpenAt) {
                throw new \Exception('The appointable is not available at the given time');
            }
        }
        $this->at = $at;
        $this->title = $title;
        $this->save();

        return $this;
    }

    public function setWorkingHours(array $openingHours): static
    {
        $appointableTimeSetting = new Models\AppointableTimeSetting([
            'opening_hours' => $openingHours,
        ]);
        $appointableTimeSetting->appointable()->associate($this->appointable);
        $appointableTimeSetting->save();

        $this->workingHours = OpeningHours::create($appointableTimeSetting->opening_hours);

        return $this;
    }

    private function save(): void
    {
        $appointment = new Models\Appointment([
            'starts_at' => $this->at,
            'status' => 'pending', //TODO: change to enum
            'title' => $this->title,
        ]);
        $appointment->appointable()->associate($this->appointable);
        $appointment->scheduleable()->associate($this->scheduleable);
        $appointment->save();
        $this->dbRecord = $appointment;
    }

    public function workingHours(): ?OpeningHours
    {
        if (! $this->appointable) {
            $timeSetting = AppointableTimeSetting::general()->first();
        } else {
            $timeSetting = $this->appointable->timeSetting;
        }
        if (! $timeSetting) {
            return null;
        }
        $this->workingHours = OpeningHours::create($timeSetting->opening_hours);
        $this->ignoreTimeSetting = false;

        return $this->workingHours;
    }
}
