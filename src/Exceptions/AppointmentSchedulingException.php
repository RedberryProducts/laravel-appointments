<?php

namespace RedberryProducts\Appointment\Exceptions;

class AppointmentSchedulingException extends \Exception
{
    public function __construct($message = 'An error occurred while scheduling the appointment')
    {
        parent::__construct($message);
    }
}
