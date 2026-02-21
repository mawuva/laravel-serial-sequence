<?php

declare(strict_types=1);

namespace Mawuva\LaravelSerialSequence\Concerns;

use Mawuva\LaravelSerialSequence\Data\SerialData;

trait HasSerialAttributes
{
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
