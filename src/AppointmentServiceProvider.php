<?php

namespace RedberryProducts\Appointment;

use Illuminate\Support\Facades\Event;
use RedberryProducts\Appointment\Commands\AppointmentCommand;
use RedberryProducts\Appointment\Events\AppointmentCanceled;
use RedberryProducts\Appointment\Events\AppointmentCompleted;
use RedberryProducts\Appointment\Events\AppointmentRescheduled;
use RedberryProducts\Appointment\Events\AppointmentScheduled;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AppointmentServiceProvider extends PackageServiceProvider
{
    protected array $events = [
        AppointmentScheduled::class => [
            // Listeners go here
        ],
        AppointmentRescheduled::class => [
            // Listeners go here
        ],
        AppointmentCompleted::class => [
            // Listeners go here
        ],
        AppointmentCanceled::class => [
            // Listeners go here
        ],
    ];

    public function registerEvents(): void
    {
        $eventListenerMapping = $this->events;
        foreach ($eventListenerMapping as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    public function boot(): AppointmentServiceProvider
    {
        $this->registerEvents();

        return parent::boot();
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-appointments')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_appointments_table')
            ->hasMigration('create_appointable_time_settings_table')
            ->hasCommand(AppointmentCommand::class);
    }
}
