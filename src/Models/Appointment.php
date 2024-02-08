<?php

namespace RedberryProducts\Appointment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $title
 * @property string $status
 * @property string $type
 * @property Carbon $ends_at
 * @property Carbon $starts_at
 */
class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'starts_at',
        'ends_at',
        'title',
        'type',
        'status',
    ];

    public function appointable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scheduleable(): MorphTo
    {
        return $this->morphTo();
    }
}
