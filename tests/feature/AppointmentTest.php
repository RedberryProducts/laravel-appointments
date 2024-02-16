<?php

use Illuminate\Support\Carbon;
use RedberryProducts\Appointment\Facades\Appointment;
use RedberryProducts\Appointment\Models\User;
use Spatie\OpeningHours\OpeningHours;
use Spatie\OpeningHours\OpeningHoursForDay;

beforeEach(function () {
    $this->doctor = User::factory()->doctor()->create();
    $this->patient = User::factory()->patient()->create();
});

describe('Test package functionalities using facade', function () {

    it('can schedule a appointment', function () {
        $at = Carbon::now()->addHour();
        $appointment = \RedberryProducts\Appointment\Facades\Appointment::with($this->doctor)
            ->for($this->patient)
            ->schedule($at, 'Consultation with Dr. John Doe');

        expect($appointment)->toBeInstanceOf(\RedberryProducts\Appointment\Appointment::class)
            ->and($appointment->appointable->type)->toBe('doctor')
            ->and($appointment->scheduleable->type)->toBe('patient')
            ->and($appointment->at->toString())->toBe($at->toString())
            ->and($appointment->title)->toBe('Consultation with Dr. John Doe');
    });

    it('can schedule an appointment without appointable', function () {
        $at = Carbon::now()->addHour();
        $appointment = \RedberryProducts\Appointment\Facades\Appointment::for($this->patient)
            ->schedule($at, 'Appointment without appointable');

        expect($appointment)->toBeInstanceOf(\RedberryProducts\Appointment\Appointment::class)
            ->and($appointment->scheduleable->type)->toBe('patient')
            ->and($appointment->appointable)->toBeNull()
            ->and($appointment->at->toString())->toBe($at->toString())
            ->and($appointment->title)->toBe('Appointment without appointable');
    });

    it('can set opening hours', function () {
        Appointment::with($this->doctor)->setWorkingHours([
            'monday' => ['09:00-12:00'],
            'tuesday' => ['09:00-12:00', '13:00-18:00'],
            'wednesday' => ['09:00-12:00'],
            'thursday' => ['09:00-12:00', '13:00-18:00'],
            'friday' => ['09:00-12:00', '13:00-20:00'],
            'saturday' => ['09:00-12:00', '13:00-16:00'],
            'sunday' => [],
            'exceptions' => [],
        ]);

        expect($this->doctor->workingHours())
            ->toBeInstanceOf(OpeningHours::class)
            ->and($this->doctor->workingHours()->forDay('monday'))
            ->toBeInstanceOf(OpeningHoursForDay::class);
    });

    it('can set opening hours for without appointable', function () {
        $appointment = Appointment::setWorkingHours([
            'monday' => ['09:00-12:00'],
            'tuesday' => ['09:00-12:00', '13:00-18:00'],
            'wednesday' => ['09:00-12:00'],
            'thursday' => ['09:00-12:00', '13:00-18:00'],
            'friday' => ['09:00-12:00', '13:00-20:00'],
            'saturday' => ['09:00-12:00', '13:00-16:00'],
            'sunday' => [],
            'exceptions' => [],
        ]);

        expect($appointment->workingHours)->toBeInstanceOf(OpeningHours::class);
    });

    it('can use working hour timeslots', function () {
        $appointment = Appointment::with($this->doctor)->setWorkingHours([
            'monday' => ['11:00-12:00', '12:00-13:00', '13:00-14:00', '16:00-17:00'],
        ]);
        $appointableOpeningHours = $appointment->workingHours;

        expect($appointableOpeningHours->isOpenAt(new DateTime('2024-04-08 12:00:00')))->toBeTrue()
            ->and($appointableOpeningHours->isOpenAt(new DateTime('2024-04-08 13:00:00')))->toBeTrue()
            ->and($appointableOpeningHours->isOpenAt(new DateTime('2024-04-08 14:00:00')))->toBeFalse()
            ->and($appointableOpeningHours->isOpenAt(new DateTime('2024-04-08 15:00:00')))->toBeFalse()
            ->and($appointableOpeningHours->isOpenAt(new DateTime('2024-04-08 16:00:00')))->toBeTrue()
            ->and($appointableOpeningHours->isOpenAt(new DateTime('2024-04-08 17:00:00')))->toBeFalse();
    });

    it('can schedule appointment on an available timeslot', function () {
        Appointment::with($this->doctor)->setWorkingHours([
            'monday' => ['11:00-12:00', '12:00-13:00', '13:00-14:00', '16:00-17:00'],
        ]);

        $appointment = $this->patient->scheduleAppointment(
            with: $this->doctor,
            at: Carbon::make('2024-04-08 12:00:00'),
            title: 'Consultation with Dr. John Doe'
        );

        expect($appointment->dbRecord)->toBeInstanceOf(\RedberryProducts\Appointment\Models\Appointment::class)
            ->and($appointment->dbRecord->appointable->type)->toBe('doctor')
            ->and($appointment->dbRecord->scheduleable->type)->toBe('patient')
            ->and($appointment->dbRecord->starts_at->toString())->toBe(Carbon::make('2024-04-08 12:00:00')->toString())
            ->and($appointment->dbRecord->title)->toBe('Consultation with Dr. John Doe');
    });

    it('can not schedule appointment on an invalid timeslot', function () {
        Appointment::with($this->doctor)->setWorkingHours([
            'monday' => ['11:00-12:00', '12:00-13:00', '13:00-14:00', '16:00-17:00'],
        ]);

        $this->patient->scheduleAppointment(
            with: $this->doctor,
            at: Carbon::make('2024-04-08 15:00:00'),
            title: 'Consultation with Dr. John Doe'
        );
    })->throws(\Exception::class, 'The appointable is not available at the given time');
});
