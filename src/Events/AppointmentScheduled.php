<?php

namespace RedberryProducts\Appointment\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use RedberryProducts\Appointment\Appointment;

class AppointmentScheduled extends Event
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Appointment $appointment
    ) {
    }
}
