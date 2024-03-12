<?php

namespace RedberryProducts\Appointment;

use Illuminate\Support\Carbon;
use RedberryProducts\Appointment\Enums\Status;
use RedberryProducts\Appointment\Events\AppointmentCanceled;
use RedberryProducts\Appointment\Events\AppointmentCompleted;
use RedberryProducts\Appointment\Events\AppointmentRescheduled;
use RedberryProducts\Appointment\Events\AppointmentScheduled;
use RedberryProducts\Appointment\Exceptions\AppointmentAlreadyCanceledException;
use RedberryProducts\Appointment\Exceptions\AppointmentAlreadyCompletedException;
use RedberryProducts\Appointment\Exceptions\UnavailableAppointmentTimeException;
use RedberryProducts\Appointment\Models\AppointableTimeSetting;
use Spatie\OpeningHours\OpeningHours;

class Appointment
{
    private mixed $scheduleable;

    private mixed $appointable = null;

    private \DateTime $at;

    private ?string $title = null;

    private Status $status = Status::PENDING;

    private bool $ignoreTimeSetting = true;

    private ?Models\Appointment $databaseRecord = null;

    private ?OpeningHours $workingHours = null;

    public function makeFromModel(Models\Appointment $model): static
    {
        $this->databaseRecord = $model;
        $this->at = Carbon::make($model->starts_at)->toDateTime();
        $this->title = $model->title;
        $this->status = Status::from($model->status);

        return $this;
    }

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
    public function schedule(\DateTime $at, ?string $title): static
    {
        if ($this->workingHours() && ! $this->ignoreTimeSetting) {

            $isOpenAt = $this->workingHours->isOpenAt($at);
            if (! $isOpenAt) {
                throw new UnavailableAppointmentTimeException();
            }
        }
        $this->at = $at;
        $this->title = $title;
        $this->create();
        AppointmentScheduled::dispatch($this);

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

    public function updateWorkingHours(array $openingHours): static
    {
        $appointableTimeSetting = $this->appointable->timeSetting;
        $appointableTimeSetting->update(['opening_hours' => $openingHours]);
        $this->workingHours = OpeningHours::create($appointableTimeSetting->opening_hours);

        return $this;
    }

    private function create(): void
    {
        $appointment = new Models\Appointment([
            'starts_at' => $this->at,
            'status' => $this->status->value,
            'title' => $this->title,
        ]);
        $appointment->appointable()->associate($this->appointable);
        $appointment->scheduleable()->associate($this->scheduleable);
        $appointment->save();
        $this->databaseRecord = $appointment;
    }

    /**
     * @throws AppointmentAlreadyCompletedException
     */
    public function cancel(): static
    {
        if ($this->status() === Status::COMPLETED->value) {
            throw new AppointmentAlreadyCompletedException();
        }
        $this->databaseRecord->cancel();
        AppointmentCanceled::dispatch($this);

        return $this;
    }

    /**
     * @throws AppointmentAlreadyCanceledException
     */
    public function complete(): static
    {
        if ($this->status() === Status::CANCELED->value) {
            throw new AppointmentAlreadyCanceledException();
        }
        $this->databaseRecord->complete();
        AppointmentCompleted::dispatch($this);

        return $this;
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

    public function databaseRecord(): ?Models\Appointment
    {
        return $this->databaseRecord;
    }

    public function appointable(): mixed
    {
        return $this->databaseRecord()->appointable;
    }

    public function scheduleable(): mixed
    {
        return $this->databaseRecord()->scheduleable;
    }

    public function startsAt(): \DateTime
    {
        return $this->databaseRecord()->starts_at->toDateTime();
    }

    public function endsAt(): \DateTime
    {
        return $this->databaseRecord()->ends_at->toDateTime();
    }

    public function title(): string
    {
        return $this->databaseRecord()->title;
    }

    public function status(): string
    {
        return $this->databaseRecord()->status;
    }

    public function type(): string
    {
        return $this->databaseRecord()->type;
    }

    public function id(): int
    {
        return $this->databaseRecord()->id;
    }

    public function findSchedule(int $id): static
    {
        $this->makeFromModel(Models\Appointment::find($id));

        return $this;
    }

    /**
     * @throws AppointmentAlreadyCompletedException
     * @throws AppointmentAlreadyCanceledException
     */
    public function reschedule(\DateTime $at): static
    {
        if ($this->status() === Status::COMPLETED->value) {
            throw new AppointmentAlreadyCompletedException();
        } elseif ($this->status() === Status::CANCELED->value) {
            throw new AppointmentAlreadyCanceledException();
        }
        $this->databaseRecord?->update(['starts_at' => $at]);
        AppointmentRescheduled::dispatch($this);

        return $this;
    }
}
