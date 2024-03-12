<?php

namespace RedberryProducts\Appointment\Exceptions;

class AppointmentAlreadyCanceledException extends \Exception
{
    public function __construct($message = 'The appointment has already been canceled')
    {
        parent::__construct($message);
    }
}
