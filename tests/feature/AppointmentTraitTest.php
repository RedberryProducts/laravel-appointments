<?php

use Illuminate\Support\Carbon;
use RedberryProducts\Appointment\Models\AppointableTimeSetting;
use RedberryProducts\Appointment\Tests\Models\User;
use Spatie\OpeningHours\OpeningHours;
use Spatie\OpeningHours\OpeningHoursForDay;

beforeEach(function () {
    $this->doctor = User::factory()->doctor()->create();
    $this->patient = User::factory()->patient()->create();
});

describe('Test package functionalities using model traits', function () {

    it('can test basic model relations', function () {

        $appointment = \RedberryProducts\Appointment\Models\Appointment::factory()
            ->appointable($this->doctor)
            ->scheduleable($this->patient)
            ->create();

        expect($appointment->appointable)->toBeInstanceOf(User::class)
            ->and($appointment->appointable->type)->toBe('doctor')
            ->and($appointment->scheduleable)->toBeInstanceOf(User::class)
            ->and($appointment->scheduleable->type)->toBe('patient');
    });

    it('can schedule an appointment using model traits', function () {

        $appointmentTitle = 'Consultation with Dr. John Doe';
        $appointmentAt = Carbon::make('2024-04-08 12:00:00')->toDateTime();
        /** @var \RedberryProducts\Appointment\Appointment $appointment */
        $appointment = $this->patient->scheduleAppointment(
            with: $this->doctor,
            at: $appointmentAt,
            title: $appointmentTitle // optional
        );

        //        dd($appointment->startsAt(), $appointmentAt);
        //        dd($appointment->databaseRecord()->starts_at, $appointmentAt);
        expect($appointment->databaseRecord())->toBeInstanceOf(\RedberryProducts\Appointment\Models\Appointment::class)
            ->and($appointment->appointable()->type)->toBe('doctor')
            ->and($appointment->scheduleable()->type)->toBe('patient')
            ->and($appointment->startsAt()->getTimestamp())->toBe($appointmentAt->getTimestamp())
            ->and($appointment->title())->toBe($appointmentTitle);
    });

    it('can set opening hour of appointables via trait', function () {
        $this->doctor->setWorkingHours(
            [
                'monday' => ['09:00-12:00'],
                'tuesday' => ['09:00-12:00', '13:00-18:00'],
                'wednesday' => ['09:00-12:00'],
                'thursday' => ['09:00-12:00', '13:00-18:00'],
                'friday' => ['09:00-12:00', '13:00-20:00'],
                'saturday' => ['09:00-12:00', '13:00-16:00'],
                'sunday' => [],
                'exceptions' => [],
            ]
        );

        expect($this->doctor->timeSetting)->toBeInstanceOf(AppointableTimeSetting::class)
            ->and($this->doctor->timesetting->opening_hours)->toBeArray();
    });
    it('can get working hours of appointable', function () {
        $this->doctor->setWorkingHours(
            [
                'monday' => ['09:00-12:00'],
                'tuesday' => ['09:00-12:00', '13:00-18:00'],
                'wednesday' => ['09:00-12:00'],
                'thursday' => ['09:00-12:00', '13:00-18:00'],
                'friday' => ['09:00-12:00', '13:00-20:00'],
                'saturday' => ['09:00-12:00', '13:00-16:00'],
                'sunday' => [],
                'exceptions' => [],
            ]
        );

        expect($this->doctor->workingHours())->toBeInstanceOf(OpeningHours::class)
            ->and($this->doctor->workingHours()->forDay('monday'))->toBeInstanceOf(OpeningHoursForDay::class);
    });

    it('can update working hours of appointable', function () {

        $this->doctor->setWorkingHours(
            [
                'monday' => ['09:00-12:00'],
                'tuesday' => ['09:00-12:00', '13:00-18:00'],
                'wednesday' => ['09:00-12:00'],
                'thursday' => ['09:00-12:00', '13:00-18:00'],
                'friday' => ['09:00-12:00', '13:00-20:00'],
                'saturday' => ['09:00-12:00', '13:00-16:00'],
                'sunday' => [],
                'exceptions' => [],
            ]
        );

        $this->doctor->updateWorkingHours([
            'monday' => ['12:00-14:00'],
            'tuesday' => ['09:00-12:00', '13:00-18:00'],
            'wednesday' => ['09:00-12:00'],
            'thursday' => ['09:00-12:00', '13:00-18:00'],
            'friday' => ['09:00-12:00', '13:00-20:00'],
            'saturday' => ['09:00-12:00', '13:00-16:00'],
            'sunday' => [],
            'exceptions' => [],
        ]);

        expect($this->doctor->workingHours())->toBeInstanceOf(OpeningHours::class)
            ->and($this->doctor->workingHours()->forDay('monday'))->toBeInstanceOf(OpeningHoursForDay::class)
            ->and($this->doctor->workingHours()->forDay('monday')[0]->start()->format('H:i'))->toBe('12:00');
    });

    it('can find and cancel an appointment using trait', function () {
        $appointment = $this->patient->scheduleAppointment(
            with: $this->doctor,
            at: Carbon::make('2024-04-08 12:00:00')->toDateTime(),
            title: 'Consultation with Dr. John Doe'
        );
        $appointment = $this->patient->findSchedule($appointment->databaseRecord()->id)->cancel();

        expect($appointment->status())
            ->toBe(\RedberryProducts\Appointment\Enums\Status::CANCELED->value)
            ->and($appointment->databaseRecord()->status)->toBe(\RedberryProducts\Appointment\Enums\Status::CANCELED->value);
    });

    it('can complete an appointment using trait', function () {
        $appointment = $this->patient->scheduleAppointment(
            with: $this->doctor,
            at: Carbon::make('2024-04-08 12:00:00')->toDateTime(),
            title: 'Consultation with Dr. John Doe'
        );

        $appointment->complete();

        expect($appointment->databaseRecord()->updated_at)->toBeInstanceOf(Carbon::class)
            ->and($appointment->status())->toBe(\RedberryProducts\Appointment\Enums\Status::COMPLETED->value);
    });

    it('can reschedule an appointment using trait', function () {
        $appointment = $this->patient->scheduleAppointment(
            with: $this->doctor,
            at: Carbon::make('2024-04-08 12:00:00')->toDateTime(),
            title: 'Consultation with Dr. John Doe'
        );

        $appointment->reschedule(Carbon::make('2024-04-08 14:00:00')->toDateTime());

        expect($appointment->startsAt()->getTimestamp())->toBe(Carbon::make('2024-04-08 14:00:00')->toDateTime()->getTimestamp());
    });

    it('can create, find and cancel an appointment using trait', function () {
        $appointment = $this->patient->scheduleAppointment(
            with: $this->doctor,
            at: Carbon::make('2024-04-08 12:00:00')->toDateTime(),
            title: 'Consultation with Dr. John Doe'
        );
        $appointment = $this->patient->findSchedule($appointment->databaseRecord()->id)->cancel();

        expect($appointment->status())
            ->toBe(\RedberryProducts\Appointment\Enums\Status::CANCELED->value)
            ->and($appointment->databaseRecord()->status)->toBe(\RedberryProducts\Appointment\Enums\Status::CANCELED->value);
    });

    it('can create, find and reschedule an appointment using trait', function () {
        $appointment = $this->patient->scheduleAppointment(
            with: $this->doctor,
            at: Carbon::make('2024-04-08 12:00:00')->toDateTime(),
            title: 'Consultation with Dr. John Doe'
        );
        $appointment = $this->patient->findSchedule($appointment->databaseRecord()->id)->reschedule(Carbon::make('2024-04-08 14:00:00')->toDateTime());

        expect($appointment->startsAt()->getTimestamp())->toBe(Carbon::make('2024-04-08 14:00:00')->toDateTime()->getTimestamp());
    });
});
