<?php

namespace RedberryProducts\Appointment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
