<?php

namespace RedberryProducts\Appointment\Exceptions;

class AppointmentDataValidationException extends \Exception
{
    public function __construct($message = 'Invalid appointment data')
    {
        parent::__construct($message);
    }
}
