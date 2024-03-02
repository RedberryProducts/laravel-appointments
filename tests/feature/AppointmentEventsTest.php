<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use RedberryProducts\Appointment\Facades\Appointment;
use RedberryProducts\Appointment\Models\Appointment as AppointmentModel;
use RedberryProducts\Appointment\Tests\Models\User;

describe('test appointment events', function () {
    beforeEach(function () {
        $this->doctor = User::factory()->doctor()->create();
        $this->patient = User::factory()->patient()->create();
    });
    it('dispatches AppointmentReschedule event on appointment reschedule', function () {
        Event::fake();
        $user = User::factory()->create();
        $appointment = AppointmentModel::factory()->scheduleable($user)->pending()->create();
        Appointment::findSchedule($appointment->id)
            ->reschedule(
                Carbon::make('2022-01-01 12:00:00')->toDateTime()
            );
        Event::assertDispatched(\RedberryProducts\Appointment\Events\AppointmentRescheduled::class);
    });

    it('dispatches AppointmentScheduled event on appointment schedule', function () {
        Event::fake();
        Appointment::with($this->doctor)
            ->for($this->patient)
            ->schedule(
                Carbon::make('2022-01-01 12:00:00')->toDateTime(),
                'title'
            );
        Event::assertDispatched(\RedberryProducts\Appointment\Events\AppointmentScheduled::class);
    });

    it('dispatches AppointmentCanceled event on appointment cancel', function () {
        Event::fake();
        $user = User::factory()->create();
        $appointment = AppointmentModel::factory()->scheduleable($user)->pending()->create();
        Appointment::findSchedule($appointment->id)
            ->cancel();
        Event::assertDispatched(\RedberryProducts\Appointment\Events\AppointmentCanceled::class);
    });

    it('dispatches AppointmentCompleted event on appointment complete', function () {
        Event::fake();
        $user = User::factory()->create();
        $appointment = AppointmentModel::factory()->scheduleable($user)->pending()->create();
        Appointment::findSchedule($appointment->id)
            ->complete();
        Event::assertDispatched(\RedberryProducts\Appointment\Events\AppointmentCompleted::class);
    });
});
