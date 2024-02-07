<?php

namespace RedberryProducts\Appointment;

use RedberryProducts\Appointment\Commands\AppointmentCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AppointmentServiceProvider extends PackageServiceProvider
{
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
            ->hasCommand(AppointmentCommand::class);
    }
}
