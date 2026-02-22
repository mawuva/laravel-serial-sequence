<?php

namespace Mawuva\LaravelSerialSequence\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mawuva\LaravelSerialSequence\LaravelSerialSequenceServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Tests\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelSerialSequenceServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Load package migrations
        foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__.'/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
        }

        // Load test models migrations
        foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__.'/database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
        }
    }
}
