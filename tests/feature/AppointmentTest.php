<?php

use Illuminate\Support\Carbon;
use RedberryProducts\Appointment\Exceptions\AppointmentAlreadyCanceledException;
use RedberryProducts\Appointment\Exceptions\AppointmentAlreadyCompletedException;
use RedberryProducts\Appointment\Exceptions\UnavailableAppointmentTimeException;
use RedberryProducts\Appointment\Facades\Appointment;
use RedberryProducts\Appointment\Tests\Models\User;
use Spatie\OpeningHours\OpeningHours;
use Spatie\OpeningHours\OpeningHoursForDay;

beforeEach(function () {
    $this->doctor = User::factory()->doctor()->create();
    $this->patient = User::factory()->patient()->create();
});

describe('Test package functionalities using facade', function () {

    it('can schedule a appointment', function () {
        $at = Carbon::now()->addHour()->toDateTime();
        $appointment = \RedberryProducts\Appointment\Facades\Appointment::with($this->doctor)
            ->for($this->patient)
            ->schedule($at, 'Consultation with Dr. John Doe');

        expect($appointment)->toBeInstanceOf(\RedberryProducts\Appointment\Appointment::class)
            ->and($appointment->appointable()->type)->toBe('doctor')
            ->and($appointment->scheduleable()->type)->toBe('patient')
            ->and($appointment->startsAt()->getTimestamp())->toBe($at->getTimestamp())
            ->and($appointment->title())->toBe('Consultation with Dr. John Doe');
    });

    it('can schedule an appointment without appointable', function () {
        $at = Carbon::now()->addHour()->toDateTime();
        $appointment = \RedberryProducts\Appointment\Facades\Appointment::for($this->patient)
            ->schedule($at, 'Appointment without appointable');

        expect($appointment)->toBeInstanceOf(\RedberryProducts\Appointment\Appointment::class)
            ->and($appointment->scheduleable()->type)->toBe('patient')
            ->and($appointment->appointable())->toBeNull()
            ->and($appointment->startsAt()->getTimestamp())->toBe($at->getTimestamp())
            ->and($appointment->title())->toBe('Appointment without appointable');
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

        expect($appointment->workingHours())->toBeInstanceOf(OpeningHours::class);
    });

    it('can use working hour timeslots', function () {
        $appointment = Appointment::with($this->doctor)->setWorkingHours([
            'monday' => ['11:00-12:00', '12:00-13:00', '13:00-14:00', '16:00-17:00'],
        ]);
        $appointableOpeningHours = $appointment->workingHours();

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
            at: Carbon::make('2024-04-08 12:00:00')->toDateTime(),
            title: 'Consultation with Dr. John Doe'
        );

        expect($appointment->databaseRecord())->toBeInstanceOf(\RedberryProducts\Appointment\Models\Appointment::class)
            ->and($appointment->databaseRecord()->appointable->type)->toBe('doctor')
            ->and($appointment->databaseRecord()->scheduleable->type)->toBe('patient')
            ->and($appointment->databaseRecord()->starts_at->toString())->toBe(Carbon::make('2024-04-08 12:00:00')->toString())
            ->and($appointment->databaseRecord()->title)->toBe('Consultation with Dr. John Doe');
    });

    it('can not schedule appointment on an invalid timeslot', function () {
        Appointment::with($this->doctor)->setWorkingHours([
            'monday' => ['11:00-12:00', '12:00-13:00', '13:00-14:00', '16:00-17:00'],
        ]);

        $this->patient->scheduleAppointment(
            with: $this->doctor,
            at: Carbon::make('2024-04-08 15:00:00')->toDateTime(),
            title: 'Consultation with Dr. John Doe'
        );
    })->throws(UnavailableAppointmentTimeException::class, 'The time slot is unavailable or outside of working hours');

    it('can cancel an appointment', function () {
        $appointment = $this->patient->scheduleAppointment(
            with: $this->doctor,
            at: Carbon::make('2024-04-08 12:00:00')->toDateTime(),
            title: 'Consultation with Dr. John Doe'
        );

        $appointment->cancel();

        expect($appointment->databaseRecord()->updated_at)->toBeInstanceOf(Carbon::class);
    });

    it('can find and cancel an appointment', function () {
        $appointment = Appointment::with($this->doctor)
            ->for($this->patient)
            ->schedule(Carbon::make('2024-04-08 12:00:00')->toDateTime(), 'Consultation with Dr. John Doe');
        $appointment = $appointment->cancel();

        expect($appointment->status())->toBe(\RedberryProducts\Appointment\Enums\Status::CANCELED->value)
            ->and($appointment->databaseRecord()->status)->toBe(\RedberryProducts\Appointment\Enums\Status::CANCELED->value);
    });

    it('can complete an appointment', function () {
        $appointment = Appointment::with($this->doctor)
            ->for($this->patient)
            ->schedule(Carbon::make('2024-04-08 12:00:00')->toDateTime(), 'Consultation with Dr. John Doe');
        $appointment = $appointment->complete();

        expect($appointment->status())->toBe(\RedberryProducts\Appointment\Enums\Status::COMPLETED->value)
            ->and($appointment->databaseRecord()->status)->toBe(\RedberryProducts\Appointment\Enums\Status::COMPLETED->value);
    });

    it('can create an appointment, then find it using facade and cancel it', function () {
        $appointment = Appointment::with($this->doctor)
            ->for($this->patient)
            ->schedule(Carbon::make('2024-04-08 12:00:00')->toDateTime(), 'Consultation with Dr. John Doe');
        $appointment = Appointment::findSchedule($appointment->databaseRecord()->id)->cancel();

        expect($appointment->status())->toBe(\RedberryProducts\Appointment\Enums\Status::CANCELED->value)
            ->and($appointment->databaseRecord()->status)->toBe(\RedberryProducts\Appointment\Enums\Status::CANCELED->value);
    });

    it('can create an appointment, then find it using facade and complete it', function () {
        $appointment = Appointment::with($this->doctor)
            ->for($this->patient)
            ->schedule(Carbon::make('2024-04-08 12:00:00')->toDateTime(), 'Consultation with Dr. John Doe');
        $appointment = Appointment::findSchedule($appointment->databaseRecord()->id)->complete();

        expect($appointment->status())->toBe(\RedberryProducts\Appointment\Enums\Status::COMPLETED->value)
            ->and($appointment->databaseRecord()->status)->toBe(\RedberryProducts\Appointment\Enums\Status::COMPLETED->value);
    });

    it('can not complete an appointment that is already canceled', function () {
        $appointment = Appointment::with($this->doctor)
            ->for($this->patient)
            ->schedule(Carbon::make('2024-04-08 12:00:00')->toDateTime(), 'Consultation with Dr. John Doe');
        $appointment = Appointment::findSchedule($appointment->databaseRecord()->id)->cancel();

        expect($appointment->status())->toBe(\RedberryProducts\Appointment\Enums\Status::CANCELED->value)
            ->and($appointment->databaseRecord()->status)->toBe(\RedberryProducts\Appointment\Enums\Status::CANCELED->value);

        Appointment::findSchedule($appointment->databaseRecord()->id)->complete();
    })->throws(AppointmentAlreadyCanceledException::class, 'The appointment has already been canceled');

    it('can not cancel an appointment that is already completed', function () {
        $appointment = Appointment::with($this->doctor)
            ->for($this->patient)
            ->schedule(Carbon::make('2024-04-08 12:00:00')->toDateTime(), 'Consultation with Dr. John Doe');
        $appointment = Appointment::findSchedule($appointment->databaseRecord()->id)->complete();

        expect($appointment->status())->toBe(\RedberryProducts\Appointment\Enums\Status::COMPLETED->value)
            ->and($appointment->databaseRecord()->status)->toBe(\RedberryProducts\Appointment\Enums\Status::COMPLETED->value);

        Appointment::findSchedule($appointment->databaseRecord()->id)->cancel();
    })->throws(AppointmentAlreadyCompletedException::class, 'The appointment has already been completed');

    it('can reschedule an appointment', function () {
        $appointment = Appointment::with($this->doctor)
            ->for($this->patient)
            ->schedule(Carbon::make('2024-04-08 12:00:00')->toDateTime(), 'Consultation with Dr. John Doe');
        $appointment = Appointment::findSchedule($appointment->databaseRecord()->id)
            ->reschedule(Carbon::make('2024-04-08 13:00:00')->toDateTime());

        expect($appointment->startsAt()->getTimestamp())->toBe(Carbon::make('2024-04-08 13:00:00')->getTimestamp());
    });

    it('can not rescchedule an appointment that is already completed', function () {
        $appointment = Appointment::with($this->doctor)
            ->for($this->patient)
            ->schedule(Carbon::make('2024-04-08 12:00:00')->toDateTime(), 'Consultation with Dr. John Doe');
        $appointment = Appointment::findSchedule($appointment->databaseRecord()->id)->complete();

        expect($appointment->status())->toBe(\RedberryProducts\Appointment\Enums\Status::COMPLETED->value)
            ->and($appointment->databaseRecord()->status)->toBe(\RedberryProducts\Appointment\Enums\Status::COMPLETED->value);

        Appointment::findSchedule($appointment->databaseRecord()->id)->reschedule(Carbon::make('2024-04-08 13:00:00')->toDateTime());
    })->throws(AppointmentAlreadyCompletedException::class, 'The appointment has already been completed');

    it('can not reschedule an appointment that is already canceled', function () {
        $appointment = Appointment::with($this->doctor)
            ->for($this->patient)
            ->schedule(Carbon::make('2024-04-08 12:00:00')->toDateTime(), 'Consultation with Dr. John Doe');
        $appointment = Appointment::findSchedule($appointment->databaseRecord()->id)->cancel();

        expect($appointment->status())->toBe(\RedberryProducts\Appointment\Enums\Status::CANCELED->value)
            ->and($appointment->databaseRecord()->status)->toBe(\RedberryProducts\Appointment\Enums\Status::CANCELED->value);

        Appointment::findSchedule($appointment->databaseRecord()->id)->reschedule(Carbon::make('2024-04-08 13:00:00')->toDateTime());
    })->throws(AppointmentAlreadyCanceledException::class, 'The appointment has already been canceled');
});
