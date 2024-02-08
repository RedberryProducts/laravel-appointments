<?php

use Illuminate\Support\Carbon;
use RedberryProducts\Appointment\Appointment;
use RedberryProducts\Appointment\Models\User;

beforeEach(function () {
    $this->doctor = User::factory()->create();
    $this->patient = User::factory()->create();
    $this->appointment = new Appointment();
});

it('returns instance of itself', function () {
    $afterFor = $this->appointment->for($this->patient);
    $afterWith = $this->appointment->with($this->doctor);

    expect($afterFor)->toBeInstanceOf(Appointment::class)
        ->and($afterWith)->toBeInstanceOf(Appointment::class);
});

it('can set scheduleable and appointable', function () {
    $this->appointment->for($this->patient);
    $this->appointment->with($this->doctor);

    expect($this->appointment->scheduleable)->toBeInstanceOf(User::class)
        ->and($this->appointment->appointable)->toBeInstanceOf(User::class);
});

it('can schedule an appointment', function () {
    $appointment = $this->appointment->for($this->patient)
        ->with($this->doctor)
        ->schedule(Carbon::now()->addHour(), 'Consultation with Dr. John Doe');

    expect($appointment)->toBeInstanceOf(Appointment::class)
        ->and($appointment->scheduleable)->toBeInstanceOf(User::class)
        ->and($appointment->appointable)->toBeInstanceOf(User::class)
        ->and($appointment->at->toString())->toBe(Carbon::now()->addHour()->toString())
        ->and($appointment->title)->toBe('Consultation with Dr. John Doe')
        ->and($appointment->dbRecord)->toBeInstanceOf(\RedberryProducts\Appointment\Models\Appointment::class)
        ->and($appointment->dbRecord->title)->toBe('Consultation with Dr. John Doe')
        ->and($appointment->dbRecord->status)->toBe('pending');
});

it('can schedule an appointment without appointable', function () {
    $appointment = $this->appointment->for($this->patient)
        ->schedule(Carbon::now()->addHour(), 'Appointment without appointable');

    expect($appointment)->toBeInstanceOf(Appointment::class)
        ->and($appointment->scheduleable)->toBeInstanceOf(User::class)
        ->and($appointment->appointable)->toBeNull()
        ->and($appointment->at->toString())->toBe(Carbon::now()->addHour()->toString())
        ->and($appointment->title)->toBe('Appointment without appointable')
        ->and($appointment->dbRecord)->toBeInstanceOf(\RedberryProducts\Appointment\Models\Appointment::class)
        ->and($appointment->dbRecord->title)->toBe('Appointment without appointable')
        ->and($appointment->dbRecord->status)->toBe('pending');
});
