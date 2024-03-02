<?php

namespace RedberryProducts\Appointment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use RedberryProducts\Appointment\Enums\Status;

/**
 * @property string $title
 * @property string $status
 * @property string $type
 * @property Carbon $ends_at
 * @property Carbon $starts_at
 * @property int $id
 * @property mixed $appointable
 * @property mixed $scheduleable
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

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'status' => 'string',
    ];

    public function appointable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scheduleable(): MorphTo
    {
        return $this->morphTo();
    }

    public function cancel(): void
    {
        $this->update(['status' => Status::CANCELED->value]);
    }

    public function complete(): void
    {
        $this->update(['status' => Status::COMPLETED->value]);
    }
}
