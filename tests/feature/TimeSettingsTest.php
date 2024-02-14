<?php

use RedberryProducts\Appointment\Facades\Appointment;
use RedberryProducts\Appointment\Models\AppointableTimeSetting;
use RedberryProducts\Appointment\Models\User;
use Spatie\OpeningHours\OpeningHours;
use Spatie\OpeningHours\OpeningHoursForDay;

beforeEach(function () {
    $this->doctor = User::factory()->doctor()->create();
    $this->patient = User::factory()->patient()->create();
});

it('can set opening hour of appointables via trait', function () {
    $this->doctor->setOpeningHours(
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
    $this->doctor->setOpeningHours(
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

    expect($this->doctor->openingHours())->toBeInstanceOf(OpeningHours::class)
        ->and($this->doctor->openingHours()->forDay('monday'))->toBeInstanceOf(OpeningHoursForDay::class);
});

it('can set opening hours via facade', function () {
    Appointment::with($this->doctor)->setOpeningHours([
        'monday' => ['09:00-12:00'],
        'tuesday' => ['09:00-12:00', '13:00-18:00'],
        'wednesday' => ['09:00-12:00'],
        'thursday' => ['09:00-12:00', '13:00-18:00'],
        'friday' => ['09:00-12:00', '13:00-20:00'],
        'saturday' => ['09:00-12:00', '13:00-16:00'],
        'sunday' => [],
        'exceptions' => [],
    ]);

    expect($this->doctor->openingHours())
        ->toBeInstanceOf(OpeningHours::class)
        ->and($this->doctor->openingHours()->forDay('monday'))
        ->toBeInstanceOf(OpeningHoursForDay::class);
});

it('can set opening hours for without appointable', function () {
    $openingHours = Appointment::setOpeningHours([
        'monday' => ['09:00-12:00'],
        'tuesday' => ['09:00-12:00', '13:00-18:00'],
        'wednesday' => ['09:00-12:00'],
        'thursday' => ['09:00-12:00', '13:00-18:00'],
        'friday' => ['09:00-12:00', '13:00-20:00'],
        'saturday' => ['09:00-12:00', '13:00-16:00'],
        'sunday' => [],
        'exceptions' => [],
    ]);

    expect($openingHours)->toBeInstanceOf(AppointableTimeSetting::class)
        ->and($openingHours->opening_hours)->toBeArray();
});
