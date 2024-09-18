<?php

use RedberryProducts\Appointment\Appointment;
use RedberryProducts\Appointment\Tests\Models\User;

beforeEach(function () {
    $this->doctor = User::factory()->create();
    $this->patient = User::factory()->create();
    $this->appointment = new Appointment;
});

it('for and with methods returns instance of static', function () {
    $afterFor = $this->appointment->for($this->patient);
    $afterWith = $this->appointment->with($this->doctor);

    expect($afterFor)->toBeInstanceOf(Appointment::class)
        ->and($afterWith)->toBeInstanceOf(Appointment::class);
});
//
//it('can set scheduleable and appointable', function () {
//    $this->appointment->for($this->patient);
//    $this->appointment->with($this->doctor);
//
//    expect($this->appointment->scheduleable())->toBeInstanceOf(User::class)
//        ->and($this->appointment->appointable())->toBeInstanceOf(User::class);
//});
