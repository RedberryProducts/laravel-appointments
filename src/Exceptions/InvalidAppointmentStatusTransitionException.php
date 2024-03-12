<?php

namespace RedberryProducts\Appointment\Exceptions;

class InvalidAppointmentStatusTransitionException extends \Exception
{
    public function __construct($message = 'Invalid appointment status transition')
    {
        parent::__construct($message);
    }
}
