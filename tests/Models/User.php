<?php

namespace RedberryProducts\Appointment\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RedberryProducts\Appointment\Models\Traits;

/**
 * @property mixed $timeSetting
 */
class User extends Model
{
    use HasFactory;
    use Traits\HasAppointments;
    use Traits\HasSchedules;

    protected $fillable = [
        'name',
        'email',
        'type',
    ];
}
