<?php

namespace RedberryProducts\Appointment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
