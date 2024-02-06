<?php

namespace RedberryProducts\Appointment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RedberryProducts\Appointment\Appointment
 */
class Appointment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \RedberryProducts\Appointment\Appointment::class;
    }
}
