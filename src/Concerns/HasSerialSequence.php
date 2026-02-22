<?php

declare(strict_types=1);

namespace Mawuva\LaravelSerialSequence\Concerns;

use Mawuva\LaravelSerialSequence\Data\SerialData;
use Mawuva\LaravelSerialSequence\Observers\SerialSequenceObserver;

trait HasSerialSequence
{
    /**
     * Boot the trait and register the observer.
     * 
     * This method is automatically called by Laravel when the model boots.
     * It registers the SerialSequenceObserver to handle automatic serial generation.
     * 
     * @return void
     */
    public static function bootHasSerialSequence(): void
    {
        static::observe(SerialSequenceObserver::class);
    }

    /**
     * Set serial attributes from SerialData object.
     * 
     * @param SerialData $data The serial data containing all components
     * @return void
     */
    public function setSerialAttributes(SerialData $data): void
    {
        $this->serial = $data->serial;
        $this->serie = $data->serie;
        $this->serial_year = $data->year;
        $this->serial_month = $data->month;
        $this->serial_number = $data->number;
    }
}
