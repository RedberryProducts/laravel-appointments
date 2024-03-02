<?php

namespace RedberryProducts\Appointment\Enums;

enum Status: string
{
    case PENDING = 'pending';
    case CANCELED = 'canceled';
    case COMPLETED = 'completed';
}
