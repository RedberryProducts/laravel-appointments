<?php

namespace RedberryProducts\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AppointableTimeSetting extends Model
{
    protected $fillable = [
        'opening_hours',
    ];

    protected $casts = [
        'opening_hours' => 'array',
    ];

    public function appointable(): MorphTo
    {
        return $this->morphTo();
    }
}