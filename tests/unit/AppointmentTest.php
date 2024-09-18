<?php

use Illuminate\Support\Carbon;
use RedberryProducts\Appointment\Facades\Appointment;
use RedberryProducts\Appointment\Tests\Models\User;

beforeEach(function () {
    $this->doctor = User::factory()->create();
    $this->patient = User::factory()->create();
    $this->appointment = Appointment::with($this->doctor)
        ->for($this->patient)
        ->schedule(Carbon::make('2024-09-16 12:00:00')->toDateTime(), 'Consultation with Dr. John Doe');
});

it('can return databaseRecord instance', function () {
    expect($this->appointment->databaseRecord())->toBeInstanceOf(RedberryProducts\Appointment\Models\Appointment::class);
});

it('returns startsAt() DateTime object', function () {
    expect($this->appointment->startsAt())->toBeInstanceOf(\DateTime::class);
});

it('returns findSchedule() Appointment model', function () {
    $appointment = Appointment::findSchedule($this->appointment->id());
    expect($appointment)->toBeInstanceOf(RedberryProducts\Appointment\Appointment::class);
});

it('sets with and for instances', function () {
    $appointment = Appointment::with($this->doctor)->for($this->patient);

    expect($appointment->appointable()->id)->toBe($this->doctor->id)
        ->and($appointment->scheduleable()->id)->toBe($this->patient->id);
});
