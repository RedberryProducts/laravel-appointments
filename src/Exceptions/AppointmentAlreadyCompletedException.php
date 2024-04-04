<?php

namespace RedberryProducts\Appointment\Exceptions;

class AppointmentAlreadyCompletedException extends \Exception
{
    public function __construct($message = 'The appointment has already been completed')
    {
        parent::__construct($message);
    }
}
