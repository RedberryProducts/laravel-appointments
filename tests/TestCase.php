<?php

namespace RedberryProducts\Appointment\Tests;

use AllowDynamicProperties;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use RedberryProducts\Appointment\AppointmentServiceProvider;

#[AllowDynamicProperties] class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'RedberryProducts\\Appointment\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            AppointmentServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../database/migrations/create_appointments_table.php.stub';
        $appointableTimeSettings = include __DIR__.'/../database/migrations/create_appointable_time_setting_table.php.stub';
        $userMigration = include __DIR__.'/../database/migrations/create_users_table.php.stub';
        $userMigration->up();
        $migration->up();
        $appointableTimeSettings->up();
    }
}
