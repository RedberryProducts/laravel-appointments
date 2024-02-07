<?php

namespace RedberryProducts\Appointment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RedberryProducts\Appointment\Appointment
 *
 * @mixin \RedberryProducts\Appointment\Appointment
 */
class Appointment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \RedberryProducts\Appointment\Appointment::class;
    }
}
