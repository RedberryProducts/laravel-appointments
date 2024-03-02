<?php

namespace RedberryProducts\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property array $opening_hours;
 *
 * @method whereNull(string $string)
 * @method static general()
 */
class AppointableTimeSetting extends Model
{
    protected $fillable = [
        'opening_hours',
    ];

    protected $casts = [
        'opening_hours' => 'array',
    ];

    public function scopeGeneral()
    {
        return $this->whereNull('appointable_type');
    }

    public function appointable(): MorphTo
    {
        return $this->morphTo();
    }
}
