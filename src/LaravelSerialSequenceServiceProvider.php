<?php

namespace Mawuva\LaravelSerialSequence;

use Mawuva\LaravelSerialSequence\Commands\LaravelSerialSequenceCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelSerialSequenceServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-serial-sequence')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_serial_sequence_table')
            ->hasCommand(LaravelSerialSequenceCommand::class);
    }
}
