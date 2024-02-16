<?php

use Illuminate\Support\Carbon;
use RedberryProducts\Appointment\Models\AppointableTimeSetting;
use RedberryProducts\Appointment\Models\User;
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
        $appointmentAt = Carbon::make('2024-04-08 12:00:00');
        $appointment = $this->patient->scheduleAppointment(
            with: $this->doctor,
            at: $appointmentAt,
            title: $appointmentTitle // optional
        );

        expect($appointment->dbRecord)->toBeInstanceOf(\RedberryProducts\Appointment\Models\Appointment::class)
            ->and($appointment->dbRecord->appointable->type)->toBe('doctor')
            ->and($appointment->dbRecord->scheduleable->type)->toBe('patient')
            ->and($appointment->dbRecord->starts_at)->toBe($appointmentAt)
            ->and($appointment->dbRecord->title)->toBe($appointmentTitle);
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
    it('can use get working hours of appointable', function () {
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
});
