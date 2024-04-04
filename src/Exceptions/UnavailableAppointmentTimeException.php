<?php

namespace RedberryProducts\Appointment\Exceptions;

class UnavailableAppointmentTimeException extends \Exception
{
    public function __construct($message = 'The time slot is unavailable or outside of working hours')
    {
        parent::__construct($message);
    }
}
