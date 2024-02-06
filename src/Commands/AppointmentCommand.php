<?php

namespace RedberryProducts\Appointment\Commands;

use Illuminate\Console\Command;

class AppointmentCommand extends Command
{
    public $signature = 'laravel-appointments';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
