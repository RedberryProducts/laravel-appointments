<?php

namespace RedberryProducts\Appointment\Exceptions;

class AppointmentNotFoundException extends \Exception
{
    public function __construct($message = 'Appointment not found')
    {
        parent::__construct($message);
    }
}
