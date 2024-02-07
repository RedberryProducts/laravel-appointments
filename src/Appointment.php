<?php

namespace RedberryProducts\Appointment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Appointment
{
    public Model $scheduleable;

    public ?Model $appointable = null;

    public Carbon $at;

    public ?string $title = null;

    public Models\Appointment $dbRecord;

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

    public function schedule(Carbon $at, ?string $title): static
    {
        $this->at = $at;
        $this->title = $title;
        $this->save();

        return $this;
    }

    private function save(): Models\Appointment
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

        return $appointment;
    }
}
