<?php

declare(strict_types=1);

namespace Mawuva\LaravelSerialSequence\Observers;

use Mawuva\LaravelSerialSequence\Contracts\HasSerial;
use Mawuva\LaravelSerialSequence\Services\SerialGenerator;

class SerialSequenceObserver
{
    /**
     * Handle the model "creating" event.
     * 
     * This observer automatically generates a serial number for models
     * implementing the HasSerial interface when they are being created.
     * 
     * The process:
     * 1. Checks if serial is already set (skip if manually provided)
     * 2. Resolves optional prefix using configured resolver
     * 3. Generates new serial using SerialGenerator
     * 4. Hydrates model with serial data
     * 
     * @param HasSerial $model The model being created
     * @return void
     */
    public function creating(HasSerial $model): void
    {
        if (!empty($model->serial)) {
            return;
        }

        $generator = app(SerialGenerator::class);

        $prefix = null;
        $config = config('serial-sequence.prefix_resolver');
        
        if (is_callable($config)) {
            $prefix = $config($model);
        }

        $serialData = $generator->generate(
            $model->serialSerie(),
            $prefix
        );

        $model->setSerialAttributes($serialData);
    }
}
