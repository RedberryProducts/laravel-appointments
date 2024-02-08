<?php

use Illuminate\Support\Carbon;
use RedberryProducts\Appointment\Models\User;

beforeEach(function () {
    $this->doctor = User::factory()->doctor()->create();
    $this->patient = User::factory()->patient()->create();
});

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

    expect($appointment)->toBeInstanceOf(\RedberryProducts\Appointment\Models\Appointment::class)
        ->and($appointment->appointable->type)->toBe('doctor')
        ->and($appointment->scheduleable->type)->toBe('patient')
        ->and($appointment->starts_at)->toBe($appointmentAt)
        ->and($appointment->title)->toBe($appointmentTitle);
});

it('can schedule a meeting using facade', function () {
    $appointment = \RedberryProducts\Appointment\Facades\Appointment::with($this->doctor)
        ->for($this->patient)
        ->schedule(Carbon::now()->addHour(), 'Consultation with Dr. John Doe');

    expect($appointment)->toBeInstanceOf(\RedberryProducts\Appointment\Appointment::class)
        ->and($appointment->appointable->type)->toBe('doctor')
        ->and($appointment->scheduleable->type)->toBe('patient')
        ->and($appointment->at->toString())->toBe(Carbon::now()->addHour()->toString())
        ->and($appointment->title)->toBe('Consultation with Dr. John Doe');
});

it('can schedule an appointment without appointable', function () {
    $appointment = \RedberryProducts\Appointment\Facades\Appointment::for($this->patient)
        ->schedule(Carbon::now()->addHour(), 'Appointment without appointable');

    expect($appointment)->toBeInstanceOf(\RedberryProducts\Appointment\Appointment::class)
        ->and($appointment->scheduleable->type)->toBe('patient')
        ->and($appointment->appointable)->toBeNull()
        ->and($appointment->at->toString())->toBe(Carbon::now()->addHour()->toString())
        ->and($appointment->title)->toBe('Appointment without appointable');
});
